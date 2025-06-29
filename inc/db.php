<?php

   //$db = new PDO('mysql:host=127.0.0.1;dbname=novp45;charset=utf8', 'novp45', 'aem9ku7aezeime3Pho');

   $db = new PDO('mysql:host=127.0.0.1;dbname=novp45;charset=utf8', 'root', '');

    //následující nastavení zařídí, abychom byla při chybě v SQL vyhozena standardní výjimka (exception)
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>