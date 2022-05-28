<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/user.php';

    $errors = [];

    if (!(empty($_SESSION['doctor_id']) && empty(($_SESSION['patient_id'])))) {
        //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
        header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php');
        exit();
    }

    $isDoctor = false;
    if (isset($_REQUEST['doctor_id']) && !isset($_REQUEST['patient_id'])) {
        $isDoctor = true;
    }

    $invalidCode = false;

    if (!empty($_REQUEST) && !empty($_REQUEST['code'])) {
        #region kontrola, jestli se daný kód shoduje s údaji v databázi
        if ($isDoctor) {
            $query = $db->prepare('SELECT * FROM forgotten_passwords_doctors WHERE id=:id AND code=:code AND doctor_id=:user_id LIMIT 1;');
        } else {
            $query = $db->prepare('SELECT * FROM forgotten_passwords_patients WHERE id=:id AND code=:code AND patient_id=:user_id LIMIT 1;');
        }
        $query->execute([
            ':user_id' => $_REQUEST[($isDoctor ? 'doctor_id' : 'patient_id')],
            ':code' => $_REQUEST['code'],
            ':id' => $_REQUEST['request'],
        ]);

        if ($existingRequest = $query->fetch(PDO::FETCH_ASSOC)) {
            //zkontrolujeme, jestli je kód ještě platný
            if (strtotime($existingRequest['created']) > (time() - (12 * 60 * 60))) {
                $invalidCode = true;
            }
        } else {
            $invalidCode = true;
        }
        #endregion kontrola, jestli se daný kód shoduje s údaji v databázi

        if (!empty($_POST) && !$invalidCode) {
            #region změna zapomenutého hesla
            //kontrola dat z formuláře
            if (empty($_POST['password']) || (strlen($_POST['password']) < 5)) {
                $errors['password'] = 'Musíte zadat heslo o délce alespoň 5 znaků.';
            }
            if ($_POST['password'] != $_POST['password2']) {
                $errors['password2'] = 'Zadaná hesla se neshodují.';
            }

            //uložení dat
            if (empty($errors)) {
                if ($isDoctor) {
                    $saveQuery = $db->prepare('UPDATE doctors SET password=:password WHERE doctor_id=:user_id LIMIT 1;');
                } else {
                    $saveQuery = $db->prepare('UPDATE patients SET password=:password WHERE patient_id=:user_id LIMIT 1;');
                }
                $saveQuery->execute([
                    ':user_id' => $existingRequest[($isDoctor ? 'doctor_id' : 'patient_id')],
                    ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
                ]);

                //smažeme požadavky na obnovu hesla
                if ($isDoctor) {
                    $forgottenDeleteQuery = $db->prepare('DELETE FROM forgotten_passwords_doctors WHERE doctor_id=:user_id OR created<:limit;');
                } else {
                    $forgottenDeleteQuery = $db->prepare('DELETE FROM forgotten_passwords_patients WHERE patient_id=:user_id OR created<:limit;');
                }
                $forgottenDeleteQuery->execute([
                    ':user_id' => $existingRequest[($isDoctor ? 'doctor_id' : 'patient_id')],
                    ':limit' => strval(time() - (12 * 60 * 60))
                ]);

                //načteme údaje o aktuálním uživateli
                if ($isDoctor) {
                    $userQuery = $db->prepare('SELECT * FROM doctors WHERE doctor_id=:user_id LIMIT 1;');
                } else {
                    $userQuery = $db->prepare('SELECT * FROM patients WHERE patient_id=:user_id LIMIT 1;');
                }
                $userQuery->execute([
                    ':user_id' => $existingRequest[($isDoctor ? 'doctor_id' : 'patient_id')]
                ]);
                $user = $userQuery->fetch(PDO::FETCH_ASSOC);

                //uživatele rovnou přihlásíme
                if ($isDoctor) {
                    $_SESSION['doctor_id'] = $user['doctor_id'];
                } else {
                    $_SESSION['patient_id'] = $user['patient_id'];
                }
                $_SESSION['given_name'] = $user['given_name'];
                $_SESSION['family_name'] = $user['family_name'];
                $_SESSION['email'] = $user['email'];

                //přesměrování na homepage
                header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php');
                exit();
            }
            #endregion změna zapomenutého hesla
        }
    }

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

    echo '<h2>Obnova zapomenutého hesla</h2>';

    if ($invalidCode) {
        echo '<div class="alert alert-info" role="alert">Kód pro obnovu hesla není platný.</div>';
        echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php" class="mr-1 btn btn-primary">Pokračovat</a>';
    } else {
        ?>
        <form method="post">
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

            <input type="hidden" name="code" value="<?php echo htmlspecialchars($_REQUEST['code']); ?>"/>
            <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars(@$_REQUEST['doctor_id']); ?>"/>
            <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars(@$_REQUEST['patient_id']); ?>"/>
            <input type="hidden" name="request" value="<?php echo htmlspecialchars($_REQUEST['request']); ?>"/>

            <button type="submit" class="btn btn-primary">Změnit heslo</button>
            <a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php" class="btn btn-light">Zrušit</a>
        </form>
        <?php
    }

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';