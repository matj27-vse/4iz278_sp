<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_patient.php';
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/email_functions.php';

    $selectView = 'SELECT 
                        appointments.appointment_id, 
                        appointments.timestamp, 
                        appointments.confirmed, 
                        doctors.doctor_id, 
                        doctors.given_name AS doctor_given_name,
                        doctors.family_name AS doctor_family_name,
                        doctors.email AS doctor_email,
                        patients.patient_id,
                        patients.given_name AS patient_given_name,
                        patients.family_name AS patient_family_name,
                        patients.email AS patient_email
                    FROM appointments
                    JOIN doctors ON appointments.doctor_id = doctors.doctor_id
                    JOIN patients ON appointments.patient_id = patients.patient_id';

    function prepareEmailBody($appointment) {
        $emailBody = nl2br('
            Rezervace byla zrušena pacientem.
            Číslo rezervace: ' . htmlspecialchars($appointment['appointment_id']) . '
            Pacient: ' . htmlspecialchars($appointment['patient_given_name'] . ' ' . $appointment['patient_family_name']) . '
            Lékař: ' . htmlspecialchars($appointment['doctor_given_name'] . ' ' . $appointment['doctor_family_name']) . '
            Datum a čas: ' . date("d. m. Y, H:i:s", intval($appointment['timestamp'])) . '
            E-mail na pacienta: ' . ' <a href = "mailto:' . htmlspecialchars($appointment['patient_email']) . '">' .
            htmlspecialchars($appointment['patient_email']) . '</a>');

        return $emailBody;
    }

    $errors = [];
    if (!empty($_GET['appointment_id'])) {
        $appointmentQuery = $db->prepare('SELECT * FROM (' . $selectView . ') AS patients_appointments WHERE appointment_id=:appointment_id LIMIT 1;');
        $appointmentQuery->execute([
            ':appointment_id' => $_GET['appointment_id']
        ]);
        $appointment = $appointmentQuery->fetch(PDO::FETCH_ASSOC);

        if ($appointment['patient_id'] == $_SESSION['patient_id']) {
            $deleteQuery = $db->prepare('DELETE FROM appointments WHERE appointment_id=:appointment_id;');
            if (
                !$deleteQuery->execute([
                    ':appointment_id' => $_GET['appointment_id']
                ])
            ) {
                $errors['not_deleted'] = 'Chyba aplikace při odstraňování rezervace!';
            } else {
                sendMail(
                    $appointment['patient_email'], $appointment['patient_given_name'], $appointment['patient_family_name'],
                    $appointment['patient_email'], $appointment['patient_given_name'], $appointment['patient_family_name'],
                    $appointment['doctor_email'], 'Zrušení rezervace', prepareEmailBody($appointment)
                );
            }
        } else {
            $errors['different_patient'] = 'Nebyla zvolena přístupná rezervace!';
        }
    } else {
        $errors['not_selected'] = 'Nebyla zvolena rezervace pro zrušení!';
    }

    $pageTitle = 'Zrušení rezervace';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<div class="alert alert-danger" role="alert">';
            echo htmlspecialchars($error);
            echo '</div>';
        }
        echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/index.php" class="mr-1 btn btn-primary">Pokračovat</a>';
    } else {
        echo '<div class="alert alert-success" role="alert">';
        echo 'Rezervace byla úspěšně zrušena.';
        echo '</div>';
        echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/index.php" class="mr-1 btn btn-primary">Pokračovat</a>';
    }

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';
