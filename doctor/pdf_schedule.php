<?php

    use Mpdf\Mpdf;

    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/vendor/autoload.php';
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_doctor.php';

    $errors = [];

    $html = '';

    #region nacteni z db
    $orderBy = ' ORDER BY appointment_id ASC ';
    if (!empty($_GET['order-by'])) {
        switch ($_GET['order-by']) {
            case 'timestamp':
                $orderBy = ' ORDER BY timestamp ASC ';
                break;
            case 'patient':
                $orderBy = ' ORDER BY patient_family_name ASC ';
                break;
            case 'appointment-id':
                $orderBy = ' ORDER BY appointment_id ASC ';
                break;
        }
    }

    $confirmed = '';
    if (!empty($_GET['confirmed'])) {
        switch ($_GET['confirmed']) {
            case 'false':
                $confirmed = ' AND confirmed=0 ';
                break;
            case 'true':
                $confirmed = ' AND confirmed=1 ';
                break;
        }
    }
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/view_appointments_patients_doctors.php';
    $appointmentsQuery = $db->prepare(
        'SELECT * FROM (' . $selectView . ') AS patient_appointment WHERE doctor_id=:doctor_id' . $confirmed . $orderBy . ';');
    $appointmentsQuery->execute([
        ':doctor_id' => $_SESSION['doctor_id']
    ]);
    $appointments = $appointmentsQuery->fetchAll(PDO::FETCH_ASSOC);
    #endregion nacteni z db

    $html .= '<h1>Objednaní pacienti</h1>
                    <h2>Lékař: ' . htmlspecialchars($_SESSION['given_name']) . ' ' . htmlspecialchars($_SESSION['family_name']) . '</h2>
                    <p class="breadcrumb">' . date("j. n. Y") . '</p>';

    foreach ($appointments as $appointment) {
        $html .= '<h4>Pacient: ' . htmlspecialchars($appointment['patient_given_name'] . ' ' . $appointment['patient_family_name']) . '</h4>';
        $html .= '<p>';
        $html .= 'Datum a čas: ' . date("d. m. Y, H:i:s", $appointment['timestamp']) . '<br/>';
        $html .= 'Délka návštěvy: ' . $appointment['length'] . 'minut<br/>';
        $html .= '</p>';
    }


    if (empty($errors)) {
        //načteme styly ze samostatného souboru
        $stylesheet = file_get_contents('/home/httpd/html/users/matj27/4iz278/semestralni_prace/style/mpdfAppointmentsListA4.css');

        //a jdeme vytvořit PDF
        $mpdf = new mPDF();
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($stylesheet, 1);//2. parametr určuje, že jde jen o styly a ne html obsah
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    } else {
        $currentPage = basename(__FILE__);
        $pageTitle = 'Generování PDF';
        include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

        foreach ($errors as $error) {
            echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
            echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/doctor/index.php?' .
                parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) . '"
                class="mr-1 btn btn-primary">Zpět na seznam objednávek</a>';
        }

        include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';
    }
