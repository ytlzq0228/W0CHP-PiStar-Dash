<?php

if (!isset($_SESSION) || !is_array($_SESSION)) {
    session_id('pistardashsess');
    session_start();
    
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code
    checkSessionValidity();
}

$configfile = '/etc/dapnetgateway';
$service = 'dapnetgateway.service';

$tempfile = "/tmp/".md5(gmdate('M d Y')).".tmp";

//this is the function going to update your ini file
function update_ini_file($data, $filepath) {
    $content = "";
    
    //parse the ini file to get the sections
    //parse the ini file using default parse_ini_file() PHP function
    $parsed_ini = parse_ini_file($filepath, true);
    
    foreach($data as $section=>$values) {
	// UnBreak special cases
	$section = str_replace("_", " ", $section);
	$content .= "[".$section."]\n";
	//append the values
	foreach($values as $key=>$value) {
	    if (($section == "General" || $section == "Log") && $key == "DAPNET" && $value) {
		$value = str_replace('"', "", $value);
		$content .= $key."=\"".$value."\"\n";
	    }
	    else {
		$content .= $key."=".$value."\n";
	    }
	}
	$content .= "\n";
    }
    
    //write it into file
    if (!$handle = fopen($filepath, 'w')) {
	return false;
    }
    
    $success = fwrite($handle, $content);
    fclose($handle);

    // Updates complete - copy the working file back to the proper location
    exec('sudo mount -o remount,rw /');
    exec("sudo cp $tempfile $configfile");
    exec("sudo chmod 644 $configfile");
    exec("sudo chown root:root $configfile");
    exec("sudo rm $tempfile");

    // Reload the affected daemon
    exec("sudo systemctl restart $service");
    return $success;    
}

require_once('edit_template.php');

?>
