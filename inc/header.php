<?php
//načteme připojení k databázi a inicializujeme session
require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/user.php';

/*
if (!empty($_SESSION['user_id'])) {
    $isUserAdminQuery = $db->prepare('SELECT * FROM users WHERE user_id=:user_id LIMIT 1;');
    $isUserAdminQuery->execute([
        'user_id' => $_SESSION['user_id']
    ]);
    $user = $isUserAdminQuery->fetch(PDO::FETCH_ASSOC);
    $admin = ($user['admin'] == 1);
}
*/
?><!DOCTYPE html>
<html lang="cs">
<head>
    <title><?php echo(!empty($pageTitle) ? $pageTitle . ' - ' : '') ?>Objednací kalendář</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <?php
        if (@$currentPage == 'reservation.php') {
            echo('<link href="https://cdn.jsdelivr.net/npm/mc-datepicker/dist/mc-calendar.min.css" rel="stylesheet" />');
        }
    ?>
</head>
<body>
    <header class="container bg-dark">
        <h1 class="text-white py-4 px-2"><?php echo(!empty($pageTitle) ? $pageTitle : 'Objednací kalendář') ?></h1>

        <div class="text-right text-white">
            <?php
                if (!empty($_SESSION['patient_id']) || !empty($_SESSION['doctor_id'])) {
                    echo '<strong>' .
                        htmlspecialchars($_SESSION['given_name']) . ' ' . htmlspecialchars($_SESSION['family_name']) .
                        '</strong>';
                    echo ' - ';
                    echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/logout.php" class="text-white">Odhlásit se</a>';
                } else {
                    echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/login.php" class="text-white">Přihlásit se</a>';
                }
            ?>
        </div>
        <?php
            if (!empty($_SESSION['patient_id'])) {
                include 'navbar_patient.php';
            } elseif (!empty($_SESSION['doctor_id'])) {
                include 'navbar_doctor.php';
            }
        ?>
    </header>
    <main class="container pt-2">