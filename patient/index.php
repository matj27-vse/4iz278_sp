<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_patient.php';

    $loadMcDatepicker = true;
    $currentPage = basename(__FILE__);
    $pageTitle = 'Seznam termínů';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

    if (isset($_REQUEST['succ'])) {
        echo '<div class="alert alert-success" role="alert">' . $_REQUEST['succ'] . '</div>';
    }

    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/view_appointments_patients_doctors.php';

    $orderBy = '';
    if (!empty($_GET['order-by'])) {
        switch ($_GET['order-by']) {
            case 'timestamp':
                $orderBy = ' ORDER BY timestamp ASC';
                break;
            case 'doctor':
                $orderBy = ' ORDER BY doctor_family_name ASC';
                break;
            default:
                $orderBy = ' ORDER BY appointment_id ASC';
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

    $timestamp = '';
    if (!empty($_GET['timestamp'])) {
        $timestamp = ' AND timestamp>' . $_GET['timestamp'] . ' AND timestamp<' . ($_GET['timestamp'] + (24 * 60 * 60)) . ' ';
    }

    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/view_appointments_patients_doctors.php';
    $appointmentsQuery = $db->prepare(
        'SELECT * FROM (' . $selectView . ') AS patient_appointment WHERE patient_id=:patient_id' . $confirmed .
        $timestamp . $orderBy . ';');
    $appointmentsQuery->execute([
        ':patient_id' => $_SESSION['patient_id']
    ]);
    $appointments = $appointmentsQuery->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <?php
                parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $newGetQuery);

                if (@$_REQUEST['confirmed'] == 'true') {
                    $newGetQuery['confirmed'] = 'false';
                    echo '<a class="mr-1 btn btn-primary"
                        href="index.php?' . http_build_query($newGetQuery) . '">Zobrazit pouze nepotvrzené návštěvy</a>';

                    unset($newGetQuery['confirmed']);
                    echo '<a class="mr-1 btn btn-primary"
                        href="index.php?' . http_build_query($newGetQuery) . '">Zobrazit všechny návštěvy</a>';
                }

                if (@$_REQUEST['confirmed'] == 'false') {
                    $newGetQuery['confirmed'] = 'true';
                    echo '<a class="mr-1 btn btn-primary"
                        href="index.php?' . http_build_query($newGetQuery) . '">Zobrazit pouze potvrzené návštěvy</a>';

                    unset($newGetQuery['confirmed']);
                    echo '<a class="mr-1 btn btn-primary"
                        href="index.php?' . http_build_query($newGetQuery) . '">Zobrazit všechny návštěvy</a>';
                }

                if (!isset($_REQUEST['confirmed'])) {
                    $newGetQuery['confirmed'] = 'true';
                    echo '<a class="mr-1 btn btn-primary"
                        href="index.php?' . http_build_query($newGetQuery) . '">Zobrazit pouze potvrzené návštěvy</a>';

                    $newGetQuery['confirmed'] = 'false';
                    echo '<a class="mr-1 btn btn-primary"
                        href="index.php?' . http_build_query($newGetQuery) . '">Zobrazit pouze nepotvrzené návštěvy</a>';
                }
            ?>
        </div>
    </div>
    <div class="col-sm-12 col-md-12">
        <form method="get">
            <div class="form-group">
                <label for="order-by">Seřadit podle: </label>
                <select id="order-by" name="order-by">
                    <option <?php echo(empty($_GET['order-by']) ? 'selected' : ''); ?>>Čísla návštěvy</option>
                    <option value="timestamp" <?php echo(@$_GET['order-by'] == 'timestamp' ? 'selected' : ''); ?>>Data a
                        času
                    </option>
                    <option value="doctor" <?php echo(@$_GET['order-by'] == 'doctor' ? 'selected' : ''); ?>>Příjmení
                        lékaře
                    </option>
                </select>
                <button type="submit" class="btn btn-light">Seřadit</button>
            </div>
        </form>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="form-group">
                <?php
                    if (isset($_REQUEST['timestamp'])) {
                        $date = date("Y-m-d", $_REQUEST['timestamp']);
                    }
                ?>
                <label for="datepicker">Zobrazit návštěvy jen v daném dni:</label>
                <input name="date" id="datepicker" type="text" placeholder="Klepněte pro výběr data"
                    <?php echo(isset($date) ? 'value=' . $date : '') ?>>

                <script src="https://cdn.jsdelivr.net/npm/mc-datepicker/dist/mc-calendar.min.js"></script>
                <script src="inc/datepicker_driver_list.js"></script>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <?php

                if (@$_REQUEST['confirmed'] == 'true') {
                    echo '<h2>Seznam potvrzených návštěv</h2>';
                }
                if (@$_REQUEST['confirmed'] == 'false') {
                    echo '<h2>Seznam nepotvrzených návštěv</h2>';
                }
                if (!isset($_REQUEST['confirmed'])) {
                    echo '<h2>Seznam všech návštěv</h2>';
                }
            ?>
        </div>
    </div>
<?php
    foreach ($appointments as $appointment) { ?>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">
                        Číslo rezervace: <?php echo htmlspecialchars($appointment['appointment_id']); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Lékař: <?php echo htmlspecialchars($appointment['doctor_given_name'] . ' ' . $appointment['doctor_family_name']); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Datum a čas: <?php
                            echo date("d. m. Y, H:i:s", $appointment['timestamp']);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Délka návštěvy: <?php
                            echo $appointment['length'];
                        ?> minut
                    </td>
                </tr>
                <tr>
                    <td>
                        E-mail na
                        lékaře: <?php echo '<a href="mailto:' . htmlspecialchars($appointment['doctor_email']) . '">' .
                            htmlspecialchars($appointment['doctor_email']) . '</a>'; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo ($appointment['confirmed'] == 0)
                            ? '<div class="alert alert-warning" role="alert">Rezervace není potvrzena lékařem</div>'
                            : '<div class="alert alert-success" role="alert">Rezervace je potvrzena lékařem</div>' ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href="cancel_reservation.php?appointment_id=<?php echo $appointment['appointment_id']; ?>"
                           class="mr-1 btn btn-primary">Zrušit
                            rezervaci <?php echo htmlspecialchars($appointment['appointment_id']); ?></a>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';
