<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_patient.php';

    $currentPage = basename(__FILE__);
    $pageTitle = 'Seznam termínů';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

    $selectView = 'SELECT 
                        appointments.appointment_id, 
                        appointments.timestamp, 
                        appointments.confirmed, 
                        doctors.doctor_id, 
                        doctors.given_name AS doctor_given_name,
                        doctors.family_name AS doctor_family_name,
                        doctors.email AS doctor_email,
                        patients.patient_id,
                        patients.given_name AS patient_given_name,
                        patients.family_name AS patient_family_name,
                        patients.email AS patient_email
                    FROM appointments
                    JOIN doctors ON appointments.doctor_id = doctors.doctor_id
                    JOIN patients ON appointments.patient_id = patients.patient_id
                    WHERE appointments.timestamp >= UNIX_TIMESTAMP()';

    $appointmentsQuery = $db->prepare('SELECT * FROM (' . $selectView . ') AS patient_appointment WHERE patient_id=:patient_id;');
    $appointmentsQuery->execute([
        ':patient_id' => $_SESSION['patient_id']
    ]);
    $appointments = $appointmentsQuery->fetchAll(PDO::FETCH_ASSOC);


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
                        E-mail na lékaře: <?php echo htmlspecialchars($appointment['doctor_email']); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo ($appointment['confirmed'] == 0) ? 'Rezervace není potvrzena lékařem' : 'Rezervace je potvrzena lékařem' ?>
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
?>


<?php
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';