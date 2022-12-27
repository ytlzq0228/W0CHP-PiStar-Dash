<?php

if($_POST['action'] == 'enable') {
    exec('sudo mount -o remount,rw /');
    exec('sudo touch /etc/.SHOWDMRTA');
    exec('sudo sed -i "/EmbeddedLCOnly=/c\\EmbeddedLCOnly=0" /etc/mmdvmhost');
    exec('sudo sed -i "/DumpTAData=/c\\DumpTAData=1" /etc/mmdvmhost');
    exec('sudo systemctl restart mmdvmhost.service &');
    exec('sudo mount -o remount,ro /');
}

if($_POST['action'] == 'disable') {
    exec('sudo mount -o remount,rw /');
    exec('sudo rm -rf /etc/.SHOWDMRTA');
    exec('sudo sed -i "/EmbeddedLCOnly=/c\\EmbeddedLCOnly=1" /etc/mmdvmhost');
    exec('sudo sed -i "/DumpTAData=/c\\DumpTAData=0" /etc/mmdvmhost');
    exec('sudo systemctl restart mmdvmhost.service &');
    exec('sudo mount -o remount,ro /');
}

?>
