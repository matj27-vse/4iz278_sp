<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/user.php';
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/email_functions.php';

    function prepareEmailBody($link) {
        $emailBody = nl2br('
            Pro obnovu helsa prosím kliněte na tento odkaz: <a href=' . $link . '>' . $link . '</a>
            Pokud jste nežádal/a o obnovu hesla k Vašemu účtu, považujte tento e-mail za bezpředmětný.'
        );
        return $emailBody;
    }

    $errors = [];

    if (!(empty($_SESSION['doctor_id']) && empty(($_SESSION['patient_id'])))) {
        //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
        header('Location: index.php');
        exit();
    }

    if (!empty($_POST) && !empty($_POST['email'])) {
        $doctor = false;
        if (@$_POST['is-doctor'] == 'is-doctor') {
            $doctor = true;
        }

        #region zpracování formuláře
        if ($doctor) {
            $userQuery = $db->prepare('SELECT * FROM doctors WHERE email=:email LIMIT 1;');
        } else {
            $userQuery = $db->prepare('SELECT * FROM patients WHERE email=:email LIMIT 1;');
        }
        $userQuery->execute([
            ':email' => trim($_POST['email'])
        ]);
        if ($user = $userQuery->fetch(PDO::FETCH_ASSOC)) {
            //zadaný e-mail byl nalezen

            #region vygenerování kódu pro obnovu hesla
            $code = 'xx' . rand(100000, 993952); //rozhodně by tu mohlo být i kreativnější generování náhodného kódu :)

            //uložíme kód do databáze
            if ($doctor) {
                $saveQuery = $db->prepare('INSERT INTO forgotten_passwords_doctors (doctor_id, code, created) VALUES (:user, :code, :created);');
            } else {
                $saveQuery = $db->prepare('INSERT INTO forgotten_passwords_patients (patient_id, code, created) VALUES (:user, :code, :created);');
            }
            $saveQuery->execute([
                ':user' => $user[($doctor ? 'doctor' : 'patient') . '_id'],
                ':code' => $code,
                ':created' => time()
            ]);

            //načteme uložený záznam z databáze
            if ($doctor) {
                $requestQuery = $db->prepare('SELECT * FROM forgotten_passwords_doctors WHERE doctor_id=:user AND code=:code ORDER BY id DESC LIMIT 1;');
            } else {
                $requestQuery = $db->prepare('SELECT * FROM forgotten_passwords_patients WHERE patient_id=:user AND code=:code ORDER BY id DESC LIMIT 1;');
            }
            $requestQuery->execute([
                ':user' => $user[($doctor ? 'doctor' : 'patient') . '_id'],
                ':code' => $code
            ]);
            $request = $requestQuery->fetch(PDO::FETCH_ASSOC);

            //sestavíme odkaz pro mail
            $link = 'https://eso.vse.cz/~matj27/4iz278/semestralni_prace/renew_password.php';
            if ($doctor) {
                $link .= '?doctor_id=' . $request['doctor_id'];
            } else {
                $link .= '?patient_id=' . $request['patient_id'];
            }
            $link .= '&code=' . $request['code'] . '&request=' . $request['id'];
            #endregion vygenerování kódu pro obnovu hesla

            #region odeslani emailu
            sendMail(
                'system@objednacikalendar.vse.cz', 'Objednací', 'Kalendář',
                'noreply@objednacikalendar.vse.cz', '', '',
                $user['email'], 'Obnovení hesla', prepareEmailBody($link)
            );
            #endregion odeslani emailu

            //přesměrování pro potvrzení
            header('Location: forgotten_password.php?mailed=1');
        } else {
            $errors['email'] = 'Došlo k chybě při zaslání e-mailu.';
        }
    }

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';
?>

    <h2>Obnova zapomenutého hesla</h2>
<?php
    if (@$_GET['mailed'] == 1) {
        echo '<div class="alert alert-info" role="alert">
                Zkontrolujte svoji e-mailovou schránku a klikněte na odkaz, který vám byl zaslán mailem.
                Mějte prosím na paměti, že email může dorazit až za několik minut.
              </div>';
        echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php" class="mr-1 btn btn-primary">Pokračovat</a>';

    } else {
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo '<div class="alert alert-danger" role="alert">';
                echo htmlspecialchars($error);
                echo '</div>';
            }
        }
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
