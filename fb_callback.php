<?php
    //načteme připojení k databázi a inicializujeme session
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/user.php';
    //načteme inicializaci knihovny pro Facebook
    require_once 'inc/facebook.php';

    #region zpracování callbacku z Facebooku
    //inicializujeme helper pro vytvoření odkazu
    $fbHelper = $fb->getRedirectLoginHelper();

    //získáme access token z aktuálního přihlášení
    try {
        $accessToken = $fbHelper->getAccessToken();
    } catch (Exception $e) {
        echo 'Přihlášení pomocí Facebooku selhalo. Chyba: ' . $e->getMessage();
        exit();
    }

    if (!$accessToken) {
        //Nebyl vrácen access token - např. pacient odmítl oprávnění pro aplikaci atp.
        //Chyby bychom ale rozhodně mohli vypisovat v hezčí formě :)
        exit('Přihlášení pomocí Facebooku se nezdařilo. Zkuste to znovu.');
    }

    //OAuth 2.0 client pro správu access tokenů
    $oAuth2Client = $fb->getOAuth2Client();

    //XXX pokud bychom chtěli více pracovat s daty z Facebooku, byla by zde validace tokenu a jeho změna na dlouhodobý

    //získáme údaje k tokenu, který jsme získali z přihlášení
    $accessTokenMetadata = $oAuth2Client->debugToken($accessToken);

    //získáme ID pacienta z Facebooku
    $fbUserId = $accessTokenMetadata->getUserId();

    //získáme jméno a e-mail pacienta
    $response = $fb->get('/me?fields=name,email', $accessToken);
    $graphUser = $response->getGraphUser();

    $fbUserEmail = $graphUser->getEmail();
    $fbUserName = $graphUser->getName();

    #endregion zpracování callbacku z Facebooku

    #region registrace pacienta v DB a načtení odpovídajících údajů
    //nejprve se pokusíme daného pacienta načíst podle FB User ID
    $query = $db->prepare('SELECT * FROM patients WHERE facebook_id=:facebookId LIMIT 1;');
    $query->execute([
        ':facebookId' => $fbUserId
    ]);

    if ($query->rowCount() > 0) {
        //pacienta jsme našli v DB podle jeho Facebook User ID
        $patient = $query->fetch(PDO::FETCH_ASSOC);
    } else {
        //pacient nebyl nalezen v DB - pokusíme se jej najít pomocí e-mailu
        $query = $db->prepare('SELECT * FROM patients WHERE email=:email LIMIT 1;');
        $query->execute([
            ':email' => $fbUserEmail
        ]);

        if ($query->rowCount() > 0) {
            //pacienta jsme našli podle e-mailu, připíšeme k němu do DB jeho Facebook User ID
            $patient = $query->fetch(PDO::FETCH_ASSOC);

            $updateQuery = $db->prepare('UPDATE patients SET facebook_id=:facebookId WHERE patient_id=:id LIMTI 1;');
            $updateQuery->execute([
                ':facebookId' => $fbUserId,
                ':id' => $patient['patient_id']
            ]);

        } else {
            $parts = explode(" ", $fbUserName);
            $familyName = array_pop($parts);
            $givenName = implode(" ", $parts);

            //pacienta jsme vůbec nenašli, zapíšeme ho do DB jako nového
            $insertQuery = $db->prepare('INSERT INTO patients (given_name, family_name, email, facebook_id) 
                                            VALUES (:given_name, :family_name, :email, :facebookId);');
            $insertQuery->execute([
                ':given_name' => $givenName,
                ':family_name' => $familyName,
                ':email' => $fbUserEmail,
                ':facebookId' => $fbUserId
            ]);

            //pacienta následně zpětně načteme z DB pro získání jeho patient_id
            $query = $db->prepare('SELECT * FROM patients WHERE facebook_id=:facebookId LIMIT 1;');
            $query->execute([
                ':facebookId' => $fbUserId
            ]);
            $patient = $query->fetch(PDO::FETCH_ASSOC);
        }
    }

    #endregion registrace pacienta v DB a načtení odpovídajících údajů

    #region přihlášení pacienta
    if (!empty($patient)) {
        //přihlásíme pacienta (uložíme si jeho údaje do session)
        $_SESSION['patient_id'] = $patient['patient_id'];
        $_SESSION['given_name'] = $patient['given_name'];
        $_SESSION['family_name'] = $patient['family_name'];
        $_SESSION['email'] = $patient['email'];
    }

    //přesměrujeme pacienta na homepage
    header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php');
    #endregion přihlášení pacienta