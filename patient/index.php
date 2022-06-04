<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_patient.php';

    $currentPage = basename(__FILE__);
    $pageTitle = 'Seznam termínů';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

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
    $appointmentsQuery = $db->prepare('SELECT * FROM (' . $selectView . ') AS patient_appointment WHERE patient_id=:patient_id' . $orderBy . ';');
    $appointmentsQuery->execute([
        ':patient_id' => $_SESSION['patient_id']
    ]);
    $appointments = $appointmentsQuery->fetchAll(PDO::FETCH_ASSOC);
?>
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
