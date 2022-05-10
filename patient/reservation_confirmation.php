<?php
    function retreiveFreeTimeSlots($doctorId, $timestamp) {
        $date = date("Y-m-d", intval($timestamp));
        $url = 'https://eso.vse.cz/~matj27/4iz278/semestralni_prace/api/doctor/free_time_slots/?doctor_id=' .
            $doctorId . '&date=' . $date;
        $timeslots = json_decode(file_get_contents($url));

        return $timeslots;
    }

    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_patient.php';

    $errors = [];
    #region existujici lekar
    if (!empty(@$_GET['doctor_id'])) {
        $doctorQuery = $db->prepare('SELECT doctor_id,given_name,family_name,email FROM doctors WHERE doctor_id=:id AND active=1 LIMIT 1;');
        $doctorQuery->execute([
            ':id' => $_GET['doctor_id']
        ]);
        if ($doctorQuery->rowCount() != 1) {
            $errors['doctor'] = 'Došlo k chybě, ke zvolenému lékaři se nelze nyní objednat.';
        }
        $doctor = $doctorQuery->fetch(PDO::FETCH_ASSOC);
    } else {
        $errors['doctor'] = 'Došlo k chybě, lékař nebyl zvolen.';
    }
    #endregion exitujici lekar

    #region volny timeslot
    if ($doctor && !empty(@$_GET['timestamp'])) {
        $freeTimeSlots = retreiveFreeTimeSlots($doctor['doctor_id'], $_GET['timestamp']);
        if (!in_array($_GET['timestamp'], $freeTimeSlots)) {
            $errors['time'] = 'Zvolený čas již není k dispozici.';
        }
    }
    #endregion volny timeslot

    #region vytvoreni rezervace
    if (empty($errors)) {
        $insertQuery = $db->prepare('INSERT INTO appointments (timestamp, patient_id, doctor_id) 
                                            VALUES (:timestamp, :patient_id, :doctor_id);');
        if (
            $insertQuery->execute([
                ':timestamp' => $_GET['timestamp'],
                ':patient_id' => $_SESSION['patient_id'],
                ':doctor_id' => $doctor['doctor_id']
            ])
        ) {
            $appointmentId = $db->lastInsertId();
            //todo odeslat email
        } else {
            $errors['reservation'] = 'Došlo k chybě při vytváření rezervace.';
        }
    }
    #endregion vytvoreni rezervace

    $pageTitle = 'Potvrzení rezervace';
    $currentPage = basename(__FILE__);
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<div class="alert alert-danger" role="alert">';
            echo htmlspecialchars($error);
            echo '</div>';
        }
        echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/index.php" class="mr-1 btn btn-primary">Pokračovat</a>';
    } else {
        ?>
        <div class="alert alert-success" role="alert">Rezervace byla úspěšně vytvořena!</div>
        <ul class="list-group">
            <li class="list-group-item">
                Číslo rezervace: <?php echo htmlspecialchars($appointmentId); ?>
            </li>
            <li class="list-group-item">
                Pacient: <?php echo htmlspecialchars($_SESSION['given_name'] . ' ' . $_SESSION['family_name']); ?>
            </li>
            <li class="list-group-item">
                Lékař: <?php echo htmlspecialchars($doctor['given_name'] . ' ' . $doctor['family_name']); ?>
            </li>
            <li class="list-group-item">
                Datum a čas: <?php echo date("d. m. Y, H:i:s", intval($_GET['timestamp'])); ?>
            </li>
            <li class="list-group-item">
                E-mail na lékaře: <?php echo htmlspecialchars($doctor['email']); ?>
            </li>
        </ul>
        <a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/index.php" class="mr-1 btn btn-primary">Pokračovat</a>
        <?php
    }

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';