<?php

if(isset($_REQUEST['d'])) {
    $t_source = userialize($_REQUEST['d']);
    var_dump($t_source);
    die();
} else {
    die('You\'re not allowed to get into. Cheating, uh?');
}

?>