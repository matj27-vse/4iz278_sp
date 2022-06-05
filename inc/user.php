<?php

    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/vendor/autoload.php';//načtení class loaderu vytvořeného composerem

    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start(); //spustíme session
    }

    require_once 'db.php'; //načteme připojení k databázi

    #region kontrola, jestli je přihlášený uživatel platný
    if (!empty($_SESSION['doctor_id'])) {
        $userQuery = $db->prepare('SELECT doctor_id FROM doctors WHERE doctor_id=:id AND active=1 LIMIT 1;');
        $userQuery->execute([
            ':id' => $_SESSION['doctor_id']
        ]);
        if ($userQuery->rowCount() != 1) {
            //uživatel už není v DB, nebo není aktivní => musíme ho odhlásit
            header('Location: logout.php');
            exit();
        }
    }

    if (!empty($_SESSION['patient_id'])) {
        $userQuery = $db->prepare('SELECT patient_id FROM patients WHERE patient_id=:id AND active=1 LIMIT 1;');
        $userQuery->execute([
            ':id' => $_SESSION['patient_id']
        ]);
        if ($userQuery->rowCount() != 1) {
            //uživatel už není v DB, nebo není aktivní => musíme ho odhlásit
            header('Location: logout.php');
            exit();
        }
    }
    #endregion kontrola, jestli je přihlášený uživatel platný