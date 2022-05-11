<?php
    //načteme připojení k databázi a inicializujeme session
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/user.php';

    if (!empty($_SESSION['doctor_id']) || !empty($_SESSION['patient_id'])) {
        // uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
        header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php');
        exit();
    }

    $errors = false;
    if (!empty($_POST)) {
        #region zpracování formuláře
        if (@$_POST['is-doctor'] == 'is-doctor') {
            $userQuery = $db->prepare('SELECT * FROM doctors WHERE email=:email LIMIT 1;');
        } else {
            $userQuery = $db->prepare('SELECT * FROM patients WHERE email=:email LIMIT 1;');
        }
        $userQuery->execute([
            ':email' => trim($_POST['email'])
        ]);

        if ($user = $userQuery->fetch(PDO::FETCH_ASSOC)) {

            if (password_verify($_POST['password'], $user['password'])) {
                //heslo je platné => přihlásíme uživatele

                if (@$_POST['is-doctor'] == 'is-doctor') {
                    $_SESSION['doctor_id'] = $user['doctor_id'];
                } else {
                    $_SESSION['patient_id'] = $user['patient_id'];
                }
                $_SESSION['given_name'] = $user['given_name'];
                $_SESSION['family_name'] = $user['family_name'];
                $_SESSION['email'] = $user['email'];

                header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php');
                exit();

            } else {
                $errors = true;
            }
        } else {
            $errors = true;
        }
        #endregion zpracování formuláře
    }

    //vložíme do stránek hlavičku
    $pageTitle = 'Přihlášení uživatele';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';
?>

    <h2>Přihlášení uživatele</h2>

    <form method="post">
        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required
                   class="form-control <?php echo($errors ? 'is-invalid' : ''); ?>"
                   value="<?php echo htmlspecialchars(@$_POST['email']) ?>"
            />
        </div>
        <div class="form-group">
            <label for="password">Heslo:</label>
            <input type="password" name="password" id="password" required
                   class="form-control <?php echo($errors ? 'is-invalid' : ''); ?>"
            />
            <?php
                echo($errors ? '<div class="invalid-feedback">Neplatná kombinace přihlašovacího e-mailu a hesla!</div>' : '');
            ?>
        </div>
        <div class="form-group">
            <input type="checkbox" name="is-doctor" id="is-doctor" value="is-doctor"
                <?php echo(isset($_POST['is-doctor']) ? 'checked' : ''); ?>
            />
            <label for="is-doctor">Přihlašuji se jako lékař</label>
        </div>
        <button type="submit" class="btn btn-primary">Přihlásit se</button>
        <a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/registration.php" class="btn btn-light">Registrovat
            se</a>
        <a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php" class="btn btn-light">Zrušit</a>
    </form>

<?php
    //vložíme do stránek patičku
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';
