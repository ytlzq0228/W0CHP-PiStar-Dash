<?php

if($_POST['action'] == 'enable') {
    exec('sudo mount -o remount,rw /');
    exec('sudo touch /etc/.TGNAMES');
}

if($_POST['action'] == 'disable') {
    exec('sudo mount -o remount,rw /');
    exec('sudo rm -rf /etc/.TGNAMES');
}

?>
