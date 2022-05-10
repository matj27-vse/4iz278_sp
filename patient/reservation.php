<?php
    require_once '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/require_patient.php';

    $pageTitle = 'Rezervace termínu';
    $currentPage = basename(__FILE__);
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/header.php';
?>
<?php
    if (!empty(@$_REQUEST['error'])) {
        echo '<div class="alert alert-danger" role="alert">';
        echo htmlspecialchars($_REQUEST['error']);
        echo '</div>';
        echo '<a href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/reservation.php" class="mr-1 btn btn-primary">Pokračovat</a>';
    } else {
        if (empty($_GET['doctor_id'])) { ?>
            <form method="get">
                <div class="form-group">
                    <?php
                        $doctors = $db->query('SELECT doctor_id,given_name,family_name FROM doctors WHERE active=1;')
                            ->fetchAll(PDO::FETCH_ASSOC);
                        if ($doctors) {
                            ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Jméno lékaře</th>
                                        <th scope="col">Objednání</th>
                                    </tr>
                                </thead>
                                <?php
                                    foreach ($doctors as $doctor) {
                                        echo '<tr>';
                                        echo '<th scope="col">'
                                            . htmlspecialchars($doctor['given_name'])
                                            . ' '
                                            . htmlspecialchars($doctor['family_name'])
                                            . '</th>';
                                        echo '<th scope="col"><a href="reservation.php?doctor_id=' . $doctor['doctor_id'] . '">Objednat k lékaři</a></th>';
                                        echo '</tr>';
                                    } ?>
                            </table>
                        <?php } else {
                            header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/reservation.php?error=Neexistuje doktor, ke kterému je možné se objednat.');
                        }
                    ?>
                </div>
            </form>
        <?php } else { ?>
            <?php
            $queryDoctor = $db->prepare('SELECT given_name,family_name FROM doctors WHERE doctor_id=:doctor_id AND active=1 LIMIT 1;');
            $queryDoctor->execute([
                ':doctor_id' => $_GET['doctor_id']
            ]);
            if ($doctor = $queryDoctor->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <h2>Objednání k lékaři <?php
                        echo htmlspecialchars($doctor['given_name'] . ' ' . $doctor['family_name']);
                    ?></h2>
                <div class="form-group">
                    <label for="datepicker">Zvolte datum návštěvy:</label>
                    <input name="date" id="datepicker" type="text" placeholder="Klepněte pro výběr data">
                </div>

                <div class="form-group" id="timeslots-table"></div>

                <script src="https://cdn.jsdelivr.net/npm/mc-datepicker/dist/mc-calendar.min.js"></script>
                <script src="inc/datepicker_driver.js"></script>
                <?php
            } else {
                header('Location: https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/reservation.php?error=Zvolený doktor neexistuje.');
            }
        }
    } ?>
<?php
    include '/home/httpd/html/users/matj27/4iz278/semestralni_prace/inc/footer.php';