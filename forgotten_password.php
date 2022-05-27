<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/user.php';

    if (!(empty($_SESSION['doctor_id']) && empty(($_SESSION['patient_id'])))) {
        //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
        header('Location: index.php');
        exit();
    }

    $errors = [];

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';
?>


    <h2>Obnova zapomenutého hesla</h2>
<?php
    if (@$_GET['mailed'] == 1) {
        echo '<div class="alert alert-info" role="alert">Zkontrolujte svoji e-mailovou schránku a klikněte na odkaz, který vám byl zaslán mailem.</div>';
        echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php" class="mr-1 btn btn-primary">Pokračovat</a>';

    } else {
        ?>
        <form method="post">
        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required
                   class="form-control <?php echo($errors ? 'is-invalid' : ''); ?>"
                   value="<?php echo htmlspecialchars(@$_POST['email']) ?>"
            />
        </div>
        <div class="form-group">
            <input type="checkbox" name="is-doctor" id="is-doctor" value="is-doctor"
                <?php echo(isset($_POST['is-doctor']) ? 'checked' : ''); ?>
            />
            <label for="is-doctor">Požaduji změnu hesla k účtu lékaře</label>
        </div>
        <button type="submit" class="btn btn-primary">Zaslat e-mail pro obnovu hesla</button>
        <a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/login.php" class="btn btn-light">Zpět na
            přihlášení</a>
        <a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php" class="btn btn-light">Zrušit</a>
        <?php
    }
?>
<?php
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';
