<?php
    //připojení do DB na serveru eso.vse.cz - XNAME a HESLO samozřejmě zaktualizujte dle svých vlastních údajů
    //doporučuji do connection stringu rovnou dopsat také údaje o kódování, ve kterém chceme s databází komunikovat
    $db = new PDO('mysql:host=127.0.0.1;dbname=matj27;charset=utf8', 'matj27', 'ahj9xeir9eim9omei9');

    //následující nastavení zařídí, abychom byla při chybě v SQL vyhozena standardní výjimka (exception)
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
