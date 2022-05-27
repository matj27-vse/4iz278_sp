<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_doctor.php';

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

    function sendMailToPatient($appointment) {
        $chyby = [];

        if (!filter_var($appointment['patient_email'], FILTER_VALIDATE_EMAIL)) {
            $chyby['to'] = 'E-mail příjemce nemá platný formát.';
        }

        $subject = 'Zrušení rezervace';

        $emailBody = nl2br('
            Rezervace byla zrušena.
            Číslo rezervace: ' . htmlspecialchars($appointment['appointment_id']) . '
            Pacient: ' . htmlspecialchars($appointment['patient_given_name'] . ' ' . $appointment['patient_family_name']) . '
            Lékař: ' . htmlspecialchars($appointment['doctor_given_name'] . ' ' . $appointment['doctor_family_name']) . '
            Datum a čas: ' . date("d. m. Y, H:i:s", intval($appointment['timestamp'])) . '
            E-mail na lékaře: ' . ' <a href = "mailto:' . htmlspecialchars($appointment['doctor_email']) . '">' .
            htmlspecialchars($appointment['doctor_email']) . '</a>');
        $emailHtml = '
        <html lang="cs">
        <head>
            <title>' . $subject . '</title>
        </head>
        <body>' . $emailBody . '</body>
        </html>';

        $hlavicky = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8', //pokud chceme správně kódování a neřešit ruční kódování do mailu
            'From: ' . $appointment['doctor_given_name'] . ' ' . $appointment['doctor_family_name'] . '<' . $appointment['doctor_email'] . '>', //pokud byste v mailu chtěli nejen adresu, ale i jméno odesílatele, může tu být tvar: From: Jméno Příjmení<email@domain.tld> (obdobně u dalších hlaviček)
            'Reply-To: ' . $appointment['doctor_given_name'] . ' ' . $appointment['doctor_family_name'] . '<' . $appointment['doctor_email'] . '>',
            'X-Mailer: PHP/' . phpversion()
        ];

        $hlavicky = implode("\r\n", $hlavicky);

        if (empty($chyby)) {
            //mail($appointment['patient_email'], $subject, $emailHtml, $hlavicky);
            mail('matj27@vse.cz', $subject, $emailHtml, $hlavicky);
        }
    }

    $errors = [];
    if (!empty($_GET['appointment_id'])) {
        $appointmentQuery = $db->prepare('SELECT * FROM (' . $selectView . ') AS patients_appointments WHERE appointment_id=:appointment_id LIMIT 1;');
        $appointmentQuery->execute([
            ':appointment_id' => $_GET['appointment_id']
        ]);
        $appointment = $appointmentQuery->fetch(PDO::FETCH_ASSOC);

        if ($appointment['doctor_id'] == $_SESSION['doctor_id']) {
            $deleteQuery = $db->prepare('DELETE FROM appointments WHERE appointment_id=:appointment_id;');
            if (
                !$deleteQuery->execute([
                    ':appointment_id' => $_GET['appointment_id']
                ])
            ) {
                $errors['not_deleted'] = 'Chyba aplikace při odstraňování rezervace!';
            } else {
                sendMailToPatient($appointment);
            }
        } else {
            $errors['different_doctor'] = 'Nebyla zvolena přístupná rezervace!';
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
        echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/doctor/index.php" class="mr-1 btn btn-primary">Pokračovat</a>';
    } else {
        echo '<div class="alert alert-success" role="alert">';
        echo 'Rezervace byla úspěšně zrušena.';
        echo '</div>';
        echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/doctor/index.php" class="mr-1 btn btn-primary">Pokračovat</a>';
    }

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';
