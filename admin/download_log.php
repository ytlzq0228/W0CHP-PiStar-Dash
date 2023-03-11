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

if ($_SERVER["PHP_SELF"] == "/admin/download_log.php") {
	$log = $_GET['log'];

	switch ($log) {
            case "MMDVMHost":
		$logfile = "/var/log/pi-star/MMDVM-".gmdate('Y-m-d').".log";
		break;
	    case "DStarRepeater":
		$logfile = "/var/log/pi-star/DStarRepeater-".gmdate('Y-m-d').".log";
		break;
	    case "DMRGateway":
		$logfile = "/var/log/pi-star/DMRGateway-".gmdate('Y-m-d').".log";
		break;
	    case "YSFGateway":
		$logfile = "/var/log/pi-star/YSFGateway-".gmdate('Y-m-d').".log";
		break;
	    case "ircDDBGateway":
		$logfile = "/var/log/pi-star/ircDDBGateway-".gmdate('Y-m-d').".log";
		break;
	    case "P25Gateway":
		$logfile = "/var/log/pi-star/P25Gateway-".gmdate('Y-m-d').".log";
		break;
	    case "NXDNGateway":
		$logfile = "/var/log/pi-star/NXDNGateway-".gmdate('Y-m-d').".log";
		break;
	    case "M17Gateway":
		$logfile = "/var/log/pi-star/M17Gateway-".gmdate('Y-m-d').".log";
		break;
	    case "DAPNETGateway":
		$logfile = "/var/log/pi-star/DAPNETGateway-".gmdate('Y-m-d').".log";
		break;
	}
	$hostNameInfo = exec('cat /etc/hostname');

	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Cache-Control: private', false);
	header('Content-Type: text/plain');
	if ($hostNameInfo != "pi-star") {
		header('Content-Disposition: attachment; filename="Pi-Star_'.$hostNameInfo.'_'.basename($logfile).'";');
	} else {
		header('Content-Disposition: attachment; filename="Pi-Star_'.basename($logfile).'";');
	}
	header('Content-Length: '.filesize($logfile));
	header('Accept-Ranges: bytes');

	// User Agent Detection
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'indows') !== false) {
		$userAgent = "Windows";
	} else {
		$userAgent = "NonWindows";
	}

	// Pre-flight checks done, send the output.
	set_time_limit(0);
	$file = @fopen($logfile,"rb");
	while(!feof($file)) {
		if ($userAgent == "Windows") { print(str_replace("\n", "\r\n", @fread($file, 1024*8))); }
		if ($userAgent == "NonWindows") { print(@fread($file, 1024*8)); }
		ob_flush();
		flush();
	}

	// Ok we are done, close the file and clean up.
	@fclose($file);
	exit;
}
else {
    die();
}
?>
