<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/email_functions.php';
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/view_appointments_patients_doctors.php';

    function prepareEmailBody($appointmentId, $doctor) {
        $emailBody = nl2br('Byla vytvořená nová rezervace.
            Číslo rezervace: ' . htmlspecialchars($appointmentId) . '
            Pacient: ' . htmlspecialchars($_SESSION['given_name'] . ' ' . $_SESSION['family_name']) . '
            Lékař: ' . htmlspecialchars($doctor['given_name'] . ' ' . $doctor['family_name']) . '
            Datum a čas: ' . date("d. m. Y, H:i:s", intval($_GET['timestamp'])) . '
            E-mail na pacienta: ' . ' <a href = "mailto:' . htmlspecialchars($_SESSION['email']) . '">' .
            htmlspecialchars($_SESSION['email']) . '</a>');

        return $emailBody;
    }

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
        $doctorQuery = $db->prepare('SELECT doctor_id,given_name,family_name,email,timeslot_size FROM doctors WHERE doctor_id=:id AND active=1 LIMIT 1;');
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
        $insertQuery = $db->prepare('INSERT INTO appointments (timestamp, length, patient_id, doctor_id, confirmed) 
                                            VALUES (:timestamp, :lenght, :patient_id, :doctor_id, 0);');
        if (
            $insertQuery->execute([
                ':timestamp' => $_GET['timestamp'],
                ':lenght'=> $doctor['timeslot_size'],
                ':patient_id' => $_SESSION['patient_id'],
                ':doctor_id' => $doctor['doctor_id']
            ])
        ) {
            $appointmentId = $db->lastInsertId();
            $emailBody = prepareEmailBody($appointmentId, $doctor);
            sendMail(
                $_SESSION['email'], $_SESSION['given_name'], $_SESSION['family_name'],
                $_SESSION['email'], $_SESSION['given_name'], $_SESSION['family_name'],
                $doctor['email'], 'Nová rezervace', $emailBody
            );
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
        <div class="alert alert-success" role="alert">Rezervace byla vytvořena. Vyčkejte na potvrzovací e-mail od
            lékaře.
        </div>
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
                E-mail na lékaře: <?php echo '<a href="mailto:' . htmlspecialchars($doctor['email']) . '">' .
                    htmlspecialchars($doctor['email']) . '</a>'; ?>
            </li>
        </ul>
        <p>
            Upozorňujeme, že Vámi zarezervovaná návštěva musí být potvrzená ošetřujícím lékařem.<br/>
            Před návštěvou ordinace vyčkejte, prosím, na potvrzovací e-mail.
        </p>
        <a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/index.php" class="mr-1 btn btn-primary">Pokračovat</a>
        <?php
    }

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';