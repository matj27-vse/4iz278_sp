<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_doctor.php';
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/email_functions.php';

    function prepareEmailBody($appointment) {
        $emailBody = nl2br('
            Rezervace byla potvrzena lékařem.
            Číslo rezervace: ' . htmlspecialchars($appointment['appointment_id']) . '
            Pacient: ' . htmlspecialchars($appointment['patient_given_name'] . ' ' . $appointment['patient_family_name']) . '
            Lékař: ' . htmlspecialchars($appointment['doctor_given_name'] . ' ' . $appointment['doctor_family_name']) . '
            Datum a čas: ' . date("d. m. Y, H:i:s", intval($appointment['timestamp'])) . '
            E-mail na lékaře: ' . ' <a href = "mailto:' . htmlspecialchars($appointment['doctor_email']) . '">' .
            htmlspecialchars($appointment['doctor_email']) . '</a>');

        return $emailBody;
    }

    $errors = [];
    $patientAlreadyVisited = false;    //todo alert ještě tu tenhle pacient nebyl

    if (!empty($_GET['appointment_id'])) {
        $appointmentQuery = $db->prepare('SELECT * FROM appointments WHERE appointment_id=:appointment_id LIMIT 1;');
        $appointmentQuery->execute([
            ':appointment_id' => $_GET['appointment_id']
        ]);
        $appointment = $appointmentQuery->fetch(PDO::FETCH_ASSOC);
    } else {
        $errors['appointment_not_chosen'] = 'Nebyla zvolena rezervace pro potvrzení.';
    }

    if (empty($errors)) {
        if ($appointment['doctor_id'] == $_SESSION['doctor_id']) {
            $appointmentQuery = $db->prepare('UPDATE appointments SET confirmed = 1 WHERE appointment_id = :appointment_id;');
            if (
                $appointmentQuery->execute([
                    ':appointment_id' => $appointment['appointment_id']
                ])
            ) {
                sendMail(
                    $appointment['doctor_email'], $appointment['doctor_given_name'], $appointment['doctor_family_name'],
                    $appointment['doctor_email'], $appointment['doctor_given_name'], $appointment['doctor_family_name'],
                    $appointment['patient_email'], 'Potvrzení rezervace', prepareEmailBody($appointment)
                );
            }
        } else {
            $errors['different_doctor'] = 'Nemáte oprávnění pro správu této rezervace.';
        }
    }

    $currentPage = basename(__FILE__);
    $pageTitle = 'Potvrzení rezervace';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

    if (empty($errors)) {
        echo '<div class="alert alert-success" role="alert">Rezervace byla úspěšně potvrzena</div>';
        if (!$patientAlreadyVisited) {
            echo '<div class="alert alert-warning" role="alert">Tento pacient Vás ještě nenavštívil.</div>';
        }
    } else {
        foreach ($errors as $error) {
            echo '<div class="alert alert-danger" role="alert">';
            echo htmlspecialchars($error);
            echo '</div>';
        }
    }

