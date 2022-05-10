<?php

    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start(); //spustíme session
    }

    require_once 'db.php'; //načteme připojení k databázi

    if (!empty($_SESSION['patient_id'])) {
        $userQuery = $db->prepare('SELECT patient_id FROM patients WHERE patient_id=:id AND active=1 LIMIT 1;');
        $userQuery->execute([
            ':id' => $_SESSION['patient_id']
        ]);
        if ($userQuery->rowCount() != 1) {
            //uživatel už není v DB, nebo není aktivní => musíme ho odhlásit
            header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/logout.php?error=Pro přístup k této části aplikace nemáte dostatečná práva.');
            exit();
        }
    } else {
        //v session není uložené id pacienta
        header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/logout.php?error=Pro přístup k této části aplikace nemáte dostatečná práva.');
        exit();
    }
