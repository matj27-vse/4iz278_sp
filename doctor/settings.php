<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_doctor.php';

    $errors = [];

    function prepareEmailBodyDoctor($appointments) {
        $emailBody = '';

        foreach ($appointments as $appointment) {
            $emailBody .= nl2br('
            Rezervace byla zrušena.
            Číslo rezervace: ' . htmlspecialchars($appointment['appointment_id']) . '
            Pacient: ' . htmlspecialchars($appointment['patient_given_name'] . ' ' . $appointment['patient_family_name']) . '
            Lékař: ' . htmlspecialchars($appointment['doctor_given_name'] . ' ' . $appointment['doctor_family_name']) . '
            Datum a čas: ' . date("d. m. Y, H:i:s", intval($appointment['timestamp'])) . '
            E-mail na pacienta: ' . ' <a href = "mailto:' . htmlspecialchars($appointment['patient_email']) . '">' .
                htmlspecialchars($appointment['patient_email']) . '</a>
            E-mail na lékaře: ' . ' <a href = "mailto:' . htmlspecialchars($appointment['doctor_email']) . '">' .
                htmlspecialchars($appointment['doctor_email']) . '</a>
            --------------------------------------');
        }

        return $emailBody;
    }

    function prepareEmailBodyPatient($appointment) {
        $emailBody = nl2br('Rezervace byla zrušena.
            Číslo rezervace: ' . htmlspecialchars($appointment['appointment_id']) . '
            Pacient: ' . htmlspecialchars($appointment['patient_given_name'] . ' ' . $appointment['patient_family_name']) . '
            Lékař: ' . htmlspecialchars($appointment['doctor_given_name'] . ' ' . $appointment['doctor_family_name']) . '
            Datum a čas: ' . date("d. m. Y, H:i:s", intval($appointment['timestamp'])) . '
            E-mail na lékaře: ' . ' <a href = "mailto:' . htmlspecialchars($appointment['doctor_email']) . '">' .
            htmlspecialchars($appointment['doctor_email']) . '</a>');

        return $emailBody;
    }

    function deleteOverflowingAppointments($db, $scheduleFrom, $scheduleTo) {
        $baseTimestamp = time();
        $scheduleFrom = strtotime($scheduleFrom, $baseTimestamp);
        $scheduleTo = strtotime($scheduleTo, $baseTimestamp);

        require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/view_appointments_patients_doctors.php';
        $appointmentsQuery = $db->prepare('SELECT * FROM (' . $selectView . ') AS patient_appointment WHERE doctor_id=:doctor_id;');
        $appointmentsQuery->execute([
            ':doctor_id' => $_SESSION['doctor_id']
        ]);
        $appointments = $appointmentsQuery->fetchAll(PDO::FETCH_ASSOC);

        $deletedAppointments = [];
        foreach ($appointments as $appointment) {
            $appointment['timeBaseTimestamp'] = strtotime(date("H:i", $appointment['timestamp']), $baseTimestamp);

            if ($appointment['timeBaseTimestamp'] < $scheduleFrom ||
                $appointment['timeBaseTimestamp'] > $scheduleTo) {
                $deleteAppointmentQuery = $db->prepare('DELETE FROM appointments WHERE appointment_id=:appointment_id LIMIT 1;');
                $deleteAppointmentQuery->execute([
                    ':appointment_id' => $appointment['appointment_id']
                ]);

                array_push($deletedAppointments, $appointment);
            }
        }

        return $deletedAppointments;
    }

    if (isset($_POST['schedule-change'])) {
        if (preg_match("/^(?:2[0-3]|[01][0-9])$/", trim($_POST['schedule-from']))) {
            $errors['schedule-from'] = 'Zadejte platný čas!';
        }

        if (preg_match("/^(?:2[0-3]|[01][0-9])$/", trim($_POST['schedule-to']))) {
            $errors['schedule-to'] = 'Zadejte platný čas!';
        }

        if (empty($errors)) {
            $saveQuery = $db->prepare('UPDATE doctors SET schedule_from=:schedule_from, schedule_to=:schedule_to
                                                WHERE doctor_id=:doctor_id LIMIT 1;');
            $saveQuery->execute([
                ':doctor_id' => $_SESSION['doctor_id'],
                ':schedule_from' => $_POST['schedule-from'],
                ':schedule_to' => $_POST['schedule-to']
            ]);

            $overflowingAppointments = deleteOverflowingAppointments($db, $_POST['schedule-from'], $_POST['schedule-to']);

            require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/email_functions.php';

            foreach ($overflowingAppointments as $appointment) {
                sendMail($appointment['doctor_email'], $_SESSION['given_name'], $_SESSION['family_name'],
                    $appointment['doctor_email'], $_SESSION['given_name'], $_SESSION['family_name'],
                    $appointment['patient_email'], 'Rezervace byla zrušena',
                    prepareEmailBodyPatient($appointment)
                );
            }

            sendMail(end($overflowingAppointments)['doctor_email'], $_SESSION['given_name'], $_SESSION['family_name'],
                end($overflowingAppointments)['doctor_email'], $_SESSION['given_name'], $_SESSION['family_name'],
                end($overflowingAppointments)['doctor_email'], 'Rezervace byla zrušena',
                prepareEmailBodyDoctor($overflowingAppointments)
            );


            header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/doctor/settings.php?success=Pracovní doba byla změněna. Seznam zrušených rezervací Vám byl zaslán na e-mail.');
            exit();
        }
    }

    if (isset($_POST['timeslot-change'])) {
        if (!is_numeric($_POST['timeslot']) || @$_POST['timeslot'] < 1) {
            $errors['timeslot'] = 'Zadejte kladné číslo!';
        }

        if (empty($errors)) {
            $saveQuery = $db->prepare('UPDATE doctors SET timeslot_size=:timeslot_size WHERE doctor_id=:doctor_id LIMIT 1;');
            $saveQuery->execute([
                ':doctor_id' => $_SESSION['doctor_id'],
                ':timeslot_size' => $_POST['timeslot']
            ]);

            header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/doctor/settings.php?success=Délka vyšetření byla změněna.');
            exit();
        }
    }

    if (isset($_POST['password-change'])) {
        $passwordCheckQuery = $db->prepare('SELECT password FROM doctors WHERE doctor_id=:doctor_id LIMIT 1');
        $passwordCheckQuery->execute([
            ':doctor_id' => $_SESSION['doctor_id']
        ]);
        $oldPasswordHash = $passwordCheckQuery->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($_POST['old-password'], $oldPasswordHash['password'])) {
            $errors['old-password'] = 'Stávající heslo není správné.';
        }

        if (empty($_POST['password']) || (strlen($_POST['password']) < 5)) {
            $errors['password'] = 'Musíte zadat heslo o délce alespoň 5 znaků.';
        }
        if ($_POST['password'] != $_POST['password2']) {
            $errors['password2'] = 'Zadaná hesla se neshodují.';
        }

        //uložení dat
        if (empty($errors)) {
            $saveQuery = $db->prepare('UPDATE doctors SET password=:password WHERE doctor_id=:doctor_id LIMIT 1;');
            $saveQuery->execute([
                ':doctor_id' => $_SESSION['doctor_id'],
                ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
            ]);

            header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/doctor/settings.php?success=Heslo bylo úspěšně změněno.');
            exit();
        }
    }

    $currentPage = basename(__FILE__);
    $pageTitle = 'Nastavení lékaře';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';
?>

<?php
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success" role="alert">' . $_GET['success'] . '</div>';
    }
?>

<h2>Změna délky vyšetření</h2>
<div class="alert alert-info">Změna délky vyšetření se projeví až u dalších nově vytvořených rezervací.</div>
<form method="post">
    <div class="form-group">
        <?php
            $timeslotSizeQuery = $db->prepare('SELECT timeslot_size FROM doctors WHERE doctor_id=:doctor_id LIMIT 1;');
            $timeslotSizeQuery->execute([
                ':doctor_id' => $_SESSION['doctor_id']
            ]);
            $timeslot = $timeslotSizeQuery->fetch(PDO::FETCH_ASSOC);
        ?>
        <label for="timeslot">Zadejte novou délku vyšetření v minutách (aktuální délka
            - <?php echo htmlspecialchars(reset($timeslot)); ?>
            minut):</label>
        <input type="number" name="timeslot" id="timeslot" required
               class="form-control <?php echo(!empty($errors['timeslot']) ? 'is-invalid' : ''); ?>"/>
        <?php
            echo(!empty($errors['timeslot']) ? '<div class="invalid-feedback">' . $errors['timeslot'] . '</div>' : '');
        ?>
    </div>
    <input type="hidden" name="timeslot-change" value="true">

    <button type="submit" class="btn btn-primary">Změnit délku vyšetření</button>
</form>

<h2>Změna ordinační doby</h2>
<div class="alert alert-info">Změna ordinační doby způsobí zrušení všech stávajících objednávek začínající před, nebo po
    pracovní
    době.
</div>
<form method="post">
    <div class="form-group">
        <?php
            $scheduleQuery = $db->prepare('SELECT schedule_from, schedule_to FROM doctors WHERE doctor_id=:doctor_id LIMIT 1;');
            $scheduleQuery->execute([
                ':doctor_id' => $_SESSION['doctor_id']
            ]);
            $schedule = $scheduleQuery->fetch(PDO::FETCH_ASSOC);
        ?>
        <label for="schedule-from">Zadejte nový začátek ordinační doby (aktuální
            začátek: <?php echo $schedule['schedule_from'] ?>):</label>
        <input type="time" name="schedule-from" id="schedule-from" required
               class="form-control <?php echo(!empty($errors['schedule-from']) ? 'is-invalid' : ''); ?>"/>
        <?php
            echo(!empty($errors['schedule-from']) ? '<div class="invalid-feedback">' . $errors['schedule-from'] . '</div>' : '');
        ?>
        <label for="schedule-to">Zadejte nový konec ordinační doby (aktuální
            konec: <?php echo $schedule['schedule_to'] ?>):</label>
        <input type="time" name="schedule-to" id="schedule-to" required
               class="form-control <?php echo(!empty($errors['schedule-to']) ? 'is-invalid' : ''); ?>"/>
        <?php
            echo(!empty($errors['schedule-to']) ? '<div class="invalid-feedback">' . $errors['schedule-to'] . '</div>' : '');
        ?>
    </div>
    <input type="hidden" name="schedule-change" value="true">

    <button type="submit" class="btn btn-primary">Změnit ordinační dobu</button>
</form>

<h2>Změna hesla</h2>
<form method="post">
    <div class="form-group">
        <label for="old-password">Stávající heslo:</label>
        <input type="password" name="old-password" id="old-password" required
               class="form-control <?php echo(!empty($errors['old-password']) ? 'is-invalid' : ''); ?>"/>
        <?php
            echo(!empty($errors['old-password']) ? '<div class="invalid-feedback">' . $errors['old-password'] . '</div>' : '');
        ?>
    </div>
    <div class="form-group">
        <label for="password">Nové heslo:</label>
        <input type="password" name="password" id="password" required
               class="form-control <?php echo(!empty($errors['password']) ? 'is-invalid' : ''); ?>"/>
        <?php
            echo(!empty($errors['password']) ? '<div class="invalid-feedback">' . $errors['password'] . '</div>' : '');
        ?>
    </div>
    <div class="form-group">
        <label for="password2">Potvrzení nového hesla:</label>
        <input type="password" name="password2" id="password2" required
               class="form-control <?php echo(!empty($errors['password2']) ? 'is-invalid' : ''); ?>"/>
        <?php
            echo(!empty($errors['password2']) ? '<div class="invalid-feedback">' . $errors['password2'] . '</div>' : '');
        ?>
    </div>
    <input type="hidden" name="password-change" value="true">

    <button type="submit" class="btn btn-primary">Změnit heslo</button>
</form>
