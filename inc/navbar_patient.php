<nav class="navbar navbar-expand-lg navbar-light bg-light w-100">
    <ul class="navbar-nav">
        <li class="nav-item <?php echo(($currentPage == 'index.php') ? 'active' : ''); ?>">
            <a class="nav-link" href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php">Seznam termínů<?php
                    echo(($currentPage == 'index.php') ? '<span class="sr-only">(current)</span>' : '');
                ?></a>
        </li>
        <li class="nav-item <?php echo(($currentPage == 'reservation.php') ? 'active' : ''); ?>">
            <a class="nav-link"
               href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/patient/reservation.php">Rezervace termínu<?php
                    echo(($currentPage == 'reservation.php') ? '<span class="sr-only">(current)</span>' : '');
                ?></a>
        </li>
    </ul>
</nav>