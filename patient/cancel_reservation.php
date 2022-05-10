<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_patient.php';

    $errors = [];
    if (!empty($_GET['appointment_id'])) {
        $appointmentQuery = $db->prepare('SELECT * FROM appointments WHERE appointment_id=:appointment_id LIMIT 1;');
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
                //todo odeslat email lékaři
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
