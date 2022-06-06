<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_doctor.php';

    $loadMcDatepicker = true;
    $currentPage = basename(__FILE__);
    $pageTitle = 'Sekce pro lékaře';
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';

    $errors = [];

    if (isset($_GET['visited'])) {
        if ($_GET['visited'] == 'true') {
            $visitedQuery = $db->prepare('SELECT * FROM visited JOIN patients on (visited.patient_id=patients.patient_id) WHERE doctor_id=:doctor_id;');
            $visitedQuery->execute([
                ':doctor_id' => $_SESSION['doctor_id']
            ]);

            $patients = $visitedQuery->fetchAll(PDO::FETCH_ASSOC);
        } else if ($_GET['visited'] == 'false') {
            $notVisitedQuery = $db->prepare('SELECT * FROM patients JOIN 
                (SELECT DISTINCT(appointments.patient_id) FROM 
                    appointments LEFT JOIN visited ON appointments.doctor_id=visited.doctor_id 
                    WHERE appointments.patient_id!=visited.patient_id AND appointments.doctor_id=:doctor_id)
                AS not_visited ON patients.patient_id=not_visited.patient_id;');
            $notVisitedQuery->execute([
                ':doctor_id' => $_SESSION['doctor_id']
            ]);

            $patients = $notVisitedQuery->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $errors['bad-param'] = 'Byl zadán neočekávaný vstup.';
        }
    }
?>

    <div class="row">
        <div class="col-sm-12 col-md-12">
            <form method="get">
                <div class="form-group">
                    <?php echo(@$_GET['visited'] == 'true' ? '<a href="visited.php?visited=false" class="btn btn-primary">
                        Zobrazit pacienty, kteří mě prozatím nenavštívíli</a>' : '') ?>
                    <?php echo(@$_GET['visited'] == 'false' ? '<a href="visited.php?visited=true" class="btn btn-primary">
                        Zobrazit pacienty, kteří mě již navštívíli</a>' : '') ?>
                </div>
            </form>
        </div>
    </div>

<?php
    if (isset($_GET['visited']) == 'true') {
        foreach ($patients as $patient) { ?>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">
                            Pacient: <?php echo htmlspecialchars($patient['given_name'] . ' ' . $patient['family_name']); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            E-mail na
                            pacienta: <?php echo '<a href="mailto:' . htmlspecialchars($patient['email']) . '">' .
                                htmlspecialchars($patient['email']) . '</a>'; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
        }
    }