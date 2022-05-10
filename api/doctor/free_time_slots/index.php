<?php
    #region načtení závislostí
    require_once __DIR__ . '/../../inc/functions.php';//načtení souboru s pomocnými funkcemi
    try {
        require_once __DIR__ . '/../../../inc/db.php';//připojení k DB
    } catch (\Exception $e) {
        send_error_response('Chyba připojení k databázi.', 500);
    }
    #endregion načtení závislostí

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            //načtení jedné či několika osob
            if (!empty($_GET['doctor_id']) && !empty($_GET['date'])) {
                getDoctorFreeTimeSlots($db, intval($_GET['doctor_id']), $_GET['date']);
                break;
            }

        default:
            send_error_response('Nesprávný požadavek.', 400);
            exit();
    }

    function getDoctorFreeTimeSlots(PDO $db, int $doctorId, $date) {
        $query = $db->prepare('SELECT schedule_from,schedule_to,timeslot_size FROM doctors WHERE doctor_id=:doctor_id LIMIT 1;');
        $query->execute([
            ':doctor_id' => $doctorId
        ]);

        if ($boundaries = $query->fetch(PDO::FETCH_ASSOC)) {
            $query = $db->prepare('SELECT timestamp FROM appointments WHERE doctor_id=:doctor_id AND timestamp>=:today AND timestamp<:tomorrow;');
            $query->execute([
                ':doctor_id' => $doctorId,
                ':today' => strtotime($date),
                ':tomorrow' => strtotime($date) + (24 * 60 * 60)
            ]);

            $occupiedTimeSlots = $query->fetchAll(PDO::FETCH_ASSOC);
            for ($i = 0; $i < count($occupiedTimeSlots); ++$i) {
                $occupiedTimeSlots[$i] = strtotime($date . 't' . $occupiedTimeSlots[$i]['time']);
            }

            $schedule = calculateFreeTimeSlots($boundaries, $date, $occupiedTimeSlots);
            $response = [];
            foreach ($schedule as $time) {
                array_push($response, $time);
            }
            send_json_response($response);

        } else {
            send_error_response('Zvolený lékař neexistuje.', 404);
        }
    }

    function isWeekend($timestamp) {
        $date = strtotime($timestamp);
        $date = date("l", $date);
        $date = strtolower($date);
        if ($date == "saturday" || $date == "sunday") {
            return true;
        } else {
            return false;
        }
    }

    function calculateFreeTimeSlots($boundaries, $date, $occupiedTimeSlots) {
        if (isWeekend($date)) {
            return [];
        }

        $boundaries['schedule_from'] = strtotime($date . 't' . $boundaries['schedule_from']);
        $boundaries['schedule_to'] = strtotime($date . 't' . $boundaries['schedule_to']);

        $entireSchedule = [];
        $schedulesIterator = 0;
        $currentTime = $boundaries['schedule_from'];
        while ($currentTime < $boundaries['schedule_to']) {
            if (!($currentTime < time())) {
                $entireSchedule[$schedulesIterator] = $currentTime;
                $schedulesIterator++;
            }
            $currentTime += 60 * $boundaries['timeslot_size'];
        }

        $freeTimeSlots = array_diff($entireSchedule, $occupiedTimeSlots);

        return $freeTimeSlots;
    }
