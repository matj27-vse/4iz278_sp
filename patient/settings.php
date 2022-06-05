<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_patient.php';

    $errors = [];

    if (isset($_REQUEST['password-change'])) {
        if (!isset($_REQUEST['new-password'])) {
            $passwordCheckQuery = $db->prepare('SELECT password FROM patients WHERE patient_id=:patient_id LIMIT 1');
            $passwordCheckQuery->execute([
                ':patient_id' => $_SESSION['patient_id']
            ]);
            $oldPasswordHash = $passwordCheckQuery->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($_POST['old-password'], $oldPasswordHash['password'])) {
                $errors['old-password'] = 'Stávající heslo není správné.';
            }
        }

        if (empty($_POST['password']) || (strlen($_POST['password']) < 5)) {
            $errors['password'] = 'Musíte zadat heslo o délce alespoň 5 znaků.';
        }
        if ($_POST['password'] != $_POST['password2']) {
            $errors['password2'] = 'Zadaná hesla se neshodují.';
        }

        //uložení dat
        if (empty($errors)) {
            $saveQuery = $db->prepare('UPDATE patients SET password=:password WHERE patient_id=:patient_id LIMIT 1;');
            $saveQuery->execute([
                ':patient_id' => $_SESSION['patient_id'],
                ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
            ]);

            header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/settings.php?success=Heslo bylo úspěšně změněno.');
            exit();
        }
    }

    $currentPage = basename(__FILE__);
    $pageTitle = 'Nastavení pacienta';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';
?>

<?php
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success" role="alert">' . $_GET['success'] . '</div>';
    }
?>

<?php
    $passwordSetQuery = $db->prepare('SELECT password,facebook_id FROM patients WHERE patient_id=:patient_id LIMIT 1');
    $passwordSetQuery->execute([
        ':patient_id' => $_SESSION['patient_id']
    ]);
    $passwordSet = $passwordSetQuery->fetch(PDO::FETCH_ASSOC);

    if ($passwordSet['password'] != '') { ?>
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
            <?php
    } else if ($passwordSet['password'] == '' && $passwordSet['facebook_id'] != '') { ?>
        <h2>Nastavení hesla pro přihlášení přes e-mail</h2>
        <form method="post">
            <input type="hidden" name="new-password" value="true">
        <?php
    } ?>
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
