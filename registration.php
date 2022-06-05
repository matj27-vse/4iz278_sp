<?php
    //načteme připojení k databázi a inicializujeme session
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/user.php';

    if (!empty($_SESSION['doctor_id']) || !empty($_SESSION['patient_id'])) {
        // uživatel už je přihlášený, nemá smysl, aby se registroval
        header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php');
        exit();
    }

    $errors = [];
    if (!empty($_POST)) {
        #region zpracování formuláře

        #region kontrola jména
        $givenName = trim(@$_POST['given-name']);
        if (empty($givenName)) {
            $errors['given-name'] = 'Musíte zadat své jméno.';
        }
        #endregion kontrola jména

        #region kontrola příjmení
        $familyName = trim(@$_POST['family-name']);
        if (empty($familyName)) {
            $errors['family-name'] = 'Musíte zadat své příjmení.';
        }
        #endregion kontrola příjmení

        $registeredWithFacebook = false;
        $patient = null;

        #region kontrola emailu
        $email = trim(@$_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Musíte zadat platnou e-mailovou adresu.';
        } else {
            //kontrola, jestli již není e-mail registrovaný
            $mailQuery = $db->prepare('SELECT * FROM patients WHERE email=:email LIMIT 1;');
            $mailQuery->execute([
                ':email' => $email
            ]);
            if ($mailQuery->rowCount() > 0) {
                $patient = $mailQuery->fetch(PDO::FETCH_ASSOC);
                if ($patient['password'] == '' && $patient['facebook_id'] != '') {
                    $registeredWithFacebook = true;
                } else {
                    $errors['email'] = 'Uživatelský účet s touto e-mailovou adresou již existuje.';
                }
            }
        }
        #endregion kontrola emailu

        #region kontrola hesla
        if (empty($_POST['password']) || (strlen($_POST['password']) < 5)) {
            $errors['password'] = 'Musíte zadat heslo o délce alespoň 5 znaků.';
        }
        if ($_POST['password'] != $_POST['password2']) {
            $errors['password2'] = 'Zadaná hesla se neshodují.';
        }
        #endregion kontrola hesla

        if (empty($errors)) {
            //zaregistrování uživatele
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            if ($registeredWithFacebook) {
                $updateUserQuery = $db->prepare('UPDATE patients SET password=:password WHERE patient_id=:patient_id;');
                $updateUserQuery->execute([
                    ':patient_id' => $patient['patient_id'],
                    ':password' => $password
                ]);
            } else {
                $query = $db->prepare('INSERT INTO patients (given_name, family_name, email, password, active) 
                                            VALUES (:givenName, :familyName, :email, :password, :active);');
                $query->execute([
                    ':givenName' => $givenName,
                    ':familyName' => $familyName,
                    ':email' => $email,
                    ':password' => $password,
                    ':active' => 1
                ]);
            }

            //uživatele rovnou přihlásíme
            $_SESSION['patient_id'] = ($patient ? $patient['patient_id'] : $db->lastInsertId());
            $_SESSION['given_name'] = ($patient ? $patient['given_name'] : $givenName);
            $_SESSION['family_name'] = ($patient ? $patient['family_name'] : $familyName);

            //přesměrování na homepage
            header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/index.php?succ=Registrace byla úspěšná. Vítejte v aplikaci!');
            exit();
        }

        #endregion zpracování formuláře
    }

    //vložíme do stránek hlavičku
    $pageTitle = 'Registrace nového uživatele';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';
?>

    <h2>Registrace nového uživatele</h2>

    <div class="alert alert-info" role="alert">
        Tento formulář slouží pro registraci pacientů. Chcete-li se registrovat jako lékař, obraťe se na správce
        aplikace.
    </div>

    <form method="post">
        <div class="form-group">
            <label for="given-name">Jméno:</label>
            <input type="text" name="given-name" id="given-name" required
                   class="form-control <?php echo(!empty($errors['given-name']) ? 'is-invalid' : ''); ?>"
                   value="<?php echo htmlspecialchars(@$givenName); ?>"/>
            <?php
                echo(!empty($errors['given-name']) ? '<div class="invalid-feedback">' . $errors['given-name'] . '</div>' : '');
            ?>
        </div>
        <div class="form-group">
            <label for="family-name">Příjmení:</label>
            <input type="text" name="family-name" id="family-name" required
                   class="form-control <?php echo(!empty($errors['family-name']) ? 'is-invalid' : ''); ?>"
                   value="<?php echo htmlspecialchars(@$familyName); ?>"/>
            <?php
                echo(!empty($errors['family-name']) ? '<div class="invalid-feedback">' . $errors['family-name'] . '</div>' : '');
            ?>
        </div>
        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required
                   class="form-control <?php echo(!empty($errors['email']) ? 'is-invalid' : ''); ?>"
                   value="<?php echo htmlspecialchars(@$email); ?>"/>
            <?php
                echo(!empty($errors['email']) ? '<div class="invalid-feedback">' . $errors['email'] . '</div>' : '');
            ?>
        </div>
        <div class="form-group">
            <label for="password">Heslo:</label>
            <input type="password" name="password" id="password" required
                   class="form-control <?php echo(!empty($errors['password']) ? 'is-invalid' : ''); ?>"/>
            <?php
                echo(!empty($errors['password']) ? '<div class="invalid-feedback">' . $errors['password'] . '</div>' : '');
            ?>
        </div>
        <div class="form-group">
            <label for="password2">Potvrzení hesla:</label>
            <input type="password" name="password2" id="password2" required
                   class="form-control <?php echo(!empty($errors['password2']) ? 'is-invalid' : ''); ?>"/>
            <?php
                echo(!empty($errors['password2']) ? '<div class="invalid-feedback">' . $errors['password2'] . '</div>' : '');
            ?>
        </div>
        <button type="submit" class="btn btn-primary">Registrovat se</button>
        <a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/login.php" class="btn btn-light">Zpět na
            přihlášení</a>
        <a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php" class="btn btn-light">Zrušit</a>
    </form>

<?php
    //vložíme do stránek patičku
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';