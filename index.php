<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/user.php';

    if (empty($_SESSION['doctor_id']) && empty($_SESSION['patient_id'])) {
        header('Location: login.php');
        exit();
    }

    if (empty(@$_REQUEST['error'])) {
        if (!empty(@$_SESSION['doctor_id'])) {
            header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/doctor/index.php');
        }

        if (!empty(@$_SESSION['patient_id'])) {
            header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/index.php');
        }
    }

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

    if (!empty(@$_REQUEST['error'])) {
        echo '<div class="alert alert-danger" role="alert">';
        echo htmlspecialchars($_REQUEST['error']);
        echo '</div>';
        echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php" class="mr-1 btn btn-primary">Pokračovat</a>';
        echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/logout.php" class="mr-1 btn btn-light">Odhlásit se</a>';
    }

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';
