<?php
if (isset($_COOKIE['PHPSESSID']))
{
    session_id($_COOKIE['PHPSESSID']); 
}
if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION) || !is_array($_SESSION) || (count($_SESSION, COUNT_RECURSIVE) < 10)) {
    session_id('pistardashsess');
    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code
    checkSessionValidity();
}

include_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';         // Version Code


$tempfile = "/tmp/".md5(gmdate('M d Y')).".tmp";
$tempauth = "/tmp/".md5(gmdate('M d Y')).".tmp";

$fm_config_file = '/etc/tinyfilemanager-config.php';
$fm_auth_file = '/etc/tinyfilemanager-auth.php';

// Create default config file
if (!file_exists($fm_config_file)) {
    exec('sudo echo "<?php" >' .$tempfile.'');
    exec('sudo echo "//Default Configuration" >>' .$tempfile.'');
    exec('sudo echo "\$CONFIG = \'{\"lang\":\"en\",\"error_reporting\":false,\"show_hidden\":false,\"hide_Cols\":false,\"calc_folder\":false}\';" >>' .$tempfile.'');
    exec('sudo echo "" >>' .$tempfile.'');
    exec('sudo echo "?>" >>' .$tempfile.'');
    
    exec('sudo mount -o remount,rw /');
    exec('sudo mv '.$tempauth.' '.$fm_config_file.'');
    exec('sudo chown www-data:www-data '.$fm_config_file.'');
    exec('sudo chmod 664 '.$fm_config_file.'');
    exec('sudo sync && sudo sync && sudo sync && sudo mount -o remount,ro /');
}

// Create default auth file
if (!file_exists($fm_auth_file)) {
    exec('sudo echo "<?php" >' .$tempauth.'');
    exec('sudo echo "\$use_auth = false;" >> ' .$tempauth.'');
    #exec('sudo echo "\$auth_users = array(" >> ' .$tempauth.'');
    #exec('sudo echo "\'root\' => \'\$2y\$10\$CyydY39ceRMMAvKRWmidWNZ6]eZ7kXMZpqTjiTb5R.UtFKmruYzwv24yjBw-ZmX4VjpP3HXzu9X,hjHFuV9GXarkHVQxQbfjPVShg5br\', //raspberry" >>' .$tempauth.'');
    #exec('sudo echo "\'pi-star\' => \'\$2y\$10\$CyydY39ceRMMAvKRWmidWNZ6]eZ7kXMZpqTjiTb5R.UtFKmruYzwv24yjBw-ZmX4VjpP3HXzu9X,hjHFuV9GXarkHVQxQbfjPVShg5br\' //raspberry" >>' .$tempauth.'');
    #exec('sudo echo ");" >>' .$tempauth.'');
    exec('sudo echo "?>" >>' .$tempauth.'');
    
    exec('sudo mount -o remount,rw /');
    exec('sudo mv '.$tempauth.' '.$fm_auth_file.'');
    exec('sudo chown www-data:www-data '.$fm_auth_file.'');
    exec('sudo chmod 664 '.$fm_auth_file.'');
    exec('sudo sync && sudo sync && sudo sync && sudo mount -o remount,ro /');
}

require_once('./tinyfilemanager.php');

?>
