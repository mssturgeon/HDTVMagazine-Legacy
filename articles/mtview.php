<?php
    include('/var/www/cgi-bin/movabletype/php/mt.php');
    $mt = new MT(1, '/var/www/cgi-bin/movabletype/mt.cfg');
    $mt->view();
?>