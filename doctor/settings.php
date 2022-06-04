<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_doctor.php';

    $errors = [];

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

    if (isset($_POST['timeslot-change'])) {
        if (!is_numeric($_POST['timeslot']) || @$_POST['timeslot'] < 1) {
            $errors['timeslot'] = 'Zadejte kladné číslo!';
        }

        if(empty($errors)){
            $saveQuery = $db->prepare('UPDATE doctors SET timeslot_size=:timeslot_size WHERE doctor_id=:doctor_id LIMIT 1;');
            $saveQuery->execute([
                ':doctor_id' => $_SESSION['doctor_id'],
                ':timeslot_size' => $_POST['timeslot']
            ]);

            header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/doctor/settings.php?success=Délka vyšetření byla změněna.');
            exit();
        }
    }

    $currentPage = basename(__FILE__);
    $pageTitle = 'Nastavení lékaře';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

    //todo změna ordinačních hodin
?>

<?php
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success" role="alert">' . $_GET['success'] . '</div>';
    }
?>

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
        <label for="timeslot">Zadejte novou délku vyšetření v minutách (aktuální délka: <?php echo reset($timeslot) ?>
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
