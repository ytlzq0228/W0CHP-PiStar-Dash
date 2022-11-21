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
    
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

if (isset($_SESSION['CSSConfigs']['Background']['TableRowBgEvenColor'])) {
    $tableRowEvenBg = $_SESSION['CSSConfigs']['Background']['TableRowBgEvenColor'];
    $tableRowOddBg = $_SESSION['CSSConfigs']['Background']['TableRowBgOddColor'];
} else {
    $tableRowEvenBg = "inherit";
    $tableRowOddBg = "inherit";
}

// honor time format settings
if (constant("TIME_FORMAT") == "24") {
    $local_time = 'H:i:s';
} else {
    $local_time = 'h:i:s A';
}

// BM uses stupid comments in TG names. Delete them...
function StripStupidComments($target) {
    $stupid_bm = ['/ - 10 Minute Limit/', '/ NOT A CALL CHANNEL/', '/ NO NETS(.*?)/', '/!/'];
    $clean = preg_replace($stupid_bm, "", $target);
    return $clean;
}

// Check if DMR is Enabled
$testMMDVModeDMR = getConfigItem("DMR", "Enable", $_SESSION['MMDVMHostConfigs']);

if ( $testMMDVModeDMR == 1 ) {
    $bmEnabled = true;

 //setup BM API Key
  $bmAPIkeyFile = '/etc/bmapi.key';
  if (file_exists($bmAPIkeyFile) && fopen($bmAPIkeyFile,'r')) { $configBMapi = parse_ini_file($bmAPIkeyFile, true);
    $bmAPIkey = $configBMapi['key']['apikey'];
    // Check the BM API Key
    if ( strlen($bmAPIkey) <= 20 ) { unset($bmAPIkey); }
    if ( strlen($bmAPIkey) >= 200 ) { $bmAPIkeyV2 = $bmAPIkey; unset($bmAPIkey); }
  }

    // Get the current DMR Master from the config
    $dmrMasterHost = getConfigItem("DMR Network", "Address", $_SESSION['MMDVMHostConfigs']);
    if ( $dmrMasterHost == '127.0.0.1' ) {
	$dmrMasterHost = $_SESSION['DMRGatewayConfigs']['DMR Network 1']['Address'];
	$bmEnabled = ($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Enabled'] != "0" ? true : false);
	if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Id'])) { $dmrID = $_SESSION['DMRGatewayConfigs']['DMR Network 1']['Id']; }
    }
    else if (getConfigItem("DMR", "Id", $_SESSION['MMDVMHostConfigs'])) {
	$dmrID = getConfigItem("DMR", "Id", $_SESSION['MMDVMHostConfigs']);
    }
    else {
	$dmrID = getConfigItem("General", "Id", $_SESSION['MMDVMHostConfigs']);
    }
    
    // Store the DMR Master IP, we will need this for the JSON lookup
    $dmrMasterHostIP = $dmrMasterHost;
    
    // Make sure the master is a BrandMeister Master
    if (($dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r")) != FALSE) {
	while (!feof($dmrMasterFile)) {
            $dmrMasterLine = fgets($dmrMasterFile);
            $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
            if ((strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
		if ($dmrMasterHost == $dmrMasterHostF[2]) { $dmrMasterHost = str_replace('_', ' ', $dmrMasterHostF[0]); }
            }
	}
	fclose($dmrMasterFile);
    }

    if ((substr($dmrMasterHost, 0, 3) == "BM ") && ($bmEnabled == true) && isset($_SESSION['BMAPIKey'])) { 
    $bmAPIkey = $_SESSION['BMAPIKey'];
	// Use BM API to get information about current TGs
	$jsonContext = stream_context_create(array('http'=>array('timeout' => 2, 'header' => 'User-Agent: Pi-Star '.$_SESSION['PiStarRelease']['Pi-Star']['Version'].' W0CHP-PiStar-Dash for '.$dmrID) )); // Add Timout and User Agent to include DMRID
    if (isset($bmAPIkeyV2)) {
       $json = json_decode(@file_get_contents("https://api.brandmeister.network/v2/device/$dmrID/profile", true, $jsonContext));
     } else {
       $json = json_decode(@file_get_contents("https://api.brandmeister.network/v1.0/repeater/?action=PROFILE&q=$dmrID", true, $jsonContext));
     }	
	// Set some Variable
	$bmStaticTGList = "";
	$bmDynamicTGList = "";
        $bmDynanicTGname = "";
        $bmDynanicTGexpire = "";
	// Pull the information from JSON
	if (isset($json->staticSubscriptions)) { $bmStaticTGListJson = $json->staticSubscriptions;
            foreach($bmStaticTGListJson as $staticTG) {

                if (getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) && $staticTG->slot == "1") {
                    $bmStaticTGname = exec("grep -w \"$staticTG->talkgroup\" /usr/local/etc/BM_TGs.json | cut -d\":\" -f2- | tr -cd \"'[:alnum:]\/ -\"");
                    $bmStaticTGList .= "<tr><td align='left' style='padding-left: 8px;'>TG ".$staticTG->talkgroup."</td><td align='left' style='padding-left: 8px;'>".StripStupidComments($bmStaticTGname)."</td><td align='left' style='padding-left: 8px;'>".$staticTG->slot."</td></tr>";
                }
                else if (getConfigItem("DMR Network", "Slot2", $_SESSION['MMDVMHostConfigs']) && $staticTG->slot == "2") {
                    $bmStaticTGname = exec("grep -w \"$staticTG->talkgroup\" /usr/local/etc/BM_TGs.json | cut -d\":\" -f2- | tr -cd \"'[:alnum:]\/ -\"");
                    $bmStaticTGList .= "<tr><td align='left' style='padding-left: 8px;'>TG ".$staticTG->talkgroup."</td><td align='left' style='padding-left: 8px;'>".StripStupidComments($bmStaticTGname)."</td><td align='left' style='padding-left: 8px;'>".$staticTG->slot."</td></tr>";
                }
                else if (getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) == "0" && getConfigItem("DMR Network", "Slot2", $_SESSION['MMDVMHostConfigs']) && $staticTG->slot == "0") {
                    $bmStaticTGname = exec("grep -w \"$staticTG->talkgroup\" /usr/local/etc/BM_TGs.json | cut -d\":\" -f2- | tr -cd \"'[:alnum:]\/ -\"");
                    $bmStaticTGList .= "<tr><td align='left' style='padding-left: 8px;'>TG ".$staticTG->talkgroup."</td><td align='left' style='padding-left: 8px;'>".StripStupidComments($bmStaticTGname)."</td><td align='left' style='padding-left: 8px;'>2</td></tr>";
                }
            }
            $bmStaticTGList = wordwrap($bmStaticTGList, 135, "\n");
            if (preg_match('/TG/', $bmStaticTGList) == false) { $bmStaticTGList = "<tr><td colspan='3'>No Talkgroups Linked</td></tr>"; }
        }
	else { $bmStaticTGList = "<tr><td colspan='3'>No Talkgroups Linked</td></tr>"; }
	if (isset($json->dynamicSubscriptions)) { $bmDynamicTGListJson = $json->dynamicSubscriptions;
            foreach($bmDynamicTGListJson as $dynamicTG) {
                if (getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) && $dynamicTG->slot == "1") {
		    $now = new DateTime();
		    $then = new DateTime( "@" . $dynamicTG->timeout);
		    $diff = $then->diff($now);
		    $bmDynanicTGexpire = $diff->format('%i:%S mins');
		    $bmDynamicTGname = exec("grep -w \"$dynamicTG->talkgroup\" /usr/local/etc/BM_TGs.json | cut -d\":\" -f2- | tr -cd \"'[:alnum:]\/ -\"");
		    $bmDynamicTGList .= "<tr><td align='left' style='padding-left: 8px;'>TG ".$dynamicTG->talkgroup."</td><td align='left' style='padding-left: 8px;'>".StripStupidComments($bmDynamicTGname)."</td><td align='left' style='padding-left: 8px;'>".$dynamicTG->slot."</td><td align='left' style='padding-left: 8px;' id='tgTimeout'>".date("$local_time", substr($dynamicTG->timeout, 0, 10))." ".date('T'). " ($bmDynanicTGexpire remaining)</td></tr>";
                }
                else if (getConfigItem("DMR Network", "Slot2", $_SESSION['MMDVMHostConfigs']) && $dynamicTG->slot == "2") {
		    $now = new DateTime();
		    $then = new DateTime( "@" . $dynamicTG->timeout);
		    $diff = $then->diff($now);
		    $bmDynanicTGexpire = $diff->format('%i:%S mins');
		    $bmDynamicTGname = exec("grep -w \"$dynamicTG->talkgroup\" /usr/local/etc/BM_TGs.json | cut -d\":\" -f2- | tr -cd \"'[:alnum:]\/ -\"");
		    $bmDynamicTGList .= "<tr><td align='left' style='padding-left: 8px;'>TG ".$dynamicTG->talkgroup."</td><td align='left' style='padding-left: 8px;'>".StripStupidComments($bmDynamicTGname)."</td><td align='left' style='padding-left: 8px;'>".$dynamicTG->slot."</td><td align='left' style='padding-left: 8px;'>".date("$local_time", substr($dynamicTG->timeout, 0, 10))." ".date('T')." ($bmDynanicTGexpire remaining)</td></tr>";
                }
                else if (getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) == "0" && getConfigItem("DMR Network", "Slot2", $_SESSION['MMDVMHostConfigs']) && $dynamicTG->slot == "0") {
                    $bmDynamicTGname = exec("grep -w \"$dynamicTG->talkgroup\" /usr/local/etc/BM_TGs.json | cut -d\":\" -f2- | tr -cd \"'[:alnum:]\/ -\"");
	            $bmDynamicTGList .= "<tr><td align='left' style='padding-left: 8px;'>TG ".$dynamicTG->talkgroup."</td><td align='left' style='padding-left: 8px;'>".StripStupidComments($bmDynamicTGname)."</td><td align='left' style='padding-left: 8px;'>2</td><td align='left' style='padding-left: 8px;'>".date("$local_time", substr($dynamicTG->timeout, 0, 10))." ".date('T')."</td></tr>";
                }
            }
            $bmDynamicTGList = wordwrap($bmDynamicTGList, 135, "\n");
            if (preg_match('/TG/', $bmDynamicTGList) == false) { $bmDynamicTGList = "<tr><td colspan='4'>No Talkgroups Linked</td></tr>"; }
        } else { $bmDynamicTGList = "<tr><td colspan='4'>No Talkgroups Linked</td></tr>"; }
	    echo '<div style="text-align:left;font-weight:bold;">Active BrandMeister Connections</div>
  <table id="bmLinks">
    <tr style="font-size:1.1em;">
      <th><a class=tooltip href="#">Static Talkgroups<span><b>Statically linked talkgroups</b></span></a></th>
      <th><a class=tooltip href="#">Dynamic Talkgroups<span><b>Dynamically linked talkgroups</b></span></a></th>
    </tr>'."\n";
	echo '    <tr>'."\n";
	echo '     <td align="left" style="background:'.$tableRowOddBg.';vertical-align:top;padding:0;margin:0;border:none;">';
	echo "     <table style='padding:0;margin:0;border:none;'>";
	echo "     <tr style='padding:0;margin:0;border:none;font-size:0.85em;'>";
	echo "       <th align='left' style='padding-left: 8px;'>Talkgroup #</th>";
	echo "       <th align='left' style='padding-left: 8px;'>Name</th>";
	echo "       <th align='left' style='padding-left: 8px;'>Timeslot</th>";
	echo "     </tr>";
	echo "     $bmStaticTGList";
	echo "     </table>";
	echo '     </td>';
	echo '     <td align="left" style="background:'.$tableRowOddBg.';vertical-align:top;padding:0;margin:0;border:none;">';
	echo "     <table style='padding:0;margin:0;border:none;'>";
	echo "     <tr style='padding:0;margin:0;border:none;font-size:0.85em;'>";
	echo "       <th align='left' style='padding-left: 8px;'>Talkgroup #</th>";
	echo "       <th align='left' style='padding-left: 8px;'>Name</th>";
	echo "       <th align='left' style='padding-left: 8px;'>Timeslot</th>";
	echo "       <th align='left' style='padding-left: 8px;'>Idle Timeout</th>";
	echo "     </tr>";
	echo "     $bmDynamicTGList";
	echo "     </table>";
	echo '     </td>';
	echo '    </tr>'."\n";
	echo '    <tr>'."\n";
	echo '      <td colspan="3" style="white-space:normal;padding: 3px;background:'.$tableRowEvenBg.'">Your Hotspot/Repeater ID: <a href="https://brandmeister.network/?page=hotspot&amp;id='.$dmrID.'" target="_new" title="Click to view your hotspot info on BrandMeister">'.$dmrID.'</a> | Connected To: '.$dmrMasterHost.' |  <a href="https://w0chp.net/brandmeister-talkgroups/" target="_blank">List of All BrandMeister Talkgroups</a></td>'."\n";
	echo '    </tr>'."\n";
	echo '  </table>'."\n";
    }
}
?>
