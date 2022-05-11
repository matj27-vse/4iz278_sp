<?php
    //načteme připojení k databázi a inicializujeme session
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/user.php';

    //smažeme ze session identifikaci uživatele
    unset($_SESSION['doctor_id']);
    unset($_SESSION['patient_id']);
    unset($_SESSION['given_name']);
    unset($_SESSION['family_name']);
    unset($_SESSION['email']);

    //přesměrujeme uživatele na homepage

    $href = 'https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php?';
    if (!empty($_REQUEST['error'])) {
        $href .= 'error=' . $_REQUEST['error'];
    }
    header('Location: ' . $href);