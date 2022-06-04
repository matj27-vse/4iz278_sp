<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_doctor.php';

    $currentPage = basename(__FILE__);
    $pageTitle = 'Sekce pro lékaře';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/view_appointments_patients_doctors.php';

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
    $appointmentsQuery = $db->prepare(
        'SELECT * FROM (' . $selectView . ') AS patient_appointment WHERE doctor_id=:doctor_id' . $confirmed . $orderBy . ';');
    $appointmentsQuery->execute([
        ':doctor_id' => $_SESSION['doctor_id']
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
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <form method="get">
                <div class="form-group">
                    <label for="order-by">Seřadit podle: </label>
                    <select id="order-by" name="order-by">
                        <option value="appointment-id" <?php echo(empty($_GET['order-by']) ? 'selected' : ''); ?>>
                            Čísla návštěvy
                        </option>
                        <option value="timestamp" <?php echo(@$_GET['order-by'] == 'timestamp' ? 'selected' : ''); ?>>
                            Data a času
                        </option>
                        <option value="patient" <?php echo(@$_GET['order-by'] == 'patient' ? 'selected' : ''); ?>>
                            Příjmení pacienta
                        </option>
                    </select>
                    <button type="submit" class="btn btn-light">Seřadit</button>
                </div>
            </form>
        </div>
    </div>
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
                        Pacient: <?php echo htmlspecialchars($appointment['patient_given_name'] . ' ' . $appointment['patient_family_name']); ?>
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
                        E-mail na
                        pacienta: <?php echo '<a href="mailto:' . htmlspecialchars($appointment['patient_email']) . '">' .
                            htmlspecialchars($appointment['patient_email']) . '</a>'; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo ($appointment['confirmed'] == 0)
                            ? '<div class="alert alert-warning" role="alert">Rezervace není potvrzena lékařem</div>'
                            : '<div class="alert alert-success" role="alert">Rezervace je potvrzena lékařem</div>' ?>
                    </td>
                </tr>
                <?php
                    if ($appointment['confirmed'] == 0) {
                        ?>
                        <td>
                            <a href="confirm_reservation.php?appointment_id=<?php echo $appointment['appointment_id']; ?>"
                               class="mr-1 btn btn-primary">Potvrdit
                                rezervaci <?php echo htmlspecialchars($appointment['appointment_id']); ?></a>
                        </td>
                        <?php
                    }
                ?>
                <tr>
                    <td>
                        <a href="cancel_reservation.php?appointment_id=<?php echo $appointment['appointment_id']; ?>"
                           class="mr-1 btn btn-light">Zrušit
                            rezervaci <?php echo htmlspecialchars($appointment['appointment_id']); ?></a>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';
