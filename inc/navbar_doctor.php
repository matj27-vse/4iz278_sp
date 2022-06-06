<nav class="navbar navbar-expand-lg navbar-light bg-light w-100">
    <ul class="navbar-nav">
        <li class="nav-item <?php echo((@$currentPage == 'index.php') ? 'active' : ''); ?>">
            <a class="nav-link" href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/index.php">Seznam termínů<?php
                    echo((@$currentPage == 'index.php') ? '<span class="sr-only">(current)</span>' : '');
                ?></a>
        </li>
        <li class="nav-item <?php echo((@$currentPage == 'settings.php') ? 'active' : ''); ?>">
            <a class="nav-link"
               href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/doctor/settings.php">Nastavení<?php
                    echo((@$currentPage == 'settings.php') ? '<span class="sr-only">(current)</span>' : '');
                ?></a>
        </li>
        <li class="nav-item <?php echo((@$currentPage == 'visited.php') ? 'active' : ''); ?>">
            <a class="nav-link"
               href="https://eso.vse.cz/~matj27/4iz278/semestralni_prace/doctor/visited.php?visited=true">Seznam již navštívených<?php
                    echo((@$currentPage == 'visited.php') ? '<span class="sr-only">(current)</span>' : '');
                ?></a>
        </li>
    </ul>
</nav>