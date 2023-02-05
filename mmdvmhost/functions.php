<?php
if ( isset( $_COOKIE['PHPSESSID'] ) && ! session_id() )
{
    session_id($_COOKIE['PHPSESSID']); 
}
if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION) || !is_array($_SESSION) || (count($_SESSION, COUNT_RECURSIVE) < 10)) {
    session_id('pistardashsess');
    session_start();
}

require_once($_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php');

function loadSessionConfigFile($key, $configFile, $minEntries = 2) {
    if ((!isset($_SESSION[$key]) || (count($_SESSION[$key], COUNT_RECURSIVE) < $minEntries)) && file_exists($configFile)) {
	$_SESSION[$key] = parse_ini_file($configFile, true);
    }
}

function checkSessionValidity() {
    if (!isset($_SESSION['MYCALL'])) {
	global $callsign;
	
	if (empty($callsign)) {
           include $_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php';
           $_SESSION['MYCALL'] = strtoupper($callsign);
       }
       else {
           $_SESSION['MYCALL'] = strtoupper($callsign);
	   }
    }

    if ( ! isset( $_SESSION['BMAPIKey'] ) || ( is_countable( $_SESSION['BMAPIKey'] ) && count( $_SESSION['BMAPIKey'], COUNT_RECURSIVE) < 1 ) && file_exists( '/etc/bmapi.key' ) ) {
	$configBMapi = parse_ini_file('/etc/bmapi.key', true);
	if (isset($configBMapi['key']['apikey']) && !empty($configBMapi['key']['apikey'])) {
	    $_SESSION['BMAPIKey'] = $configBMapi['key']['apikey'];
	    // Check the BM API Key
	    if ( strlen($_SESSION['BMAPIKey']) <= 20 ) {
		unset($_SESSION['BMAPIKey']);
	    }
	}
    }

    loadSessionConfigFile('DAPNETAPIKeyConfigs', '/etc/dapnetapi.key');
    loadSessionConfigFile('PiStarRelease', '/etc/pistar-release');
    if (!isset($_SESSION['MMDVMHostConfigs']) || (count($_SESSION['MMDVMHostConfigs'], COUNT_RECURSIVE) < 2)) {
	$_SESSION['MMDVMHostConfigs'] = getMMDVMConfigContent();
    }
    if (!isset($_SESSION['ircDDBConfigs']) || (count($_SESSION['ircDDBConfigs'], COUNT_RECURSIVE) < 2)) {
	global $gatewayConfigPath;

	if (empty($gatewayConfigPath)) {
	    include $_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php';
	    $_SESSION['ircDDBConfigs'] = getNoSectionsConfigContent($gatewayConfigPath);
	}
	else {
	    $_SESSION['ircDDBConfigs'] = getNoSectionsConfigContent($gatewayConfigPath);
	}

    }

    loadSessionConfigFile('DStarRepeaterConfigs', '/etc/dstarrepeater');
    loadSessionConfigFile('DMRGatewayConfigs', '/etc/dmrgateway');
    loadSessionConfigFile('YSFGatewayConfigs', '/etc/ysfgateway');
    loadSessionConfigFile('DGIdGatewayConfigs', '/etc/dgidgateway');
    loadSessionConfigFile('DAPNETGatewayConfigs', '/etc/dapnetgateway');
    loadSessionConfigFile('YSF2DMRConfigs', '/etc/ysf2dmr');
    loadSessionConfigFile('YSF2NXDNConfigs', '/etc/ysf2nxdn');
    loadSessionConfigFile('YSF2P25Configs', '/etc/ysf2p25');
    loadSessionConfigFile('DMR2YSFConfigs', '/etc/dmr2ysf');
    loadSessionConfigFile('DMR2NXDNConfigs', '/etc/dmr2nxdn');
    loadSessionConfigFile('APRSGatewayConfigs', '/etc/aprsgateway');
    loadSessionConfigFile('NXDNGatewayConfigs', '/etc/nxdngateway');
    loadSessionConfigFile('M17GatewayConfigs', '/etc/m17gateway');
    loadSessionConfigFile('P25GatewayConfigs', '/etc/p25gateway');
    loadSessionConfigFile('CSSConfigs', '/etc/pistar-css.ini');
    if ( ! isset( $_SESSION['DvModemFWVersion'] ) || ( is_countable( $_SESSION['DvModemFWVersion'] ) && count( $_SESSION['DvModemFWVersion'], COUNT_RECURSIVE ) < 1 ) ) {
	$_SESSION['DvModemFWVersion'] = getDVModemFirmware();
    }
    if ( ! isset( $_SESSION['DvModemTCXOFreq'] ) || ( is_countable( $_SESSION['DvModemTCXOFreq'] ) && count( $_SESSION['DvModemTCXOFreq'], COUNT_RECURSIVE) < 1 ) ) {
	$_SESSION['DvModemTCXOFreq'] = getDVModemTCXOFreq();
    }
}

function get_string_between($string, $start, $end) {
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) {
	return "";
    }
    $ini += strlen($start);   
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
}

function getMMDVMConfigContent() {
    $confs = array();
    if ($handle = @fopen('/etc/mmdvmhost', 'r')) {
	while ($configs = fgets($handle)) {
	    array_push($confs, trim($configs, " \t\n\r\0\x0B"));
	}
	fclose($handle);
    }
    return $confs;
}

// Load any non-sectioned config files
function getNoSectionsConfigContent($configFile) {
    $confs = array();
    if ($handle = @fopen($configFile, 'r')) {
	while ($line = fgets($handle)) {
	    if (strpos($line, '=') !== FALSE) {
		list($key, $value) = explode('=', $line, 2);
		$value = trim(str_replace('"', '', $value));
		
		$confs[$key] = $value;
	    }
	}
	fclose($handle);
    }
    return $confs;
}

// retrieves the corresponding config-entry within a [section]
function getConfigItem($section, $key, $configs) {
    if (empty($section)) {
        return null;
    }
    $sectionpos = array_search("[" . $section . "]", $configs);
    if ($sectionpos !== FALSE) {
        $sectionpos++;
        $len = count($configs);
        while(($sectionpos < $len) && (startsWith($configs[$sectionpos], $key."=") === FALSE)) {
            if (startsWith($configs[$sectionpos],"[")) {
                return null;
            }
            $sectionpos++;
        }
        if ($sectionpos < $len) {
            return substr($configs[$sectionpos], strlen($key) + 1);
        }
    }
    return null;
}

// returns enabled/disabled-State of MMDVM modes
function getEnabled ($mode, $configs) {
    return getConfigItem($mode, "Enable", $configs);
}

// return enabled/disabled state of other services (APRSgw, X-mode services, etc.)
function getServiceEnabled ($configs) {
    if ( strpos(file_get_contents($configs),"Enabled=1") !== false ) {
        return 1;
    } else {
        return 0;
    }
}

// 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
// 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
// I: 2020-11-12 17:54:01.968 MMDVM protocol version: 1, description: MMDVM 20200901 (D-Star/DMR/System Fusion/P25/NXDN/POCSAG/FM) 12.0000 MHz GitID #2509ab5
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: DVMEGA HR3.14
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM_HS-ADF7021 20170414 (D-Star/DMR/YSF/P25) (Build: 20:16:25 May 20 2017)
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM 20170206 TCXO (D-Star/DMR/System Fusion/P25/RSSI/CW Id)
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: ZUMspot ADF7021 v1.0.0 20170728 (DStar/DMR/YSF/P25) GitID #c16dd5a
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM_MDO ADF7021 v1.0.1 20170826 (DStar/DMR/YSF/P25) GitID #BD7KLE
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: ZUMspot-v1.0.3 20171226 ADF7021 FW by CA6JAU GitID #bfb82b4
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM_HS_Hat-v1.0.3 20171226 ADF7021 FW by CA6JAU GitID #bfb82b4
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM_HS-v1.0.3 20171226 ADF7021 FW by CA6JAU GitID #bfb82b4
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM_HS_Dual_Hat-v1.3.6 20180521 dual ADF7021 FW by CA6JAU GitID #bd6217a
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: D2RG_MMDVM_HS-v1.4.17 20190529 14.7456MHz ADF7021 FW by CA6JAU GitID #cc451c4
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: Nano_hotSPOT-v1.3.3 20180224 ADF7021 FW by CA6JAU GitID #62323e7
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: Nano-Spot-v1.3.3 20180224 ADF7021 FW by CA6JAU GitID #62323e7
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: Nano_DV-v1.4.3 20180716 12.2880MHz ADF7021 FW by CA6JAU GitID #6729d23
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: SkyBridge-v1.5.2 20201108 14.7456MHz ADF7021 FW by CA6JAU GitID #89daa20
function getDVModemFirmware() {
	$logMMDVMNow = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d").".log";
	$logMMDVMPrevious = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log";
	$logSearchString = "MMDVM protocol version";
	$logLine = '';
	$modemFirmware = '';
	$logLine = exec("grep \"".$logSearchString."\" ".$logMMDVMNow." | tail -1");
	if (!$logLine) { $logLine = exec("grep \"".$logSearchString."\" ".$logMMDVMPrevious." | tail -1"); }
	if ($logLine) {
		if (strpos($logLine, 'DVMEGA')) {
			$modemFirmware = substr($logLine, 67, 15);
		}
		if (strpos($logLine, 'description: MMDVM_HS')) {
			$modemFirmware = "MMDVM_HS:".ltrim(substr($logLine, 84, 8), 'v');
		}
		if (strpos($logLine, 'description: MMDVM ')) {
			$modemFirmware = "MMDVM:".substr($logLine, 73, 8);
		}
		if (strpos($logLine, 'description: ZUMspot ')) {
			$modemFirmware = "ZUMspot:".strtok(substr($logLine, 83, 12), ' ');
		}
		if (strpos($logLine, 'description: MMDVM_MDO ')) {
			$modemFirmware = "MMDVM_MDO:".ltrim(strtok(substr($logLine, 85, 12), ' '), 'v');
		}
		if (strpos($logLine, 'description: ZUMspot-')) {
			$modemFirmware = "ZUMspot:".strtok(substr($logLine, 75, 12), ' ');
		}
		if (strpos($logLine, 'description: MMDVM_HS_Hat-')) {
			$modemFirmware = "MMDVM_HS-Hat:".strtok(substr($logLine, 80, 12), ' ');
		}
		if (strpos($logLine, 'description: MMDVM_HS_Dual_Hat-')) {
			$modemFirmware = "MMDVM_HS-Dual_Hat:".strtok(substr($logLine, 85, 12), ' ');
		}
		if (strpos($logLine, 'description: D2RG_MMDVM_HS-')) {
			$modemFirmware = "HS_Hat:".strtok(substr($logLine, 81, 12), ' ');
		}
		if (strpos($logLine, 'description: MMDVM_HS-')) {
			$modemFirmware = "MMDVM_HS:".ltrim(strtok(substr($logLine, 76, 12), ' '), 'v');
		}
		if (strpos($logLine, 'description: Nano_hotSPOT-')) {
			$modemFirmware = "MMDVM_HS:".ltrim(strtok(substr($logLine, 80, 12), ' '), 'v');
		}
		if (strpos($logLine, 'description: Nano-Spot-')) {
			$modemFirmware = "NanoSpot:".strtok(substr($logLine, 77, 12), ' ');
		}
		if (strpos($logLine, 'description: Nano_DV-')) {
			$modemFirmware = "NanoDV:".strtok(substr($logLine, 75, 12), ' ');
		}
		if (strpos($logLine, 'description: OpenGD77 Hotspot')) {
			$modemFirmware = "OpenGD77_HS:".strtok(substr($logLine, 83, 12), ' ');
		}
		if (strpos($logLine, 'description: OpenGD77_HS ')) {
			$modemFirmware = "OpenGD77_HS:".strtok(substr($logLine, 79, 12), ' ');
		}
		if (strpos($logLine, 'description: SkyBridge-')) {
			$modemFirmware = "SkyBridge:".strtok(substr($logLine, 77, 12), ' ');
		}
	}
	return $modemFirmware;
}

function getDVModemTCXOFreq() {
    $logMMDVMNow = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d").".log";
    $logMMDVMPrevious = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log";
    $logSearchString = "MMDVM protocol version";
    $logLine = '';
    $modemTCXOFreq = '';
    
    $logLine = exec("grep \"".$logSearchString."\" ".$logMMDVMNow." | tail -1");
    if (!$logLine) { $logLine = exec("grep \"".$logSearchString."\" ".$logMMDVMPrevious." | tail -1"); }
    
    if ($logLine) {
        if (strpos($logLine, 'MHz') !== false) {
            $modemTCXOFreq = $logLine;
            $modemTCXOFreq = preg_replace('/.*(\d{2}\.\d{3,4}\s{0,1}MHz).*/', "$1", $modemTCXOFreq);
            $modemTCXOFreq = str_replace("MHz"," MHz", $modemTCXOFreq);
        }
    }
    return $modemTCXOFreq;
}

//I: 2021-02-21 13:22:24.213 Opening UDP port on 8673
//M: 2021-02-21 13:22:24.213 Starting APRSGateway-20210131_Pi-Star_v4
//M: 2021-02-21 13:22:24.220 Starting the APRS Writer thread
//M: 2021-02-21 13:22:24.664 Received login banner : # aprsc 2.1.8-gf8824e8
//M: 2021-02-21 13:22:24.692 Response from APRS server: # logresp W0CHP verified, server T2CAEAST
//M: 2021-02-21 13:22:24.693 Connected to the APRS server
//E: 2021-02-21 13:29:13.472 Cannot find address for host...
//E: 2021-02-21 13:29:13.472 Connect attempt to the APRS server has failed
//M: 2021-02-21 13:29:13.472 Will attempt to reconnect in 2 minutes
//M: 2021-02-21 13:32:45.385 Response from APRS server: # logresp W0CHP unverified, server T2NANJING
//E: 2021-02-21 13:33:49.907 Cannot connect the TCP client socket, err=111
//E: 2021-02-21 13:33:49.907 Connect attempt to the APRS server has failed
//M: 2021-02-21 13:33:49.907 Will attempt to reconnect in 2 minutes
//E: 2021-02-24 05:13:21.125 Error returned from recv, err=110
//E: 2021-02-24 05:13:21.126 Error when reading from the APRS server
function isAPRSISGatewayConnected() {
    $logLines = array();
    $logLines1 = array();
    $logLines2 = array();

    if (file_exists("/var/log/pi-star/APRSGateway-".gmdate("Y-m-d").".log")) {
        $logPath1 = "/var/log/pi-star/APRSGateway-".gmdate("Y-m-d").".log";
        $logLines1 = preg_split('/\r\n|\r|\n/', `tail -n 4 $logPath1 | cut -d" " -f2- | tac`);
    }
    $logLines1 = array_filter($logLines1);

    if (sizeof($logLines1) == 0) {
        if (file_exists("/var/log/pi-star/APRSGateway-".gmdate("Y-m-d", time() - 86340).".log")) {
            $logPath2 = "/var/log/pi-star/APRSGateway-".gmdate("Y-m-d", time() - 86340).".log";
            $logLines2 = preg_split('/\r\n|\r|\n/', `tail -n 4 $logPath2 | cut -d" " -f2- | tac`);
        }

        $logLines2 = array_filter($logLines2);
    }

    $logLines = $logLines1 + $logLines2;

    $errorMessages = array('Error returned', 'Error when reading from', 'Cannot find address', 'APRS server has failed', 'unverified', 'Cannot connect the TCP');

    foreach($logLines as $aprsMessageLine) {
    foreach($errorMessages as $errorLine) {
        if (strpos($aprsMessageLine, $errorLine) != FALSE)
            return false;
        }
    }
    return true;
}

//M: 2021-02-21 13:22:24.664 Received login banner : # aprsc 2.1.8-gf8824e8
//M: 2021-02-21 13:22:24.692 Response from APRS server: # logresp W0CHP verified, server T2CAEAST
//M: 2021-02-21 13:22:24.693 Connected to the APRS server
function getAPRSISserver() {
    $logAPRSISNow = "/var/log/pi-star/APRSGateway-".gmdate("Y-m-d").".log";
    $logAPRSISPrevious = "/var/log/pi-star/APRSGateway-".gmdate("Y-m-d", time() - 86340).".log";
    $logSearchString = "verified, server";
    $logLine = '';
    $APRSISserver = 'Not Connected';
    $LogError = "Cannot Open Log";
    $server_list = "/usr/local/etc/aprs_servers.json";

    if (file_exists($logAPRSISNow) || file_exists($logAPRSISPrevious)) {
	$logLine = exec("tail -2 $logAPRSISNow | grep \"".$logSearchString."\" ");
	if (!$logLine) {
	    $logLine = exec("tail -2 $logAPRSISPrevious | grep \"".$logSearchString."\" ");
       	}
    } else
	{
	return $LogError;
    }

    if ($logLine) {
	if (strpos($logLine, 'Response from APRS server: # logresp')) {
	    preg_match('/(?<=, server )\S+/i', $logLine, $match); // find server name in log line after "verified, server" string.
	    $APRSISserver = str_replace(",", "", $match[0]); // remove occasional commas after server name
	    $FQDN = exec("cat $server_list | jq '.[]' | grep -B 10 $APRSISserver | grep fqdn | sed -r 's/\"fqdn\"://g;s/\s+//g;s/\"//g;s/,//g'");
	    $APRSISserver = "<a href='http://$FQDN:14501' target='_new'>$APRSISserver</a>";
	}
    }
    return $APRSISserver;
}

//M: 2019-03-06 11:17:25.804 Opening DAPNET connection
//M: 2019-03-06 11:17:25.831 Logging into DAPNET
//M: 2019-03-06 11:17:25.862 Login failed: Invalid credentials
//M: 2019-03-06 11:17:33.873 Closing DAPNET connection
// or
//E: 2019-02-23 16:34:03.406 Cannot connect the TCP client socket, err=111
// or
//E: 2019-03-06 03:35:52.712 Cannot connect the TCP client socket, err=110
//M: 2019-03-06 03:35:52.712 Closing DAPNET connection
//M: 2019-03-06 03:35:52.713 Opening DAPNET connection
//M: 2019-03-06 03:35:52.758 Logging into DAPNET
//E: 2019-03-06 03:37:55.622 Error returned from recv, err=104
//M: 2019-03-06 03:37:55.622 Closing DAPNET connection
//M: 2019-03-06 03:37:55.622 Opening DAPNET connection
//M: 2019-03-06 03:37:55.670 Logging into DAPNET
//E: 2019-03-06 03:39:58.494 Error returned from recv, err=104
function isDAPNETGatewayConnected() {
    $logLines = array();
    $logLines1 = array();
    $logLines2 = array();
    
    // Collect last 20 lines  - see getDAPNETGatewayLog() down below for no. of line values (array_slice)
    if (file_exists("/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d").".log")) {
	$logPath1 = "/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d").".log";
	$logLines1 = preg_split('/\r\n|\r|\n/', `tail -n 5 $logPath1 | cut -d" " -f2- | tac`);
    }
    
    $logLines1 = array_filter($logLines1);

    if (sizeof($logLines1) == 0) {
        if (file_exists("/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d", time() - 86340).".log")) {
            $logPath2 = "/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d", time() - 86340).".log";
            $logLines2 = preg_split('/\r\n|\r|\n/', `tail -n 5 $logPath2 | cut -d" " -f2- | tac`);
        }
	
        $logLines2 = array_filter($logLines2);
    }

    $logLines = $logLines1 + $logLines2;

    $errorMessages = array('Cannot connect', 'Login failed', 'Error returned from recv');
    
    foreach($logLines as $dapnetMessageLine) {
	foreach($errorMessages as $errorLine) {
	    if (strpos($dapnetMessageLine, $errorLine) != FALSE)
		return false;
	}
    }

    return true;
}

//M: 2022-01-02 12:32:41.238 Opening YSF network connection 
//I: 2022-01-02 12:32:41.238 Opening UDP port on 42026 
//M: 2022-01-02 12:32:41.238      Linking at startup 
//M: 2022-01-02 12:32:41.238 Starting DGIdGateway-20210922_W0CHP 
//M: 2022-01-02 12:32:42.605 Link successful to MMDVM 
//M: 2022-01-02 12:32:49.537 DG-ID set to 0 (YSF: YSFGateway) via RF 
//M: 2022-01-02 12:32:50.619 *** 3 bleep! 
//M: 2022-01-02 12:33:41.263 Lost link to YSFGateway 
//M: 2022-01-02 12:34:49.656 DG-ID set to None via timeout 
//M: 2022-01-02 12:34:49.656 *** 2 bleep! 
//M: 2022-01-02 12:35:58.284 Opening YSF network connection 
//I: 2022-01-02 12:35:58.284 Opening UDP port on 4200 
//I: 2022-01-02 12:35:58.315 Loaded 1241 YSF reflectors 
//M: 2022-01-02 12:35:58.315 Opening IMRS network connection 
//I: 2022-01-02 12:35:58.343 Opening UDP port on 21110 
//M: 2022-01-02 12:35:58.344 Added YSF Gateway to DG-ID 0 (Static) 
//M: 2022-01-02 12:35:58.344 Opening YSF network connection 
//I: 2022-01-02 12:35:58.344 Opening UDP port on 42026 
//M: 2022-01-02 12:35:58.344      Linking at startup 
//M: 2022-01-02 12:35:58.344 Starting DGIdGateway-20210922_W0CHP 
//M: 2022-01-02 12:36:02.761 Link successful to MMDVM 
//M: 2022-01-02 13:30:04.841 Linked to YSFGateway
//M: 2022-01-02 12:36:58.363 Lost link to YSFGateway 
//M: 2022-01-02 12:42:32.053 Lost link to MMDVM
//M: 2022-01-02 18:01:31.065 DG-ID set to 0 (YSF: YSFGateway) via RF 
//M: 2022-01-02 18:01:37.554 *** 1 bleep! 
//M: 2022-01-02 18:04:17.222 DG-ID set to None via timeout 
//M: 2022-01-02 18:04:17.222 *** 2 bleep! 
//M: 2022-01-02 18:05:06.413 DG-ID set to 0 (YSF: YSFGateway) via Network 
function getDGIdLinks() {
    $logDGIdGWNow = "/var/log/pi-star/DGIdGateway-".gmdate("Y-m-d").".log";
    $logDGIdPrevious = "/var/log/pi-star/DGIdGateway-".gmdate("Y-m-d", time() - 86340).".log";
    $logSearchString = "DG-ID";
    $logLine = '';
    $linkedDGId = 'None Set';
    $LogError = "Cannot Open Log";

    if (file_exists($logDGIdGWNow) || file_exists($logDGIdPrevious)) {
        $logLine = exec("tail -5 $logDGIdGWNow | grep '$logSearchString'");
        if (!$logLine) {
            $logLine = exec("tail -10 $logDGIdPrevious | grep '$logSearchString'");
        }
    } else
        {
        return $LogError;
    }

    if (strpos($logLine, 'DG-ID set to')) {
        preg_match('/(?<=to )\S+(.*)/i', $logLine, $match); // find DG-ID # in log line after "set to" string.
        $linkedDGId = str_replace("(", "<br />(", $match[0]);
        $linkedDGId = str_replace("via", "<br />via", $linkedDGId);
        $linkedDGId = str_replace("timeout", "inactivity", $linkedDGId);
        $linkedDGId = "DG-ID: $linkedDGId";
    }
    return $linkedDGId;
}

//M: 2022-01-03 15:40:17.070 Starting M17Gateway-20211003_W0CHP
//I: 2022-01-03 15:40:17.071 Linked at startup to M17-USA A
//M: 2022-01-03 15:40:17.071 Opening M17 Network connection
//I: 2022-01-03 15:40:17.071 Opening UDP port on 17000
//M: 2022-01-03 15:40:18.193 Linked to reflector M17-USA A
//M: 2022-01-03 15:40:18.193 Link refused by reflector M17-USA A
//M: 2022-01-03 15:40:57.667 Link lost to reflector M17-USA A
//M: 2022-01-03 15:40:59.736 Unlinked from reflector M17-USA A
//M: 2022-01-03 15:48:27.222 Opened connection to the APRS Gateway
//M: 2022-01-03 15:48:27.223 Opening Rpt Network connection
//I: 2022-01-03 15:48:27.223 Opening UDP port on 17010
//I: 2022-01-03 15:48:27.224 Loaded 115 M17 reflectors
//I: 2022-01-03 15:48:27.228 Loaded the audio and index file for en_US
//M: 2022-01-03 15:48:27.228 Starting M17Gateway-20211003_W0CHP
//I: 2022-01-03 15:48:27.228 Linked at startup to M17-USA A
//M: 2022-01-03 15:48:27.228 Opening M17 Network connection
//M: 2022-01-09 19:29:52.863 Relinking to reflector M17-M17 C
//I: 2022-01-11 14:39:40.507 Linked at startup to M17-M17 C
//M: 2022-01-11 14:39:40.507 Opening M17 Network connection
//I: 2022-01-11 14:39:40.507 Opening UDP port on 17000
//M: 2022-01-11 14:39:40.550 Link refused by reflector M17-M17 C
//I: 2022-01-09 19:40:52.704 Loaded 118 M17 reflectors
//I: 2022-01-09 19:40:52.704 Loaded 118 M17 reflectors
//I: 2022-01-09 19:40:52.704 Loaded 118 M17 reflectors
//I: 2022-01-09 19:40:52.704 Loaded 118 M17 reflectors
//I: 2022-01-09 19:40:52.704 Loaded 118 M17 reflectors
function isM17GatewayConnected() {
    $logLines = array();
    $logLines1 = array();
    $logLines2 = array();
    
    // Collect last 20 lines  - see down below for no. of line values (array_slice)
    if (file_exists("/var/log/pi-star/M17Gateway-".gmdate("Y-m-d").".log")) {
	$logPath1 = "/var/log/pi-star/M17Gateway-".gmdate("Y-m-d").".log";
	$logLines1 = preg_split('/\r\n|\r|\n/', `tail -n 1 $logPath1 | cut -d" " -f2- | tac`);
    }
    
    $logLines1 = array_filter($logLines1);

    if (sizeof($logLines1) == 0) {
        if (file_exists("/var/log/pi-star/M17Gateway-".gmdate("Y-m-d", time() - 86340).".log")) {
            $logPath2 = "/var/log/pi-star/M17Gateway-".gmdate("Y-m-d", time() - 86340).".log";
            $logLines2 = preg_split('/\r\n|\r|\n/', `tail -n 1 $logPath2 | cut -d" " -f2- | tac`);
        }
	
        $logLines2 = array_filter($logLines2);
    }

    $logLines = $logLines1 + $logLines2;

    $errorMessages = array('Link lost' , 'Link refused');
    
    foreach($logLines as $m17MessageLine) {
		foreach($errorMessages as $errorLine) {
	    	if (strpos($m17MessageLine, $errorLine) != FALSE)
			return false;
		}
    }
    return true;
}

// M: 2000-00-00 00:00:00.000 M17, received RF late entry voice transmission from IU5BON to INFO
// M: 2000-00-00 00:00:00.000 M17, received RF end of transmission from IU5BON to INFO, 2.1 seconds, BER: 0.2%, RSSI: -47/-47/-47 dBm
// M: 2000-00-00 00:00:00.000 M17, received network voice transmission from IU5BON to ECHO
// M: 2000-00-00 00:00:00.000 M17, received network end of transmission from IU5BON to ECHO, 13.4 seconds
function getM17GatewayLog() {
    // Open Logfile and copy loglines into LogLines-Array()
    $logLines = array();
	$logLines1 = array();
	$logLines2 = array();
    if (file_exists("/var/log/pi-star/M17Gateway-".gmdate("Y-m-d").".log")) {
		$logPath1 = "/var/log/pi-star/M17Gateway-".gmdate("Y-m-d").".log";
		$logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Starting|witched)" $logPath1 | cut -d" " -f2- | tail -1`);
    }
	$logLines1 = array_filter($logLines1);
    if (sizeof($logLines1) == 0) {
        if (file_exists("/var/log/pi-star/M17Gateway-".gmdate("Y-m-d", time() - 86340).".log")) {
    		$logPath2 = "/var/log/pi-star/M17Gateway-".gmdate("Y-m-d", time() - 86340).".log";
			$logLines2 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Starting|witched)" $logPath2 | cut -d" " -f2- | tail -1`);
        }
		$logLines2 = array_filter($logLines2);
   }
	if (sizeof($logLines1) == 0) { $logLines = $logLines2; } else { $logLines = $logLines1; }
        return array_filter($logLines);
}

//M: 2022-01-02 12:32:41.238 Opening YSF network connection 
//I: 2022-01-02 12:32:41.238 Opening UDP port on 42026 
//M: 2022-01-02 12:32:41.238      Linking at startup 
//M: 2022-01-02 12:32:41.238 Starting DGIdGateway-20210922_W0CHP 
//M: 2022-01-02 12:32:42.605 Link successful to MMDVM 
//M: 2022-01-02 12:32:49.537 DG-ID set to 0 (YSF: YSFGateway) via RF 
//M: 2022-01-02 12:32:50.619 *** 3 bleep! 
//M: 2022-01-02 12:33:41.263 Lost link to YSFGateway 
//M: 2022-01-02 12:34:49.656 DG-ID set to None via timeout 
//M: 2022-01-02 12:34:49.656 *** 2 bleep! 
//M: 2022-01-02 12:35:58.284 Opening YSF network connection 
//I: 2022-01-02 12:35:58.284 Opening UDP port on 4200 
//I: 2022-01-02 12:35:58.315 Loaded 1241 YSF reflectors 
//M: 2022-01-02 12:35:58.315 Opening IMRS network connection 
//I: 2022-01-02 12:35:58.343 Opening UDP port on 21110 
//M: 2022-01-02 12:35:58.344 Added YSF Gateway to DG-ID 0 (Static) 
//M: 2022-01-02 12:35:58.344 Opening YSF network connection 
//I: 2022-01-02 12:35:58.344 Opening UDP port on 42026 
//M: 2022-01-02 12:35:58.344      Linking at startup 
//M: 2022-01-02 12:35:58.344 Starting DGIdGateway-20210922_W0CHP 
//M: 2022-01-02 12:36:02.761 Link successful to MMDVM 
//M: 2022-01-02 13:30:04.841 Linked to YSFGateway
//M: 2022-01-02 12:36:58.363 Lost link to YSFGateway 
//M: 2022-01-02 12:42:32.053 Lost link to MMDVM
//M: 2022-01-02 18:01:31.065 DG-ID set to 0 (YSF: YSFGateway) via RF 
//M: 2022-01-02 18:01:37.554 *** 1 bleep! 
//M: 2022-01-02 18:04:17.222 DG-ID set to None via timeout 
//M: 2022-01-02 18:04:17.222 *** 2 bleep! 
//M: 2022-01-02 18:05:06.413 DG-ID set to 0 (YSF: YSFGateway) via Network 
function getDGIdGatewayLog() {
    // Open Logfile and copy loglines into LogLines-Array()
    $logLines = array();
	$logLines1 = array();
	$logLines2 = array();
    if (file_exists("/var/log/pi-star/DGIdGateway-".gmdate("Y-m-d").".log")) {
		$logPath1 = "/var/log/pi-star/DGIdGateway-".gmdate("Y-m-d").".log";
		$logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Added|via)" $logPath1 | cut -d" " -f2- | tail -1`);
    }
	$logLines1 = array_filter($logLines1);
    if (sizeof($logLines1) == 0) {
        if (file_exists("/var/log/pi-star/DGiDGateway-".gmdate("Y-m-d", time() - 86340).".log")) {
    		$logPath2 = "/var/log/pi-star/DGIdGateway-".gmdate("Y-m-d", time() - 86340).".log";
			$logLines2 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Added|via)" $logPath2 | cut -d" " -f2- | tail -1`);
        }
		$logLines2 = array_filter($logLines2);
   }
	if (sizeof($logLines1) == 0) { $logLines = $logLines2; } else { $logLines = $logLines1; }
        return array_filter($logLines);
}

// show if mode is paused in side modes panel
function isPaused($mode) {
    if (file_exists("/etc/".$mode."_paused") && (getEnabled($mode, $configs) == 0) ) {
        $paused = true;
        return $paused;
    }
    return false;
}

// firewall status
function getFWstate () {
    if ( strpos(file_get_contents('/etc/iptables.rules'),"LOGNDROP") !== false ) {
        return 1;
    } else {
        return 0;
    }
}

// cron status
function getCronState () {
    if (isProcessRunning('cron',true) == 1) {
        return 1;
    } else {
        return 0;
    }
}

// Pi-Star Remote status
function getPSRState () {
    if (isProcessRunning('/usr/local/sbin/pistar-remote',true) == 1) {
        return 1;
    } else {
        return 0;
    }
}

// get DMR net status
function getDMRnetStatus($dmrNet) {
    $remoteStat = exec("cd /var/log/pi-star ; /usr/local/bin/RemoteCommand ".$_SESSION['DMRGatewayConfigs']['Remote Control']['Port']. " status | tail -n +2 | sed 's/ /\\r\\n/g' | grep ".$dmrNet." | grep -o conn");
    if ($remoteStat !== 'conn' && file_exists("/etc/.dmr-".$dmrNet."_disabled")) {
        return "disabled";
    } else {
        return "enabled";
    }
}

// status classes used in sysinfo.php
function getStatusClass($status, $disabled = false) {
    if ($status) {
    echo '<td class="active-service-cell" align="left" title="Service Active">';
    }
    else {
    if ($disabled)
        echo '<td class="disabled-service-cell" align="left" title="Service Disabled">';
    else
        echo '<td class="inactive-service-cell" align="left" title="Service Not Active">';
    }
}

// services status for admin page top status grid
function getServiceStatusClass($active) {
    echo (($active) ? 'active-mode-cell' : 'disabled-mode-cell');
}

// upnp state
function UPnPenabled() {
    $testupnp = exec('grep "pistar-upnp.service" /etc/crontab | cut -c 1');
    if (substr($testupnp, 0, 1) === '#') {
        return 0;
    } else {
        return 1;
    }
}

// Auto AP state
function autoAPenabled() {
if (file_exists('/etc/hostap.off')) {
        return 0;
    } else {
        return 1;
    }
}

// Pi-Star Watchdog status
function getPSWState () {
    if (isProcessRunning('/usr/local/sbin/pistar-watchdog',true) == 1) {
        return 1;
    } else {
        return 0;
    }
}

function getModeClass($status, $disabled = false) {
    if ($status) {
	    echo '<div class="active-mode-cell" title="Active">';
    }
    else {
	    if ($disabled) {
	        echo '<div class="disabled-mode-cell" title="Disabled">';
	    }
	    else {
	        echo '<div class="inactive-mode-cell" title="Inactive">';
	    }
    }
}

// shows if mode is enabled or not.
function showMode($mode, $configs) {
    if ($mode == "APRS Network") {
        if (getServiceEnabled('/etc/aprsgateway') == 1) {
            getModeClass(isProcessRunning("APRSGateway") && (isAPRSISGatewayConnected() ==1));
        } else {
            getModeClass(false,true);
        }
    } else
        if (getEnabled($mode, $configs) == 1) {
	        if ($mode == "D-Star Network") {
	            getModeClass(isProcessRunning("ircddbgatewayd"));
	        }
	        else if ($mode == "System Fusion Network") {
	            getModeClass(isProcessRunning("YSFGateway"));
	        }
	        else if ($mode == "P25 Network") {
	            getModeClass(isProcessRunning("P25Gateway"));
	        }
	        else if ($mode == "NXDN Network") {
	            getModeClass(isProcessRunning("NXDNGateway"));
	        }
	        else if ($mode == "M17 Network") {
	            getModeClass(isProcessRunning("M17Gateway") && (isM17GatewayConnected() == 1));
	        }
	        else if ($mode == "POCSAG Network") {
	            getModeClass(isProcessRunning("DAPNETGateway") && (isDAPNETGatewayConnected() == 1));
	        }
	        else if ($mode == "DMR Network") {
	            if (getConfigItem("DMR Network", "Address", $configs) == '127.0.0.1') {
		            getModeClass(isProcessRunning("DMRGateway"));
	            }
	            else {
		            getModeClass(isProcessRunning("MMDVMHost"));
	            }
	        }
	        else {
	            if ($mode == "D-Star" || $mode == "DMR" || $mode == "System Fusion" || $mode == "P25" || $mode == "NXDN" || $mode == "POCSAG" || $mode == "M17" || $mode == "AX 25") {
		            getModeClass(isProcessRunning("MMDVMHost"));
	            }
	        }
        }
    	else if ( ($mode == "YSF X-Mode") && (getEnabled("System Fusion", $configs) == 1) ) {
			getModeClass((isProcessRunning("MMDVMHost") && (isProcessRunning("YSF2DMR") || isProcessRunning("YSF2NXDN") || isProcessRunning("YSF2P25"))),
		    	($_SESSION['YSF2DMRConfigs']['Enabled']['Enabled'] || $_SESSION['YSF2NXDNConfigs']['Enabled']['Enabled'] || $_SESSION['YSF2P25Configs']['Enabled']['Enabled']) == false);
    	}
    	else if ( ($mode == "DMR X-Mode") && (getEnabled("DMR", $configs) == 1) ) {
			getModeClass((isProcessRunning("MMDVMHost") && (isProcessRunning("DMR2YSF") || isProcessRunning("DMR2NXDN"))),
		    	($_SESSION['DMR2YSFConfigs']['Enabled']['Enabled'] || $_SESSION['DMR2NXDNConfigs']['Enabled']['Enabled']) == false);
    	}
    	else if ( ($mode == "YSF2DMR Network") && (getEnabled("System Fusion", $configs) == 1) ) {
			getModeClass(isProcessRunning("YSF2DMR"), ($_SESSION['YSF2DMRConfigs']['Enabled']['Enabled']) == false);
    	}
    	else if ( ($mode == "YSF2NXDN Network") && (getEnabled("System Fusion", $configs) == 1) ) {
			getModeClass(isProcessRunning("YSF2NXDN"), ($_SESSION['YSF2NXDNConfigs']['Enabled']['Enabled']) == false);
    	}
    	else if ( ($mode == "YSF2P25 Network") && (getEnabled("System Fusion", $configs) == 1) ) {
			getModeClass(isProcessRunning("YSF2P25"), ($_SESSION['YSF2P25Configs']['Enabled']['Enabled']) == false);
    	}
    	else if ( ($mode == "DMR2NXDN Network") && (getEnabled("DMR", $configs) == 1) ) {
			getModeClass(isProcessRunning("DMR2NXDN"), ($_SESSION['DMR2NXDNConfigs']['Enabled']['Enabled']) == false);
    	}
    	else if ( ($mode == "DMR2YSF Network") && (getEnabled("DMR", $configs) == 1) ) {
			getModeClass(isProcessRunning("DMR2YSF"), ($_SESSION['DMR2YSFConfigs']['Enabled']['Enabled']) == false);
    	}
    	else {
			getModeClass(false, true);
    	}

        $mode = str_replace("System Fusion", "YSF", $mode);
        $mode = str_replace("AX 25", "AX.25", $mode);
        $mode = str_replace("Network", "Net", $mode);
        if (strpos($mode, 'YSF2') > -1) {
	        $mode = str_replace(" Net", "", $mode);
        }
        if (strpos($mode, 'DMR2') > -1) {
	        $mode = str_replace(" Net", "", $mode);
        }
    echo $mode."</div>\n";
}

// Open MMDVMHost Logfile and copy loglines into LogLines-Array()
function getMMDVMLog() {
    $logLines = array();
    $logLines1 = array();
    $logLines2 = array();
    if (file_exists(MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d").".log")) {
        $logPath = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d").".log";
        $fileList = array_filter(array("/etc/.GETNAMES", "/etc/.CALLERDETAILS", "/etc/.SHOWDMRTA", "/etc/.TGNAMES"), 'file_exists');
        if (!$file = array_shift($fileList)) { // no caller names/last caller selected
	    if(isset($_SESSION['PiStarRelease']['Pi-Star']['ProcNum']) && ($_SESSION['PiStarRelease']['Pi-Star']['ProcNum'] >= 4)) { // multi-core
		if ($_SESSION['CSSConfigs']['ExtraSettings']['LastHeardRows'] > 40 ) { // more than 40 rows selected
		    $logLines1 = explode("\n", `egrep -h "^M.*(from|end|watchdog|lost|Alias|0000)" $logPath | sed '/\(CSBK\|overflow\|Downlink\|Valid\|Invalid\)/d'`); // search entire log
	        } else {
		    $logLines1 = explode("\n", `tail -1500 $logPath | sed '/\(CSBK\|overflow\|Downlink\|Valid\|Invalid\)/d' | egrep -h "^M.*(from|end|watchdog|lost|Alias|0000)"`);  // 40 or less rows selected
	        }
	    } else { 
		$logLines1 = explode("\n", `tail -250 $logPath | sed '/\(CSBK\|overflow\|Downlink\|Valid\|Invalid\)/d' | egrep -h "^M.*(from|end|watchdog|lost|Alias|0000)"`); // single-core crap
	    }
            $lineNos = sizeof($logLines1);
            $logLines1 = array_slice($logLines1, -1500);
        } else { // caller names/last caller selected! keep perf. in check..
	    if(isset($_SESSION['PiStarRelease']['Pi-Star']['ProcNum']) && ($_SESSION['PiStarRelease']['Pi-Star']['ProcNum'] >= 4)) { // multi-core
		if ($_SESSION['CSSConfigs']['ExtraSettings']['LastHeardRows'] > 40 ) {  // more than 40 rows selected
		    $logLines1 = explode("\n", `tail -500 $logPath | sed '/\(CSBK\|overflow\|Downlink\|Valid\|Invalid\)/d' | egrep -h "^M.*(from|end|watchdog|lost|Alias|0000)"`); // search last 500 lines
		} else {
		    $logLines1 = explode("\n", `tail -250 $logPath | sed '/\(CSBK\|overflow\|Downlink\|Valid\|Invalid\)/d' | egrep -h "^M.*(from|end|watchdog|lost|Alias|0000)"`); // 40 or less rows selected
		}
	    } else {
		$logLines1 = explode("\n", `tail -250 $logPath | sed '/\(CSBK\|overflow\|Downlink\|Valid\|Invalid\)/d' | egrep -h "^M.*(from|end|watchdog|lost|Alias|0000)"`); // single-core crap
	    }
            $lineNos = sizeof($logLines1);
            $logLines1 = array_slice($logLines1, -1500);
        }
    }

    // current log is less than 150 lines; check previous log...
    if ($lineNos < 150) {
        if (file_exists(MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log")) {
	    $logPath = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log";
	    if(isset($_SESSION['PiStarRelease']['Pi-Star']['ProcNum']) && ($_SESSION['PiStarRelease']['Pi-Star']['ProcNum'] >= 4)) { // multi-core
		$logLines2 = explode("\n", `tail -500 $logPath | sed '/\(CSBK\|overflow\|Downlink\|Valid\|Invalid\)/d' | egrep -h "^M.*(from|end|watchdog|lost|Alias|0000)"`);
	    } else {
		$logLines2 = explode("\n", `tail -250 $logPath | sed '/\(CSBK\|overflow\|Downlink\|Valid\|Invalid\)/d' | egrep -h "^M.*(from|end|watchdog|lost|Alias|0000)"`); // single-core crap
	    }
	    $logLines2 = array_slice($logLines2, -1500);
        }
    }
    if ($lineNos < 150) {
        $logLines = $logLines1 + $logLines2;
    } else {
        $logLines = $logLines1;
    }
    $logLines = array_slice($logLines, -1500);
    return $logLines;
}

// Open Logfile and copy loglines into LogLines-Array()
function getYSFGatewayLog() {
    $logLines = array();
    $logLines1 = array();
    $logLines2 = array();
    if (file_exists(YSFGATEWAYLOGPATH."/".YSFGATEWAYLOGPREFIX."-".gmdate("Y-m-d").".log")) {
	$logPath1 = YSFGATEWAYLOGPATH."/".YSFGATEWAYLOGPREFIX."-".gmdate("Y-m-d").".log";
	$logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(onnection to|onnect to|ink|isconnect|Opening YSF network)" $logPath1 | sed '/Linked to MMDVM/d' | sed '/Link successful to MMDVM/d' | sed '/*Link/d' | tail -1`);
    }
    $logLines1 = array_filter($logLines1);
    if (sizeof($logLines1) == 0) {
	if (file_exists(YSFGATEWAYLOGPATH."/".YSFGATEWAYLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log")) {
	    $logPath2 = YSFGATEWAYLOGPATH."/".YSFGATEWAYLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log";
	    $logLines2 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(onnection to|onnect to|ink|isconnect|Opening YSF network)" $logPath2 | sed '/Linked to MMDVM/d' | sed '/Link successful to MMDVM/d' | sed '/*Link/d' | tail -1`);
	}
	$logLines2 = array_filter($logLines2);
    }
    if (sizeof($logLines1) == 0) {
	$logLines = $logLines2;
    }
    else {
	$logLines = $logLines1;
    }
    return array_filter($logLines);
}

// Open Logfile and copy loglines into LogLines-Array()
function getP25GatewayLog() {
    $logLines = array();
    $logLines1 = array();
    $logLines2 = array();
    if (file_exists(P25GATEWAYLOGPATH."/".P25GATEWAYLOGPREFIX."-".gmdate("Y-m-d").".log")) {
	$logPath1 = P25GATEWAYLOGPATH."/".P25GATEWAYLOGPREFIX."-".gmdate("Y-m-d").".log";
	$logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Starting|witched)" $logPath1 | cut -d" " -f2- | tail -1`);
    }
    $logLines1 = array_filter($logLines1);
    if (sizeof($logLines1) == 0) {
        if (file_exists(P25GATEWAYLOGPATH."/".P25GATEWAYLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log")) {
	    $logPath2 = P25GATEWAYLOGPATH."/".P25GATEWAYLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log";
	    $logLines2 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Starting|witched)" $logPath1 | cut -d" " -f2- | tail -1`);
        }
	$logLines2 = array_filter($logLines2);
    }
    if (sizeof($logLines1) == 0) {
	$logLines = $logLines2;
    }
    else {
	$logLines = $logLines1;
    }
    return array_filter($logLines);
}

// Open Logfile and copy loglines into LogLines-Array()
function getNXDNGatewayLog() {
    $logLines = array();
    $logLines1 = array();
    $logLines2 = array();
    if (file_exists("/var/log/pi-star/NXDNGateway-".gmdate("Y-m-d").".log")) {
	$logPath1 = "/var/log/pi-star/NXDNGateway-".gmdate("Y-m-d").".log";
    $logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|witched|Starting)" $logPath1 | cut -d" " -f2- | tail -1`);
    }
    $logLines1 = array_filter($logLines1);
    if (sizeof($logLines1) == 0) {
        if (file_exists("/var/log/pi-star/NXDNGateway-".gmdate("Y-m-d", time() - 86340).".log")) {
	    $logPath2 = "/var/log/pi-star/NXDNGateway-".gmdate("Y-m-d", time() - 86340).".log";
        $logLines2 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|witched|Starting)" $logPath2 | cut -d" " -f2- | tail -1`);
        }
	$logLines2 = array_filter($logLines2);
    }
    if (sizeof($logLines1) == 0) {
	$logLines = $logLines2;
    }
    else {
	$logLines = $logLines1;
    }
    return array_filter($logLines);
}

// Open Logfile and copy loglines into LogLines-Array()
function getDAPNETGatewayLog($myRIC) {
    $logLines = array();
    $logLines1 = array();
    $logLines2 = array();
    
    if (file_exists("/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d").".log")) {
	$logPath1 = "/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d").".log";
	$logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(Sending message)" $logPath1 | cut -d" " -f2- | tail -n 200 | tac`);
    }
    
    $logLines1 = array_filter($logLines1);

    if (sizeof($logLines1) == 0) {
        if (file_exists("/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d", time() - 86340).".log")) {
            $logPath2 = "/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d", time() - 86340).".log";
            $logLines2 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(Sending message)" $logPath2 | cut -d" " -f2- | tail -n 200 | tac`);
        }
	
        $logLines2 = array_filter($logLines2);
    }

    $logLines = $logLines1 + $logLines2;
    
    if (isset($myRIC) && ! empty($myRIC)) {
        $logLinesPersonal = array();

        // Traverse the whole array to extract personal RIC messages
        foreach($logLines as $key => $entry) {
	    
            // Extract RIC number
            $dMsgArr = explode(" ", $entry);
            $pocsag_ric = str_replace(',', '', $dMsgArr["8"]);
            
            // if RICs matches, move entry to Personal array
            if ($pocsag_ric == $myRIC) {
		$logLinesPersonal['X'.$key] = $entry;
		$logLines[$key] = '';
            }
        }

        $logLines = array_filter($logLines);
        $logLinesPersonal = array_filter($logLinesPersonal);
	// last 30
        $logLines = array_slice($logLines, 0, 20);
	
        // Is there any message for my RIC ?
        if (sizeof($logLinesPersonal) > 0) {

            // Add that special separator entry
            array_push($logLines, '<MY_RIC>');
	    // last 30
            $logLinesPersonal = array_slice($logLinesPersonal, 0, 20);
            $logLines = array_merge($logLines, $logLinesPersonal);
        }
    }
    else {
	// last 20
        $logLines = array_slice($logLines, 0, 20);
    }
    
    return $logLines;
}

// 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
// 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
// M: 2000-00-00 00:00:00.000 D-Star, received network header from M1ABC   /ABCD to CQCQCQ   via REF000 A
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received network voice header from M1ABC to TG 1
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received RF voice header from M1ABC to 5000
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received RF end of voice transmission, 1.8 seconds, BER: 3.9%
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received network end of voice transmission from M1ABC to TG 2, 0.0 seconds, 0% packet loss, BER: 0.0%
// M: 2000-00-00 00:00:00.000 DMR Slot 2, RF voice transmission lost, 1.1 seconds, BER: 6.5%
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received RF CSBK Preamble CSBK (1 to follow) from M1ABC to TG 1
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received network Data Preamble VSBK (11 to follow) from 123456 to TG 123456
// M: 2000-00-00 00:00:00.000 DMR Talker Alias (Data Format 1, Received 24/24 char): 'Hide the bottle from Ont'
// M: 2000-00-00 00:00:00.000 0000:  07 00 20 4F 6E 74 00 00 00                         *.. Ont...*
// M: 2000-00-00 00:00:00.000 DMR Slot 2, Embedded Talker Alias Block 3
// M: 2000-00-00 00:00:00.000 P25, received RF transmission from M1ABC to TG 10200
// M: 2000-00-00 00:00:00.000 Debug: P25RX: pos/neg/centre/threshold 106 -105 0 106
// M: 2000-00-00 00:00:00.000 Debug: P25RX: sync found in Ldu pos/centre/threshold 3986 9 104
// M: 2000-00-00 00:00:00.000 Debug: P25RX: pos/neg/centre/threshold 267 -222 22 245
// M: 2000-00-00 00:00:00.000 Debug: P25RX: sync found in Ldu pos/centre/threshold 3986 10 112
// M: 2000-00-00 00:00:00.000 P25, received RF end of transmission, 0.4 seconds, BER: 0.0%
// M: 2000-00-00 00:00:00.000 P25, received network transmission from 10999 to TG 10200
// M: 2000-00-00 00:00:00.000 P25, network end of transmission, 1.8 seconds, 0% packet loss
// M: 2000-00-00 00:00:00.000 YSF, received RF data from MW0MWZ     to ALL
// M: 2000-00-00 00:00:00.000 YSF, received RF end of transmission, 5.1 seconds, BER: 3.8%
// M: 2000-00-00 00:00:00.000 YSF, received network data from M1ABC      to ALL        at MB6IBK
// M: 2000-00-00 00:00:00.000 YSF, network watchdog has expired, 5.0 seconds, 0% packet loss, BER: 0.0%
// M: 2000-00-00 00:00:00.000 NXDN, received RF transmission from MW0MWZ to TG 65000
// M: 2000-00-00 00:00:00.000 Debug: NXDNRX: pos/neg/centre/threshold 106 -105 0 106
// M: 2000-00-00 00:00:00.000 Debug: NXDNRX: sync found in Ldu pos/centre/threshold 3986 9 104
// M: 2000-00-00 00:00:00.000 Debug: NXDNRX: pos/neg/centre/threshold 267 -222 22 245
// M: 2000-00-00 00:00:00.000 Debug: NXDNRX: sync found in Ldu pos/centre/threshold 3986 10 112
// M: 2000-00-00 00:00:00.000 NXDN, received RF end of transmission, 0.4 seconds, BER: 0.0%
// M: 2000-00-00 00:00:00.000 NXDN, received network transmission from 10999 to TG 65000
// M: 2000-00-00 00:00:00.000 NXDN, network end of transmission, 1.8 seconds, 0% packet loss
// M: 2000-00-00 00:00:00.000 POCSAG, transmitted 1 frame(s) of data from 1 message(s)
function getHeardList($logLines) {
    //array_multisort($logLines,SORT_DESC);
    $heardList = array();
    $ts1duration = "";
    $ts1loss	 = "";
    $ts1ber	 = "";
    $ts1rssi	 = "";
    $ts2duration = "";
    $ts2loss	 = "";
    $ts2ber	 = "";
    $ts2rssi	 = "";
    $dstarduration = "";
    $dstarloss	 = "";
    $dstarber	 = "";
    $dstarrssi	 = "";
    $ysfduration = "";
    $ysfloss	 = "";
    $ysfber	 = "";
    $ysfrssi	 = "";
    $p25duration = "";
    $p25loss	 = "";
    $p25ber	 = "";
    $p25rssi	 = "";
    $nxdnduration= "";
    $nxdnloss	 = "";
    $nxdnber	 = "";
    $nxdnrssi	 = "";
    $m17duration = "";
    $m17loss	 = "---";
    $m17ber	 = "---";
    $m17rssi	 = "---";
    $pocsagduration = "";
    $ts1alias    = "---";
    $ts2alias    = "---";
    $alias       = "";
    $ts1dbName      = "";
    $ts2dbName      = "";
    $dbName      = "";
    foreach ($logLines as $logLine) {
	$duration	= "";
	$loss		= "";
	$ber		= "";
	$rssi		= "";
    	$ts1dbName      = "";
    	$ts2dbName      = "";
    	$dbName         = "";
	//removing invalid lines
	if(strpos($logLine,"BS_Dwn_Act")) {
	    continue;
	}
	else if(strpos($logLine,"invalid access")) {
	    continue;
	}
    else if(strpos($logLine,"packet received from an invalid source")) {
        continue;
    }
	else if(strpos($logLine,"received RF header for wrong repeater")) {
	    continue;
	}
	else if(strpos($logLine,"unable to decode the network CSBK")) {
	    continue;
	}
	else if(strpos($logLine,"overflow in the DMR slot RF queue")) {
	    continue;
	}
    else if(strpos($logLine,"overflow in the System Fusion RF queue")) {
        continue;
    }
	else if(strpos($logLine,"non repeater RF header received")) {
	    continue;
	}
	else if(strpos($logLine,"Embedded Talker Alias")) {
            continue;
	}
	else if(strpos($logLine,"DMR Talker Alias")) {
	    continue;
	}
	else if(strpos($logLine,"CSBK Preamble")) {
            continue;
	}
	else if(strpos($logLine,"Preamble CSBK")) {
            continue;
	}

      if(strpos($logLine, "0000")){
      	$decodedAlias = $decodedAlias = preg_replace('/[\x00-\x1F\x7F-\xA0\xAD]/u', '', decodeAlias($logLine));
        if ($decodedAlias == "" && $alias == "") $decodedAlias="---";
        else if ($alias!="---") $alias = str_replace("---", "", $alias);
      	if ($alias == "")
	      	$alias = $decodedAlias;
	    else
	    	$alias = $decodedAlias.$alias;
      }
      if (strpos($logLine,"Embedded Talker Alias")) {
      	switch (substr($logLine, 27, strpos($logLine,",") - 27)) {
          case "DMR Slot 1":
            $ts1alias = $alias;
            break;
          case "DMR Slot 2":
            $ts2alias = $alias;
            break;
        }
      }

      if (strpos($logLine,"Name:")) {
        switch (substr($logLine, 27, strpos($logLine,",") - 27)) {
          case "DMR Slot 1":
            $ts1dbName = $dbName;
            break;
          case "DMR Slot 2":
            $ts2dbName = $dbName;
            break;
        }
      }

       if(strpos($logLine, "end of") || strpos($logLine, "watchdog has expired") || strpos($logLine, "ended RF data") || strpos($logLine, "d network data") || strpos($logLine, "RF user has timed out") || strpos($logLine, "transmission lost") || strpos($logLine, "POCSAG")) {
           $lineTokens = explode(", ",$logLine);
           if (array_key_exists(2,$lineTokens)) {
               $duration = strtok($lineTokens[2], " ");
           }
           if (array_key_exists(3,$lineTokens)) {
               $loss = $lineTokens[3];
           }
           // The change to this code was causing all FCS traffic to always show TOut rather than the timer.
           // This version should still show time-out when needed, AND show the time if it exists.
           if (strpos($logLine,"RF user has timed out") || strpos($logLine,"watchdog has expired")) {
               if (array_key_exists(2, $lineTokens) && strpos($lineTokens[2], "seconds")) {
                   $duration = strtok($lineTokens[2], " "); 
               }
               else { 
                   $duration = "Timeout";
               }
               $ber = "---";
           }
	    // if RF-Packet with no BER reported (e.g. YSF Wires-X commands) then RSSI is in LOSS position
	    if (startsWith($loss,"RSSI")) {
		$lineTokens[4] = $loss; //move RSSI to the position expected on code below
		$loss = 'BER: ---';
	    }
	    
	    // if RF-Packet, no LOSS would be reported, so BER is in LOSS position
	    if (startsWith($loss,"BER")) {
		$ber = substr($loss, 5);
		$loss = "0%";
		if (array_key_exists(4,$lineTokens) && startsWith($lineTokens[4],"RSSI")) {
		    $rssi = substr($lineTokens[4], 6);
		    $dBraw = substr($rssi, strrpos($rssi,'/')+1); //average only
		    $relint = intval($dBraw) + 93;
		    $signal = round(($relint/6)+9, 0);
		    $meterVal = str_replace("-","", $dBraw);
		    $meterVal = str_replace(" dBm","", $meterVal);
		    if ($signal < 0) {
			$signal = 0;
		    }
		    if ($signal >= 9) {
			$signal = 9;
		    }
		    if ($relint > 0) {
			if ($meterVal <= 67) {
			    $rssi = "<meter id='S-meter' value=\"1\" high=\"1\"></meter> S{$signal}+{$relint}dB <span class='noMob'>({$dBraw})</span>";
			} elseif ($meterVal >= 68 && $meterVal < 70) {
			    $rssi = "<meter id='S-meter' value=\".8\" low=\".8\"></meter> S{$signal}+{$relint}dB <span class='noMob'>({$dBraw})</span>";
			} elseif ($meterVal >= 70 && $meterVal < 80) {
			    $rssi = "<meter id='S-meter' value=\".6\" high=\".5\"></meter> S{$signal}+{$relint}dB <span class='noMob'>({$dBraw})</span>";
			} elseif ($meterVal >= 80  && $meterVal < 90) {
			    $rssi = "<meter id='S-meter' low=\".5\" optimum=\".8\" high=\".75\" value=\".3\"></meter> S{$signal}+{$relint}dB <span class='noMob'>({$dBraw})</span>";
			} elseif ($dBraw >= 90) {
			    $rssi = "<meter id='S-meter' low=\".25\" optimum=\".8\" high=\".75\" value=\".15\"> S{$signal}+{$relint}dB <span class='noMob'>({$dBraw})</span>";
			} else {
			    $rssi = "S{$signal}+{$relint}dB <span class='noMob'>({$dBraw})</span>";
			}
		    } else {
			$rssi = "S{$signal} <span class='noMob'>({$dBraw})</span>";
		    }
		}
	    }
	    else {
		$loss = strtok($loss, " ");
		if (array_key_exists(4,$lineTokens)) {
		    $ber = substr($lineTokens[4], 5);
		    $ber = preg_replace('/ - Name(.*)/', '', $ber);
		}
	    }

	    if (strpos($logLine,"ended RF data") || strpos($logLine,"d network data")) {
		switch (substr($logLine, 27, strpos($logLine,",") - 27)) {
		    case "DMR Slot 1":
			$ts1duration = "DMR Data";
			break;
		    case "DMR Slot 2":
			$ts2duration = "DMR Data";
			break;
		}
	    }
	    else {
		switch (substr($logLine, 27, strpos($logLine,",") - 27)) {
		    case "D-Star":
			$dstarduration	= $duration;
			$dstarloss	= $loss;
			$dstarber	= $ber;
			$dstarrssi	= $rssi;
			break;
		    case "DMR Slot 1":
			$ts1duration	= $duration;
			$ts1loss	= $loss;
			$ts1ber		= $ber;
			$ts1rssi	= $rssi;
			break;
		    case "DMR Slot 2":
			$ts2duration	= $duration;
			$ts2loss	= $loss;
			$ts2ber		= $ber;
			$ts2rssi	= $rssi;
			break;
		    case "YSF":
			$ysfduration	= $duration;
			$ysfloss	= $loss;
			$ysfber		= $ber;
			$ysfrssi	= $rssi;
			break;
		    case "P25":
			$p25duration	= $duration;
			$p25loss	= $loss;
			$p25ber		= $ber;
			$p25rssi	= $rssi;
			break;
		    case "NXDN":
			$nxdnduration	= $duration;
			$nxdnloss	= $loss;
			$nxdnber	= $ber;
			$nxdnrssi	= $rssi;
			break;
		case "M17":
			$m17duration	= $duration;
			$m17loss	= "---";
			$m17ber		= "---";
			$m17rssi	= "---";
			break;
		    case "POCSAG":
			$alias = "";
			$pocsagduration	= "POCSAG Data";
			break;
		}
	    }
	}
	
	$timestamp = substr($logLine, 3, 19);
	$mode = substr($logLine, 27, strpos($logLine,",") - 27);
	$callsign2 = substr($logLine, strpos($logLine,"from") + 5, strpos($logLine,"to") - strpos($logLine,"from") - 6);                                                                   
	$callsign = $callsign2;
	if (strpos($callsign2,"/") > 0) {
	    $callsign = substr($callsign2, 0, strpos($callsign2,"/"));
	}
	$callsign = trim($callsign);
	$id ="";
	if ($mode == "D-Star") {
	    $id = substr($callsign2, strpos($callsign2,"/") + 1);
	}

        if (strpos($logLine, "Name:")) {
            $dbName2 = substr($logLine, strpos($logLine, "Name:") + 5);
            $dbName2 = trim($dbName2);
            $dbName2 = explode("Name:", $dbName2)[0];
            $dbName2 = str_replace("Name:", "", $dbName2);
	    //$dbName  = ucfirst(strtolower($dbName2)); // fix malformed cases in shitty-ass RadioID DB :-(
	    $dbName  = $dbName2; // fix malformed cases in shitty-ass RadioID DB :-(
        } else {
	    $dbName = " ";
	}

	$target = trim(substr($logLine, strpos($logLine, "to") + 3));
	$target = preg_replace('/ - Name(.*)/', '', $target);
	// Handle more verbose logging from MMDVMHost
        if (strpos($target,",") !== 'false') {
	    $target = explode(",", $target)[0];
	}
	
	$source = "RF";
	if (strpos($logLine,"network") > 0 || strpos($logLine,"POCSAG") > 0) {
	    $source = "Net";
	}
	
	switch ($mode) {
	    case "D-Star":
		$duration	= $dstarduration;
		$loss		= $dstarloss;
		$ber		= $dstarber;
		$rssi		= $dstarrssi;
		break;
	    case "DMR Slot 1":
		$duration	= $ts1duration;
		$loss		= $ts1loss;
		$ber		= $ts1ber;
		$rssi		= $ts1rssi;
		break;
	    case "DMR Slot 2":
		$duration	= $ts2duration;
		$loss		= $ts2loss;
		$ber		= $ts2ber;
		$rssi		= $ts2rssi;
		break;
	    case "YSF":
                $duration	= $ysfduration;
                $loss		= $ysfloss;
                $ber		= $ysfber;
		$rssi		= $ysfrssi;
		$target		= preg_replace('!\s+!', ' ', $target);
                break;
	    case "P25":
		if ($source == "Net" && $target == "TG 10") {
		    $callsign = "PARROT";
		}
		if ($source == "Net" && $callsign == "10999") {
		    $callsign = "MMDVM";
		}
                $duration	= $p25duration;
                $loss		= strlen($p25loss) ? $p25loss : "---";
                $ber		= strlen($p25ber) ? $p25ber : "---";
		$rssi		= $p25rssi;
                break;
	    case "NXDN":
		if ($source == "Net" && $target == "TG 10") {
		    $callsign = "PARROT";
		}
                $duration	= $nxdnduration;
                $loss           = strlen($nxdnloss) ? $nxdnloss : "---";
                $ber            = strlen($nxdnber) ? $nxdnber : "---";
		$rssi		= $nxdnrssi;
                break;
	    case "M17":
		$duration	= $m17duration;
		$loss		= $m17loss;
		$ber		= $m17ber;
		$rssi		= $m17rssi;
		break;
	    case "POCSAG":
		$callsign	= "DAPNET";
		$target		= "";
		$duration	= "POCSAG";
		$loss		= "";
                $ber		= "";
		break;
	}
	
	if ( strlen($callsign) < 11 ) {
	    array_push($heardList, array($timestamp, $mode, $callsign, $id, $target, $source, $duration, $loss, $ber, $rssi, $alias, $dbName));
	    $duration = "";
	    $loss ="";
	    $ber = "";
	    $rssi = "";
	    $alias = "";
	    $ts1alias   = "---";
	    $ts2alias   = "---";
	    $dbName = "";
	}
    }
    return $heardList;
}

// returns last heard list from log
function getLastHeard($logLines) {
    $lastHeard = array();
    $heardCalls = array();
    $heardList = getHeardList($logLines);
    foreach ($heardList as $listElem) {
	if ( ($listElem[1] == "D-Star") || ($listElem[1] == "YSF") || ($listElem[1] == "P25") || ($listElem[1] == "NXDN") || ($listElem[1] == "M17") || ($listElem[1] == "POCSAG") || (startsWith($listElem[1], "DMR")) ) {

	    $callUuid = $listElem[2]."#".$listElem[1].$listElem[3].$listElem[5];
	    if (!empty($listElem[10])) {
		$listElem[10] = "Talker Alias: ".$listElem[10]."";
	    }
	    if(!(array_search($callUuid, $heardCalls) > -1)) {
		array_push($heardCalls, $callUuid);
		array_push($lastHeard, $listElem);
	    }
	}
    }
    return $lastHeard;
}

// returns mode of repeater actual working in
function getActualMode($metaLastHeard, &$configs) {
    $utc_tz =  new DateTimeZone('UTC');
    $local_tz = new DateTimeZone(date_default_timezone_get ());
    $listElem = $metaLastHeard[0];
    $timestamp = new DateTime($listElem[0], $utc_tz);
    $timestamp->setTimeZone($local_tz); 
    $mode = $listElem[1];
    if (startsWith($mode, "DMR")) {
	$mode = "DMR";
    }
    
    $now =  new DateTime();
    $hangtime = getConfigItem("General", "ModeHang", $configs);
    
    if ($hangtime != "") {
	$timestamp->add(new DateInterval('PT' . $hangtime . 'S'));
    }
    else {
	$source = $listElem[5];
	if ($source == "RF" && $mode === "D-Star") {
	    $hangtime = getConfigItem("D-Star", "ModeHang", $configs);
	}
	else if ($source == "Net" && $mode === "D-Star") {
	    $hangtime = getConfigItem("D-Star Network", "ModeHang", $configs);
	}
	else if ($source == "RF" && $mode === "DMR") {
	    $hangtime = getConfigItem("DMR", "ModeHang", $configs);
	}
	else if ($source == "Net" && $mode === "DMR") {
	    $hangtime = getConfigItem("DMR Network", "ModeHang", $configs);
	}
	else if ($source == "RF" && $mode === "YSF") {
	    $hangtime = getConfigItem("System Fusion", "ModeHang", $configs);
	}
	else if ($source == "Net" && $mode === "YSF") {
	    $hangtime = getConfigItem("System Fusion Network", "ModeHang", $configs);
	}
	else if ($source == "RF" && $mode === "P25") {
	    $hangtime = getConfigItem("P25", "ModeHang", $configs);
	}
	else if ($source == "Net" && $mode === "P25") {
	    $hangtime = getConfigItem("P25 Network", "ModeHang", $configs);
	}
	else if ($source == "RF" && $mode === "NXDN") {
	    $hangtime = getConfigItem("NXDN", "ModeHang", $configs);
	}
	else if ($source == "Net" && $mode === "NXDN") {
	    $hangtime = getConfigItem("NXDN Network", "ModeHang", $configs);
	}
	else if ($source == "RF" && $mode === "M17") {
	    $hangtime = getConfigItem("M17", "ModeHang", $configs);
	}
	else if ($source == "Net" && $mode === "M17") {
	    $hangtime = getConfigItem("M17 Network", "ModeHang", $configs);
	}
	else if ($source == "Net" && $mode === "POCSAG") {
	    $hangtime = getConfigItem("POCSAG Network", "ModeHang", $configs);
	}
	else {
	    $hangtime = getConfigItem("General", "RFModeHang", $configs);
	}
	$timestamp->add(new DateInterval('PT' . $hangtime . 'S'));
    }
    if ($listElem[6] != null) { //if terminated, hangtime counts after end of transmission
	$timestamp->add(new DateInterval('PT' . ceil($listElem[6]) . 'S'));
    }
    else { //if not terminated, always return mode
	return $mode;
    }
    if ($now->format('U') > $timestamp->format('U')) {
	return "idle";
    } 
    else {
	return $mode;
    }
}

// returns link-states of all D-Star-modules
function getDSTARLinks() {
    if (filesize(LINKLOGPATH."/Links.log") == 0) {
	return "<div>Not Linked</div>";
    }
    if ($linkLog = fopen(LINKLOGPATH."/Links.log",'r')) {
	while ($linkLine = fgets($linkLog)) {
	    $linkDate	= "&nbsp;";
	    $protocol	= "&nbsp;";
	    $linkType	= "&nbsp;";
	    $linkSource	= "&nbsp;";
	    $linkDest	= "&nbsp;";
	    $linkDir	= "&nbsp;";
	    // Reflector-Link, sample:
	    // 2011-09-22 02:15:06: DExtra link - Type: Repeater Rptr: DB0LJ	B Refl: XRF023 A Dir: Outgoing
	    // 2012-04-03 08:40:07: DPlus link - Type: Dongle Rptr: DB0ERK B Refl: REF006 D Dir: Outgoing
	    // 2012-04-03 08:40:07: DCS link - Type: Repeater Rptr: DB0ERK C Refl: DCS001 C Dir: Outgoing
	    if(preg_match_all('/^(.{19}).*(D[A-Za-z]*).*Type: ([A-Za-z]*).*Rptr: (.{8}).*Refl: (.{8}).*Dir: (.{8})/',$linkLine,$linx) > 0){
		$linkDate	= $linx[1][0];
		$protocol	= $linx[2][0];
		$linkType	= $linx[3][0];
		$linkSource	= $linx[4][0];
		$linkDest	= $linx[5][0];
		$linkDir	= $linx[6][0];
	    }
	    // CCS-Link, sample:
	    // 2013-03-30 23:21:53: CCS link - Rptr: PE1AGO C Remote: PE1KZU	Dir: Incoming
	    if(preg_match_all('/^(.{19}).*(CC[A-Za-z]*).*Rptr: (.{8}).*Remote: (.{8}).*Dir: (.{8})/',$linkLine,$linx) > 0){
		$linkDate	= $linx[1][0];
		$protocol	= $linx[2][0];
		$linkType	= $linx[2][0];
		$linkSource	= $linx[3][0];
		$linkDest	= $linx[4][0];
		$linkDir	= $linx[5][0];
	    }
	    // Dongle-Link, sample: 
	    // 2011-09-24 07:26:59: DPlus link - Type: Dongle User: DC1PIA	Dir: Incoming
	    // 2012-03-14 21:32:18: DPlus link - Type: Dongle User: DC1PIA Dir: Incoming
	    if(preg_match_all('/^(.{19}).*(D[A-Za-z]*).*Type: ([A-Za-z]*).*User: (.{6,8}).*Dir: (.*)$/',$linkLine,$linx) > 0){
		$linkDate	= $linx[1][0];
		$protocol	= $linx[2][0];
		$linkType	= $linx[3][0];
		$linkSource	= "&nbsp;";
		$linkDest	= $linx[4][0];
		$linkDir	= $linx[5][0];
	    }
        if (strtolower(substr($linkDir, 0, 2)) == "in") { $linkDir = "In"; }
        if (strtolower(substr($linkDir, 0, 3)) == "out") { $linkDir = "Out"; }
        $out = $linkDest." ".$protocol."/".$linkDir;
	}
    }
    fclose($linkLog);
    return $out;
}

// returns actual link state of specific mode
function getActualLink($logLines, $mode) {
    switch ($mode) {
	case "D-Star":
	    //M: 2016-05-02 07:04:10.504 D-Star link status set to "Verlinkt zu DCS002 S"
    	    if (isProcessRunning("ircddbgatewayd")) {
		return getDSTARLinks();
    	    } 
	    else {
    		return "<div class='inactive-mode-cell'>Service Not Started</div>";
    	    }
            break;

	case "DMR Slot 1":
	case "DMR Slot 2":
	    //M: 2016-04-03 16:16:18.638 DMR Slot 2, received network voice header from 4000 to 2625094
	    //M: 2020-01-22 01:54:50.780 DMR Slot 2, received network voice header from 4000 to TG 9
	    //M: 2016-04-03 19:30:03.099 DMR Slot 2, received network voice header from 4020 to 2625094
	    //M: 2017-09-03 08:10:42.862 DMR Slot 2, received network data header from M6JQD to TG 9, 5 blocks
            if (isProcessRunning("MMDVMHost") || isProcessRunning("DMRGateway")) {
		foreach ($logLines as $logLine) {
        	    if(strpos($logLine,"unable to decode the network CSBK")) {
			continue;
		    }
		    else if(substr($logLine, 27, strpos($logLine,",") - 27) == $mode) {
			$to = "";
			$from = "";
			if (strpos($logLine, "from") != FALSE) {
			    $from = trim(get_string_between($logLine, "from", "to"));
			}
			if (strpos($logLine,"to")) {
			    $to = trim(substr($logLine, strpos($logLine,"to") + 3));
			    $to = preg_replace('/ - Name(.*)/', '', $to);
			}
			if ($from !== "") {
			    if ($from === "4000") {
				return "Unlinked";
			    }
			}
			if ($to !== "") {
			    if (substr($to, 0, 3) !== 'TG ') {
				continue;
			    }
			    if ($to === "TG 4000") {
				return "Unlinked";
			    }
			    if (strpos($to, ',') !== false) {
				$to = substr($to, 0, strpos($to, ','));
			    }
			    return $to;
			}
		    }
		}
		return "Unlinked";
	    }
	    else {
		return "<div class='inactive-mode-cell'>Service Not Started</div>";
	    }
            break; 

	case "YSF":
	    // 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
	    // 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
	    // M: 0000-00-00 00:00:00.000 Connect to 62829 has been requested by M1ABC
	    // M: 0000-00-00 00:00:00.000 Automatic connection to 62829
	    // New YSFGateway Format
	    // M: 0000-00-00 00:00:00.000 Opening YSF network connection
	    // M: 0000-00-00 00:00:00.000 Automatic (re-)connection to 16710 - "GB SOUTH WEST   "
	    // M: 0000-00-00 00:00:00.000 Automatic (re-)connection to FCS00290
	    // M: 0000-00-00 00:00:00.000 Linked to GB SOUTH WEST   
	    // M: 0000-00-00 00:00:00.000 Linked to FCS002-90
	    // M: 0000-00-00 00:00:00.000 Disconnect via DTMF has been requested by M1ABC
	    // M: 0000-00-00 00:00:00.000 Connect to 00003 - "YSF2NXDN        " has been requested by M1ABC
	    // M: 0000-00-00 00:00:00.000 Link has failed, polls lost
	    if (isProcessRunning("YSFGateway")) {
		$to = "";
		foreach($logLines as $logLine) {
		    if ( (strpos($logLine,"Linked to")) && (!strpos($logLine,"Linked to MMDVM")) ) {
			$to = trim(substr($logLine, 37, 16));
			if (substr($to, 0, 3) === "FCS") {
			    $to = str_replace(' ', '', str_replace('-', '', $to));
			}
		    }
            else if (strpos($logLine,"Automatic (re-)connection to")) {
			if (strpos($logLine,"Automatic (re-)connection to FCS")) {
			    $to = substr($logLine, 56, 8);
			}
			else {
                  	    $to = substr($logLine, 56, 5);
			}
		    }
            else if (strpos($logLine,"Connect to")) {
			$to = substr($logLine, 38, 5);
		    }
			else if (strpos($logLine,"Automatic connection to")) {
			$to = substr($logLine, 51, 5);
		    }
			else if (strpos($logLine,"Disconnect via DTMF")) {
			$to = "Not Linked";
		    }
			else if (strpos($logLine,"Opening YSF network connection")) {
			$to = "Not Linked";
		    }
			else if (strpos($logLine,"Link has failed")) {
			$to = "Not Linked";
		    }
			else if (strpos($logLine,"DISCONNECT Reply")) {
			$to = "Not Linked";
		    }
		    if ($to !== "") {
			return $to;
		    }
		}
		return "Not Linked";
            } 
	    else {
		return "Service Not Started";
            }
            break;
	    
	case "NXDN":
            // 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
            // 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
            // 2000-01-01 00:00:00.000 Linked at startup to reflector 65000
            // 2000-01-01 00:00:00.000 Unlinked from reflector 10100 by M1ABC
            // 2000-01-01 00:00:00.000 Linked to reflector 10200 by M1ABC
            // 2000-01-01 00:00:00.000 No response from 10200, unlinking
            // **
            // ** Latest NXDNGateway
            // **
            // 2020-11-04 08:47:48.297 Statically linked to reflector 65000
            // 2020-11-04 08:47:48.297 Switched to reflector 65000 due to network activity
            // 2020-11-04 08:47:48.297 Switched to reflector 65000 due to RF activity from M1ABC
            // 2020-11-04 08:47:48.297 Switched to reflector 65000 by remote command
            // 2020-11-04 08:47:48.297 Unlinking from reflector 65000 by M1ABC
            // 2020-11-04 08:47:48.297 Unlinking from 65000 due to inactivity
            // 2020-11-04 08:47:48.297 Unlinked from reflector 65000 by remote command
            if (isProcessRunning("NXDNGateway")) {
                foreach ( array_reverse($logLines) as $logLine ) {
                    $to = "";
                    if (strpos($logLine,"Statically linked to")) {
                        $to = preg_replace('/[^0-9]/', '', substr($logLine, 55, 5));
                        $to = preg_replace('/[^0-9]/', '', $to);
                        $nxdncache = fopen("/tmp/NXDNLink.txt", "w");
                        $num = fwrite($nxdncache,$to);
                        fclose($nxdncache);
                        return "TG".$to;
                    } else
                    if (strpos($logLine,"Switched to reflector")) {
                        $to = preg_replace('/[^0-9]/', '', substr($logLine, 46, 5));
                        $to = preg_replace('/[^0-9]/', '', $to);
                        $nxdncache = fopen("/tmp/NXDNLink.txt", "w");
                        $num = fwrite($nxdncache,$to);
                        fclose($nxdncache);
                        return "TG".$to;
                    } else
                    if (strpos($logLine,"Starting NXDNGateway")) {
                        return "<div class='inactive-mode-cell'>Not Linked</div>";
                    } else
                    if (strpos($logLine,"Unlinking")) {
                        return "<div class='inactive-mode-cell'>Not Linked</div>";
                    } else
                    if (strpos($logLine,"Unlinked")) {
                        $num = 0;
                        $nxdncache = fopen("/tmp/NXDNLink.txt", "w");
                        $num = fwrite($nxdncache,"Not Linked");
                        fclose($nxdncache);
                        return "<div class='inactive-mode-cell'>Not Linked</div>";
                    } else
                    if(!file_exists("/tmp/NDXNLink.txt")) {
                        return "<div class='inactive-mode-cell'>Not Linked</div>";
                    } else {
                        $to = file_get_contents("/tmp/NXDNLink.txt");
                        return "TG".$to;
                    }
                }
                return "<div class='inactive-mode-cell'>Not Linked</div>";
            }
	    else {
		return "<div class='inactive-mode-cell'>Service Not Started</div>";
            }
            break;

    case "M17":
            if (isProcessRunning("M17Gateway")) {
		foreach($logLines as $logLine) {
		    if(preg_match_all('/Linked .* reflector (M17-.{3} [A-Z])/', $logLine, $linx) > 0) {
			return $linx[1][0];
		    }
		    else if (strpos($logLine, "Switched to reflector")) {
			return (substr($logLine, 46, 9));
		    }
		    else if (strpos($logLine, "Linking to reflector")) {
			return (substr($logLine, 45, 9));
		    }
		    else if (strpos($logLine, "Relinked from")) {
			return (substr($logLine, 51, 9));
		    }
		    else if (strpos($logLine, "Relinking to")) {
			return (substr($logLine, 47, 9));
		    }
		    else if (strpos($logLine,"is starting") || strpos($logLine,"Unlinking") || strpos($logLine,"Unlinked")) {
			return "<div class='inactive-mode-cell'>Not Linked</div>";
		    }
		}
		return "<div class='inactive-mode-cell'>Not Linked</div>";
            }
	    else {
		return "<div class='inactive-mode-cell'>Service Not Started</div>";
            }
	    break;
 
	case "P25":
	// 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
	// 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
        // 2020-11-04 08:40:35.499 Statically linked to reflector 10100
        // 2020-11-04 08:40:35.499 Switched to reflector 10100 due to network activity
        // 2020-11-04 08:40:35.499 Switched to reflector 10100 due to RF activity from M1ABC
        // 2020-11-04 08:40:35.499 Switched to reflector 10100 by remote command
        // 2020-11-04 08:40:35.499 Unlinking from 10100 due to inactivity
        // 2020-11-04 08:40:35.499 Unlinking from reflector 10100 by M1ABC
        // 2020-11-04 08:40:35.499 Unlinked from reflector 10100 by remote command
	    if (isProcessRunning("P25Gateway")) {
		foreach ( array_reverse($logLines) as $logLine ) {
		    $to = "";
                    if (strpos($logLine,"Statically linked to")) {
                        $to = preg_replace('/[^0-9]/', '', substr($logLine, 55, 5));
                        $to = preg_replace('/[^0-9]/', '', $to);
                        $p25cache = fopen("/tmp/P25Link.txt", "w");
                        $num = fwrite($p25cache,$to);
                        fclose($p25cache);
                        return "TG".$to;
		    } else
		    if (strpos($logLine,"Switched to reflector")) {
		    	$to = preg_replace('/[^0-9]/', '', substr($logLine, 46, 5));
		    	$to = preg_replace('/[^0-9]/', '', $to);
		    	$p25cache = fopen("/tmp/P25Link.txt", "w");
		    	$num = fwrite($p25cache,$to);
		    	fclose($p25cache);
		    	return "TG".$to;
		    } else
		    if (strpos($logLine,"Starting P25Gateway")) {
		    	return "<div class='inactive-mode-cell'>Not Linked</div>";
		    } else
		    if (strpos($logLine,"Unlinking")) {
		    	return "<div class='inactive-mode-cell'>Not Linked</div>";
		    } else
		    if (strpos($logLine,"Unlinked")) {
		    	$num = 0;
		    	$p25cache = fopen("/tmp/P25Link.txt", "w");
		    	$num = fwrite($p25cache,"Not Linked");
		    	fclose($p25cache);
		    	return "<div class='inactive-mode-cell'>Not Linked</div>";
		    } else 
		    if(!file_exists("/tmp/P25Link.txt")) {
		    	return "<div class='inactive-mode-cell'>Not Linked</div>";
		    } else {
			$to = file_get_contents("/tmp/P25Link.txt");
			return "TG".$to;
		    }
		}
	    } else {
		return "<div class='inactive-mode-cell'>Service Not Started</div>";
	    }
	    break;
        }
}

function decodeAlias($logLine) {
  if (substr($logLine, 34, 2) !=="04")
    $tok1 = encode(substr($logLine, 40, 2));
  else
  $tok1 = "";
  $tok2 = encode(substr($logLine, 43, 2));
  $tok3 = encode(substr($logLine, 46, 2));
  $tok4 = encode(substr($logLine, 49, 2));
  $tok5 = encode(substr($logLine, 52, 2));
  $tok6 = encode(substr($logLine, 55, 2));
  $tok7 = encode(substr($logLine, 58, 2));
  return $tok1.$tok2.$tok3.$tok4.$tok5.$tok6.$tok7;
}

function tgLookup($mode, $target) {
    if (strlen($target) >= 2) {
	if (strpos($mode, 'DMR') !== false) {
	    if ($_SESSION['DMRGatewayConfigs']['DMR Network 4']['Enabled'] == "1" && strlen($target) >= 7 && substr( $target, 0, 1 ) === "5" && $_SESSION['DMRGatewayConfigs']['DMR Network 4']['Name'] == "TGIF_Network") {
		$target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_TGIF.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'");
		if (!empty($target_lookup)) {
		    if (strpos($_SERVER["PHP_SELF"], 'lh.php') || strpos($_SERVER["PHP_SELF"], 'localtx.php') !== false) {
		    	$target = "TG $target <span style='float:right;' class='noMob'>($target_lookup)</span>";
		    } else {
		    	$target = "TG $target <span class='noMob'>($target_lookup)</span>";
		    }
		} else {
		    $target = "TG $target";
		}
	    } else if ($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Enabled'] == "1" && strlen($target) >= 6 && substr( $target, 0, 1 ) === "8" && $_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'] == "DMR+_IPSC2-FreeSTAR") {
		$target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_FreeStarIPSC.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'");
		if (!empty($target_lookup)) {
		    if (strpos($_SERVER["PHP_SELF"], 'lh.php') || strpos($_SERVER["PHP_SELF"], 'localtx.php') !== false) {
		    	$target = "TG $target <span style='float:right;' class='noMob'>($target_lookup)</span>";
		    } else {
		    	$target = "TG $target <span class='noMob'>($target_lookup)</span>";
		    }
		} else {
		    $target = "TG $target";
		}
	    } else if ($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Enabled'] == "1" && strlen($target) >= 6 && substr( $target, 0, 1 ) === "4" && startsWith($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Name'], "SystemX")) {
		$target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_SystemX.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'");
		if (!empty($target_lookup)) {
		    if (strpos($_SERVER["PHP_SELF"], 'lh.php') || strpos($_SERVER["PHP_SELF"], 'localtx.php') !== false) {
		    	$target = "TG $target <span style='float:right;' class='noMob'>($target_lookup)</span>";
		    } else {
		    	$target = "TG $target <span class='noMob'>($target_lookup)</span>";
		    }
		} else {
		    $target = "TG $target";
		}
	    } else if ($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Enabled'] == "1" && strlen($target) >= 6 && substr( $target, 0, 1 ) === "8" && startsWith($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'], "FreeDMR")) {
		$target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_FreeDMR_.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'");
		if (!empty($target_lookup)) {
		    if (strpos($_SERVER["PHP_SELF"], 'lh.php') || strpos($_SERVER["PHP_SELF"], 'localtx.php') !== false) {
		    	$target = "TG $target <span style='float:right;' class='noMob'>($target_lookup)</span>";
		    } else {
		    	$target = "TG $target <span class='noMob'>($target_lookup)</span>";
		    }
		} else {
		    $target = "TG $target";
		}
	    } else if ($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Enabled'] == "1" && strlen($target) >= 6 && substr( $target, 0, 1 ) === "8" && startsWith($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'], "DMR+_IPSC2")) {
		$target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_DMRp.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'");
		if (!empty($target_lookup)) {
		    if (strpos($_SERVER["PHP_SELF"], 'lh.php') || strpos($_SERVER["PHP_SELF"], 'localtx.php') !== false) {
		    	$target = "TG $target <span style='float:right;' class='noMob'>($target_lookup)</span>";
		    } else {
		    	$target = "TG $target <span class='noMob'>($target_lookup)</span>";
		    }
		} else {
		    $target = "TG $target";
		}
	    } else if (strlen($target) >= 6 && substr( $target, 0, 1 ) === "7" && $_SESSION['DMRGatewayConfigs']['DMR Network 3']['Enabled'] == "1") {                             
		if ($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Name'] == "DMR2YSF_Cross-over") {                                                                             
		    if (strpos($_SERVER["PHP_SELF"], 'lh.php') || strpos($_SERVER["PHP_SELF"], 'localtx.php') !== false) {
		    	$target = "TG $target <span style='float:right;' class='noMob'>(DMR2YSF)</span>";
		    } else {
		    	$target = "TG $target <span class='noMob'>(DMR2YSF)</span>";
		    }
		} else if ($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Name'] == "DMR2NXDN_Cross-over") {                                                                     
		    if (strpos($_SERVER["PHP_SELF"], 'lh.php') || strpos($_SERVER["PHP_SELF"], 'localtx.php') !== false) {
		    	$target = "TG $target <span style='float:right;' class='noMob'>(DMR2NXDN)</span>";
		    } else {
		    	$target = "TG $target <span class='noMob'>(DMR2NXDN)</span>";
		    }
		}
	    } else if ($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Enabled'] == "1" && startsWith($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Name'], "BM")) {
		$target_lookup = exec("grep -w \"$target\" /usr/local/etc/groups.txt | awk -F, '{print $1}' | head -1 | tr -d '\"'");
		if (!empty($target_lookup)) {
		    $target = $target_lookup;
		    $stupid_bm = ['/ - 10 Minute Limit/', '/ NOT A CALL CHANNEL/', '/ NO NETS(.*?)/', '/!/'];
		    $target = preg_replace($stupid_bm, "", $target); // strip comments from BM TG names..
		    if (strpos($_SERVER["PHP_SELF"], 'lh.php') || strpos($_SERVER["PHP_SELF"], 'localtx.php') !== false) {
		    	$target = str_replace(": ", " <span style='float:right;' class='noMob'>(", $target.")</span>");
		    } else {
		    	$target = str_replace(": ", " <span class='noMob'>(", $target.")</span>");
		    }
		    $target = "TG $target";
		} else {
		    $target = "TG $target";
		}
	    } else { // nothing found in any DMR list/network - return TG # only
		$target = "TG $target";
	    }
	} else if (strpos($mode, 'NXDN') !== false) {
	    $target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_NXDN.txt | awk -F';' '{print $2}'");
	    if (!empty($target_lookup)) {
		if (strpos($_SERVER["PHP_SELF"], 'lh.php') || strpos($_SERVER["PHP_SELF"], 'localtx.php') !== false) {
	    	    $target = "TG $target <span style='float:right;' class='noMob'>($target_lookup)</span>";
		} else {
	    	    $target = "TG $target <span class='noMob'>($target_lookup)</span>";
		}
	    } else {
		$target = "TG $target";
	    }
	} else if (strpos($mode, 'P25') !== false) {
	    $target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_P25.txt | awk -F';' '{print $2}'");
	    if (!empty($target_lookup)) {
		if (strpos($_SERVER["PHP_SELF"], 'lh.php') || strpos($_SERVER["PHP_SELF"], 'localtx.php') !== false) {
	    	    $target = "TG $target <span style='float:right;' class='noMob'>($target_lookup)</span>";
		} else {
	    	    $target = "TG $target <span class='noMob'>($target_lookup)</span>";
		}
	    } else {
		$target = "TG $target";
	    }
	} else { // nothing found in any mode - return TG # only
	    $target = $target;
	}
    } else {  // ID is <= 2 digits; no lookups - return TG # only
	$modeArray = array('DMR', 'NXDN', 'P25');
	if (strpos($mode, $modeArray[0]) !== false) {
	    $target = "TG $target";
    	} else {
	    $target = $target;
	}
    }
    return $target;
}

function getName($callsign) {
    ini_set('default_socket_timeout', 2);
    $name = array();
    $TMP_CALL_NAME = "/tmp/Callsign_Name.txt";
    $cl_api = "https://callook.info/$callsign/json";
    // check cache first...
    if (file_exists($TMP_CALL_NAME)) {
        if (strpos($callsign,"-")) {
            $callsign = substr($callsign,0,strpos($callsign,"-"));
        }
        $delimiter =" ";
        $contents = exec("egrep -m1 '".$callsign.$delimiter."' ".$TMP_CALL_NAME, $output);
        if (count($output) !== 0) {
            $name = substr($output[0], strpos($output[0],$delimiter));
            $name = substr($name, strpos($name,$delimiter));
            return $name;
        }
    }
    // not in cache...perform lookup, then cache...
    $fp = fsockopen('ssl://callook.info', '443', $errno, $errstr, 2);
    if ($fp) {
        $options = array(
                'http'=>array(
                    'method'=>"GET",
                    'header'=>"Accept-language: en\r\n" .
                               "User-Agent: W0CHP-PiStar-Dash; Name Lookup Function - <https://w0chp.net/w0chp-pistar-dash/>\r\n"
                )
        );
        $context = stream_context_create($options);
        $api_data = file_get_contents($cl_api, false, $context);
        $result = json_decode($api_data);
        if ($result->status == 'INVALID') { // Check if in NOT in API DB
            $name = "---"; // placeholder for non US/FCC callsigns
        } else {
            $name_full = $result->name; // grab name value from json
            $name_array = explode(' ', $name_full);
            foreach($name_array as $key => $value) {
                $name = implode (" ", $name_array);
            }
            $name = ucwords(strtolower($name)); // name result is all UPPER. Convert to Camel Case.
        }
	// now actaully write the result/non-result to cache..
        $fp = fopen($TMP_CALL_NAME .'.TMP', 'a');
        $TMP_STRING = $callsign .' '  .$name;
        fwrite($fp, $TMP_STRING.PHP_EOL);
        fclose($fp);
        exec('sort ' .$TMP_CALL_NAME.'.TMP' .' ' .$TMP_CALL_NAME .' | uniq  > ' .$TMP_CALL_NAME);
    } else {
        return _("Unable to connect to Call Sign Lookup API");
    }
}

//Some basic inits
if (!in_array($_SERVER["PHP_SELF"],array('/mmdvmhost/bm_links.php','/mmdvmhost/bm_manager.php'),true)) {
    $logLinesMMDVM = getMMDVMLog();
    $reverseLogLinesMMDVM = $logLinesMMDVM;
    array_multisort($reverseLogLinesMMDVM,SORT_DESC);
    $lastHeard = getLastHeard($reverseLogLinesMMDVM);
    
    // Only need these in repeaterinfo.php
    if (strpos($_SERVER["PHP_SELF"], 'repeaterinfo.php') !== false || strpos($_SERVER["PHP_SELF"], 'index.php') !== false) {
	$logLinesYSFGateway = getYSFGatewayLog();
	$reverseLogLinesYSFGateway = $logLinesYSFGateway;
	array_multisort($reverseLogLinesYSFGateway,SORT_DESC);
	$logLinesP25Gateway = getP25GatewayLog();
	$logLinesNXDNGateway = getNXDNGatewayLog();
	$logLinesM17Gateway = getM17GatewayLog();
	$reverseLogLinesM17Gateway = $logLinesM17Gateway;
	array_multisort($reverseLogLinesM17Gateway,SORT_DESC);
    }
    
    // Only need these in index.php
    if (strpos($_SERVER["PHP_SELF"], 'index.php') !== false || strpos($_SERVER["PHP_SELF"], 'pages.php') !== false) {
	// Will separate personal and global messages only in Admin page, if MY_RIC is defined in dapnetapi.key.
        $origin = (isset($_GET['origin']) ? $_GET['origin'] : (isset($myOrigin) ? $myOrigin : "unknown"));
	$logLinesDAPNETGateway = getDAPNETGatewayLog(($origin == "admin" ? (isset($_SESSION['DAPNETAPIKeyConfigs']) ? getConfigItem("DAPNETAPI", "MY_RIC", $_SESSION['DAPNETAPIKeyConfigs']) : null) : null));
    }
}
?>
