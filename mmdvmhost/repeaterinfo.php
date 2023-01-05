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
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code
require_once($_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php');

if (isset($_SESSION['CSSConfigs']['Background']['TableRowBgEvenColor'])) {
    $tableRowEvenBg = $_SESSION['CSSConfigs']['Background']['TableRowBgEvenColor'];
} else {
    $tableRowEvenBg = "inherit";
}
if (isset($_SESSION['CSSConfigs']['ExtraSettings']['TableBorderColor'])) {
    $tableBorderColor = $_SESSION['CSSConfigs']['ExtraSettings']['TableBorderColor'];
} else {
    $tableBorderColor = "inherit";
}

function FillConnectionHosts(&$destArray, $remoteEnabled, $remotePort) {
    if (($remoteEnabled == 1) && ($remotePort != 0)) {
	$remoteOutput = null;
	$remoteRetval = null;
	exec('cd /var/log/pi-star; /usr/local/bin/RemoteCommand '.$remotePort.' hosts', $remoteOutput, $remoteRetval);
	if (($remoteRetval == 0) && (count($remoteOutput) >= 2)) {
	    $expOutput = preg_split('/"[^"]*"(*SKIP)(*F)|\x20/', $remoteOutput[1]);
	    foreach ($expOutput as $entry) {
		$keysValues = explode(":", $entry);
		$destArray[$keysValues[0]] = $keysValues[1];
	    }
	}
    }
}

function FillConnectionStatus(&$destArray, $remoteEnabled, $remotePort) {
    if (($remoteEnabled == 1) && ($remotePort != 0)) {
	$remoteOutput = null;
	$remoteRetval = null;
	exec('cd /var/log/pi-star; /usr/local/bin/RemoteCommand '.$remotePort.' status', $remoteOutput, $remoteRetval);
	if (($remoteRetval == 0) && (count($remoteOutput) >= 2)) {
	    $tok = strtok($remoteOutput[1], " \n\t");
	    while ($tok !== false) {
		$keysValues = explode(":", $tok);
		$destArray[$keysValues[0]] = $keysValues[1];
		$tok = strtok(" \n\t");
	    }
	}
    }
}

function GetActiveConnectionStyle($masterStates, $key) {
    global $tableRowEvenBg;
    if (count($masterStates)) {
	if (isset($masterStates[$key])) {
	    if ($key == "xlx" && file_exists("/etc/.XLX_paused")) { // xlx dmr manager logic
		return "class=\"paused-mode-cell\" title=\"User Unlinked\"";
	    } else if (($masterStates[$key] == "n/a") || ($masterStates[$key] == "disc")) {
		return "class=\"error-state-cell\"";
	    }
	}
    }
    return "style='background: $tableRowEvenBg;'";
}

//
// Grab networks status from remote commands
//
$remoteMMDVMResults = [];
$remoteDMRgwResults = [];
$remoteYSFGResults = [];
$remoteP25GResults = [];
$remoteNXDNGResults = [];
$remoteM17GWResults = [];

if (isProcessRunning("MMDVMHost")) {
    $cfgItemEnabled = getConfigItem("Remote Control", "Enable", $_SESSION['MMDVMHostConfigs']);
    $cfgItemPort = getConfigItem("Remote Control", "Port", $_SESSION['MMDVMHostConfigs']);
    FillConnectionStatus($remoteMMDVMResults, (isset($cfgItemEnabled) ? $cfgItemEnabled : 0), (isset($cfgItemPort) ? $cfgItemPort : 0));
}

if (isProcessRunning("DMRGateway")) {
    $remoteCommandEnabled = (isset($_SESSION['DMRGatewayConfigs']['Remote Control']) ? $_SESSION['DMRGatewayConfigs']['Remote Control']['Enable'] : 0);
    $remoteCommandPort = (isset($_SESSION['DMRGatewayConfigs']['Remote Control']) ? $_SESSION['DMRGatewayConfigs']['Remote Control']['Port'] : 0);
    FillConnectionStatus($remoteDMRgwResults, $remoteCommandEnabled, $remoteCommandPort);
}

if (isProcessRunning("YSFGateway")) {
    $remoteCommandEnabled = (isset($_SESSION['YSFGatewayConfigs']['Remote Commands']) ? $_SESSION['YSFGatewayConfigs']['Remote Commands']['Enable'] : 0);
    $remoteCommandPort = (isset($_SESSION['YSFGatewayConfigs']['Remote Commands']) ? $_SESSION['YSFGatewayConfigs']['Remote Commands']['Port'] : 0);
    FillConnectionStatus($remoteYSFGResults, $remoteCommandEnabled, $remoteCommandPort);
}

if (isProcessRunning("P25Gateway")) {
    $remoteCommandEnabled = (isset($_SESSION['P25GatewayConfigs']['Remote Commands']) ? $_SESSION['P25GatewayConfigs']['Remote Commands']['Enable'] : 0);
    $remoteCommandPort = (isset($_SESSION['P25GatewayConfigs']['Remote Commands']) ? $_SESSION['P25GatewayConfigs']['Remote Commands']['Port'] : 0);
    FillConnectionStatus($remoteP25GResults, $remoteCommandEnabled, $remoteCommandPort);
}

if (isProcessRunning("NXDNGateway")) {
    $remoteCommandEnabled = (isset($_SESSION['NXDNGatewayConfigs']['Remote Commands']) ? $_SESSION['NXDNGatewayConfigs']['Remote Commands']['Enable'] : 0);
    $remoteCommandPort = (isset($_SESSION['NXDNGatewayConfigs']['Remote Commands']) ? $_SESSION['NXDNGatewayConfigs']['Remote Commands']['Port'] : 0);
    FillConnectionStatus($remoteNXDNGResults, $remoteCommandEnabled, $remoteCommandPort);
}

if (isProcessRunning("M17Gateway")) {
    $remoteCommandEnabled = (isset($_SESSION['M17GatewayConfigs']['Remote Commands']) ? $_SESSION['M17GatewayConfigs']['Remote Commands']['Enable'] : 0);
    $remoteCommandPort = (isset($_SESSION['M17GatewayConfigs']['Remote Commands']) ? $_SESSION['M17GatewayConfigs']['Remote Commands']['Port'] : 0);
    FillConnectionStatus($remoteM17GResults, $remoteCommandEnabled, $remoteCommandPort);
}

// get number of DMR Masters configged for DMRGw:
$numDMRmasters = exec('cd /var/log/pi-star ; /usr/local/bin/RemoteCommand 7643 status | grep -o "conn" | wc -l');
?>

<div class="mode_flex" id="rptInfoTable">
  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableHead"><?php echo $lang['modes_enabled'];?></div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
	<?php if (isPaused("D-Star")) { echo '<div class="paused-mode-cell" title="Mode Paused">D-Star</div>'; } else {showMode("D-Star", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
    </div>
    <div class="mode_flex column">
      <div class="divTableCell">
	<?php if (isPaused("DMR")) { echo '<div class="paused-mode-cell" title="Mode Paused">DMR</div>'; } else { showMode("DMR", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
	<?php if (isPaused("YSF")) { echo '<div class="paused-mode-cell" title="Mode Paused">YSF</div>'; } else { showMode("System Fusion", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
    </div>
    <div class="mode_flex column">
      <div class="divTableCell">
	<?php if (isPaused("P25")) { echo '<div class="paused-mode-cell" title="Mode Paused">P25</div>'; } else { showMode("P25", $_SESSION['MMDVMHostConfigs']); }?>
     </div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
	    <?php if (isPaused("M17")) { echo '<div class="paused-mode-cell" title="Mode Paused">M17</div>'; } else { showMode("M17", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
    </div>
    <div class="mode_flex column">
      <div class="divTableCell">
	<?php if (isPaused("NXDN")) { echo '<div class="paused-mode-cell" title="Mode Paused">NXDN</div>'; } else { showMode("NXDN", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
	<?php showMode("DMR X-Mode", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="mode_flex column">
      <div class="divTableCell">
	    <?php showMode("YSF X-Mode", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
	<?php if (isPaused("POCSAG")) { echo '<div class="paused-mode-cell" title="Mode Paused">POCSAG</div>'; } else { showMode("POCSAG", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
    </div>
  </div>
</div>

<br />

<div class="mode_flex">
  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableHead"><?php echo $lang['net_status'];?></div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("D-Star Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("DMR Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("System Fusion Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php if (getServiceEnabled('/etc/dgidgateway') == 1 && (isPaused("YSF"))) { echo '<div class="paused-mode-cell" title="YSF Mode Paused">DG-ID Net</div>'; } else { showMode("DG-ID Network", $_SESSION['DGIdGatewayConfigs']); } ?>
      </div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("YSF2DMR Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("YSF2NXDN Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("YSF2P25 Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("P25 Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("NXDN Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("M17 Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("DMR2NXDN Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("DMR2YSF Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
  </div>

  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php showMode("POCSAG Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="mode_flex column">
      <div class="divTableCell">
        <?php if (isPaused("APRS")) { echo '<div class="paused-mode-cell" title="Service Paused">APRS Net</div>'; } else { showMode("APRS Network", $_SESSION['APRSGatewayConfigs']); }?>
      </div>
    </div>
  </div>
</div>

<br />

<?php
$testMMDVModeDSTAR = getConfigItem("D-Star", "Enable", $_SESSION['MMDVMHostConfigs']);
if ( $testMMDVModeDSTAR == 1 || isPaused("D-Star") ) { //Hide the D-Star Reflector information when D-Star Network not enabled.
    $linkedTo = getActualLink($reverseLogLinesMMDVM, "D-Star");
?>
<div class="divTable">
  <div class="divTableHead"><?php echo $lang['dstar_repeater'];?></div>
  <div class="divTableBody">
    <div class="divTableRow center">
      <div class="divTableHeadCell">RPT1</div>
      <div class="divTableCell cell_content" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo str_replace(' ', '&nbsp;', $_SESSION['DStarRepeaterConfigs']['callsign']); ?></div>
    </div>    
    <div class="divTableRow center">
      <div class="divTableHeadCell">RPT2</div>
      <div class="divTableCell cell_content" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo str_replace(' ', '&nbsp;', $_SESSION['DStarRepeaterConfigs']['gateway']); ?></div>
    </div>	
  </div>
</div>
<div class="divTable">
  <div class="divTableHead"><?php echo $lang['dstar_net']; ?></div>
  <div class="divTableBody">
    <?php
        if (isPaused("D-Star")) {
                echo "<div class='divTableRow center'><div class='divTableCell cell_content' style=\"background: $tableRowEvenBg;\">Mode Paused</div></div>\n";                 
        } else {
                echo "<div class='divTableRow center'><div class='divTableCell cell_content' ".GetActiveConnectionStyle($remoteMMDVMResults, "dstar")." title=\"".$linkedTo."\">".$linkedTo."</div></div>\n";
        }
        if ($_SESSION['ircDDBConfigs']['aprsEnabled'] == 1) {
		if (substr($_SESSION['ircDDBConfigs']['aprsAddress'], 0, 18) == '127.0.0.1') {
	            echo "<div class='divTableRow center'><div class='divTableHeadCell'>APRS Host</div></div><div class='divTableRow center'><div class='divTableCell cell_content' title=\"Using APRSGateway\">APRSGateway</div></div>\n";
		} else {
	            echo "<div class='divTableRow center'><div class='divTableHeadCell'>APRS Host</div></div><div class='divTableRow center'><div class='divTableCell cell_content' style=\"background: $tableRowEvenBg;\">".substr($_SESSION['ircDDBConfigs']['aprsAddress'], 0, 18)."</div></div>\n";
		}
        }
        if ($_SESSION['ircDDBConfigs']['ircddbEnabled'] == 1) {
	    if (isProcessRunning("ircddbgatewayd")) {
	        echo "<div class='divTableRow center'><div class='divTableHeadCell'>ircDDB Host</div></div><div class='divTableRow center'><div class='divTableCell cell_content' style=\"background: $tableRowEvenBg;\">".substr($_SESSION['ircDDBConfigs']['ircddbHostname'], 0 ,18)."</div></div>\n";
	    } else {
	        echo "<div class='divTableRow center'><div class='divTableHeadCell'>ircDDB Host</div></div><div class='divTableRow center'><div class='divTableCell cell_content'><div class='inactive-mode-cell'>Service Not Started</div></div></div>\n";
	    }
        }
	?>
  </div>
</div>

<br />

<?php 

}
	$testMMDVModeDMR = getConfigItem("DMR", "Enable", $_SESSION['MMDVMHostConfigs']);
	if ( $testMMDVModeDMR == 1 || isPaused("DMR") ) { //Hide the DMR information when DMR mode not enabled.
		if (isPaused("DMR")) {
			$dmrMasterHost = "Mode Paused";
			$dmrMasterHostTooltip = $dmrMasterHost;
		} else {
	    $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	    $dmrMasterHost = getConfigItem("DMR Network", "Address", $_SESSION['MMDVMHostConfigs']);
	    $dmrMasterPort = getConfigItem("DMR Network", "Port", $_SESSION['MMDVMHostConfigs']);
	    if ($dmrMasterHost == '127.0.0.1') {
		if (isset($_SESSION['DMRGatewayConfigs']['XLX Network 1']['Address'])) {
		    $xlxMasterHost1 = $_SESSION['DMRGatewayConfigs']['XLX Network 1']['Address'];
		}
		else {
		    $xlxMasterHost1 = "";
		}
		$dmrMasterHost1 = $_SESSION['DMRGatewayConfigs']['DMR Network 1']['Address'];
		$dmrMasterHost2 = $_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'];
		if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'])) {
		    $dmrMasterHost2 = str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name']);
		}
		$dmrMasterHost3 = str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 3']['Name']);
		if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 4']['Name'])) {
		    $dmrMasterHost4 = str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 4']['Name']);
		}
		if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Name'])) {
		    $dmrMasterHost5 = str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 5']['Name']);
		}
		if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 6']['Name'])) {
		    $dmrMasterHost6 = str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 6']['Name']);
		}
        if (isset($configdmrgateway['DMR Network 6']['Name'])) {$dmrMasterHost6 = str_replace('_', ' ', $configdmrgateway['DMR Network 6']['Name']);}
		while (!feof($dmrMasterFile)) {
		    $dmrMasterLine = fgets($dmrMasterFile);
		    $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
		    if ((count($dmrMasterHostF) >= 2) && (strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
			if ((strpos($dmrMasterHostF[0], 'XLX_') === 0) && ($xlxMasterHost1 == $dmrMasterHostF[2])) {
			    $xlxMasterHost1 = str_replace('_', ' ', $dmrMasterHostF[0]);
			}
			if ((strpos($dmrMasterHostF[0], 'BM_') === 0) && ($dmrMasterHost1 == $dmrMasterHostF[2])) {
			    $dmrMasterHost1 = str_replace('_', ' ', $dmrMasterHostF[0]);
			}
			if ((strpos($dmrMasterHostF[0], 'DMR+_') === 0) && ($dmrMasterHost2 == $dmrMasterHostF[2])) {
			    $dmrMasterHost2 = str_replace('_', ' ', $dmrMasterHostF[0]);
			}
		    }
		}
		
		$xlxMasterHost1Tooltip = $xlxMasterHost1;
		$dmrMasterHost1Tooltip = $dmrMasterHost1;
		$dmrMasterHost2Tooltip = $dmrMasterHost2;
		$dmrMasterHost3Tooltip = $dmrMasterHost3;
		if (isset($dmrMasterHost4)) {
		    $dmrMasterHost4Tooltip = $dmrMasterHost4;
		}
		if (isset($dmrMasterHost5)) {
		    $dmrMasterHost5Tooltip = $dmrMasterHost5;
		}
        if (isset($dmrMasterHost6)) {
            $dmrMasterHost6Tooltip = $dmrMasterHost6;
        }
		if (strlen($xlxMasterHost1) > 20) {
		    $xlxMasterHost1 = substr($xlxMasterHost1, 0, 17) . '...';
		}
		if (strlen($dmrMasterHost1) > 20) {
		    $dmrMasterHost1 = substr($dmrMasterHost1, 0, 17) . '...';
		}
		if (strlen($dmrMasterHost2) > 20) {
		    $dmrMasterHost2 = substr($dmrMasterHost2, 0, 17) . '...';
		}
		if (strlen($dmrMasterHost3) > 20) {
		    $dmrMasterHost3 = substr($dmrMasterHost3, 0, 17) . '...';
		}
		if (isset($dmrMasterHost4)) {
		    if (strlen($dmrMasterHost4) > 20) {
			    $dmrMasterHost4 = substr($dmrMasterHost4, 0, 17) . '...';
		    }
		}
		if (isset($dmrMasterHost5)) {
		    if (strlen($dmrMasterHost5) > 20) {
			    $dmrMasterHost5 = substr($dmrMasterHost5, 0, 17) . '...';
		    }
		}
        if (isset($dmrMasterHost6)) { if (strlen($dmrMasterHost6) > 20) { $dmrMasterHost6 = substr($dmrMasterHost6, 0, 17) . '...'; } }
	    }
	    else {
		while (!feof($dmrMasterFile)) {
		    $dmrMasterLine = fgets($dmrMasterFile);
                    $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
		    if ((count($dmrMasterHostF) >= 4) && (strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
			if (($dmrMasterHost == $dmrMasterHostF[2]) && ($dmrMasterPort == $dmrMasterHostF[4])) {
			    $dmrMasterHost = str_replace('_', ' ', $dmrMasterHostF[0]);
			}
		    }
		}
		$dmrMasterHostTooltip = $dmrMasterHost;
		if (strlen($dmrMasterHost) > 20) {
		    $dmrMasterHost = substr($dmrMasterHost, 0, 15) . '...';
		}
	    }
	    fclose($dmrMasterFile);
	    }
	    ?>
<div class="divTable">
  <div class="divTableHead"><?php echo $lang['dmr_repeater'];?></div>
  <div class="divTableBody">
    <div class="divTableRow center">
      <div class="divTableHeadCell">TS1</div>
	    <?php
	    if (getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) == 1) {
		if (preg_match("/Not/",getActualLink($reverseLogLinesMMDVM, "DMR Slot 1"))) {
		    echo "<div class='divTableCell cell_content middle' title='Unlinked'><div style=\"background: $tableRowEvenBg;\">Unlinked</div></div>\n";
		} else {
		    $slot1Link = substr(getActualLink($reverseLogLinesMMDVM, "DMR Slot 1"), -11); 
		    if (file_exists("/etc/.TGNAMES")) {
			$slot1Link = str_replace("TG ", "", $slot1Link);
			if (strlen($slot1Link) >= 2) {
			    if (strlen($slot1Link) >= 7 && substr( $slot1Link, 0, 1 ) === "5" && $_SESSION['DMRGatewayConfigs']['DMR Network 4']['Enabled'] == "1") {
				$target_lookup = trim(exec("grep -w \"$slot1Link\" /usr/local/etc/TGList_TGIF.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'"));
				if (strlen($target_lookup) > 20) {
				    $target_lookup = substr($target_lookup, 0, 15) . '...';
				}
				$TGname = $target_lookup;
			    } else if (strlen($slot1Link) >= 6 && substr( $slot1Link, 0, 1 ) === "8" && $_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'] == "DMR+_IPSC2-FreeSTAR") {
				$target_lookup = exec("grep -w \"$slot1Link\" /usr/local/etc/TGList_FreeStarIPSC.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'");
				if (strlen($target_lookup) > 20) {
				    $target_lookup = substr($target_lookup, 0, 15) . '...';
				}
				$TGname = $target_lookup;
			    } else if (strlen($slot1Link) >= 6 && substr( $slot1Link, 0, 1 ) === "8" && startsWith($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'], "SystemX")) {
				$target_lookup = exec("grep -w \"$slot1Link\" /usr/local/etc/TGList_SystemX.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'"); 
				if (strlen($target_lookup) > 20) {
				    $target_lookup = substr($target_lookup, 0, 15) . '...';
				}
				$TGname = $target_lookup;
			    } else if (strlen($slot1Link) >= 6 && substr( $slot1Link, 0, 1 ) === "8" && startsWith($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'], "FreeDMR")) {
				$target_lookup = exec("grep -w \"$slot1Link\" /usr/local/etc/TGList_FreeDMR.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'"); 
				if (strlen($target_lookup) > 20) {
				    $target_lookup = substr($target_lookup, 0, 15) . '...';
				}
				$TGname = $target_lookup;
			    } else if (strlen($slot1Link) >= 6 && substr( $slot1Link, 0, 1 ) === "8" && startsWith($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'], "DMR+_IPSC2")) {
				$target_lookup = exec("grep -w \"$slot1Link\" /usr/local/etc/TGList_DMRp.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'"); 
				if (strlen($target_lookup) > 20) {
				    $target_lookup = substr($target_lookup, 0, 15) . '...';
				}
				$TGname = $target_lookup;
                            } else if (strlen($slot1Link) >= 6 && substr( $slot1Link, 0, 1 ) === "7" && $_SESSION['DMRGatewayConfigs']['DMR Network 3']['Enabled'] == "1") {
                                if ($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Name'] == "DMR2YSF_Cross-over") {
				    $target_lookup = "DMR2YSF";
				    $TGname = $target_lookup;
                                } else if ($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Name'] == "DMR2NXDN_Cross-over") {
				    $target_lookup = "DMR2NXDN";
				    $TGname = $target_lookup;
                                }
			    } else {
				$target_lookup = trim(exec("grep -w \"$slot1Link\" /usr/local/etc/groups.txt | awk -F, '{print $1}' | head -1 | tr -d '\"'"));
				$TGname = str_replace("$slot1Link: ", "", $target_lookup);
				$stupid_bm = ['/ - 10 Minute Limit/', '/ NOT A CALL CHANNEL/', '/ NO NETS(.*?)/', '/!/'];
				$TGname = preg_replace($stupid_bm, "", $TGname); // strip stupid fucking comments from BM admins in TG names. Idiots.
				if (strlen($TGname) > 20) {
				    $TGname = substr($TGname, 0, 15) . '...';
				}
			    }
			    if (!empty($target_lookup)) {
				echo "<div class='divTableCell cell_content middle active-mode-cell' title='Time Slot 1 Enabled: TG $slot1Link' style='border: .5px solid $tableBorderColor;'>TG $slot1Link<br /><small>($TGname)</small></div>\n";
			    } else {
				if ($slot1Link == "Unlinked") {
				    echo "<div class='divTableCell cell_content middle active-mode-cell' title='Time Slot 1 Enabled: $slot1Link' style='border: .5px solid $tableBorderColor;'>$slot1Link</div>\n";
				} else {
				    echo "<div class='divTableCell cell_content middle active-mode-cell' title='Time Slot 1 Enabled: TG $slot1Link' style='border: .5px solid $tableBorderColor;'>TG $slot1Link</div>\n";
				}
			    }
			} else {
			    echo "<div class='divTableCell cell_content middle active-mode-cell' title='Time Slot 1 Enabled: TG $slot1Link' style='border: .5px solid $tableBorderColor;'>TG $slot1Link</div>\n";
			}
		    } else {
			echo "<div class='divTableCell cell_content middle active-mode-cell' title='Time Slot 1 Enabled: $slot1Link' style='border: .5px solid $tableBorderColor;'>$slot1Link</div>\n";
		    }
		}
	    } else {
		echo "<div class='divTableCell cell_content middle title='Time Slot 1 disabled' style='border: .5px solid $tableBorderColor;'>Disabled</div>\n";
	    }
	    ?>
    </div>
    <div class="divTableRow center">
      <div class="divTableHeadCell">TS2</div>
           <?php
	    if (getConfigItem("DMR Network", "Slot2", $_SESSION['MMDVMHostConfigs']) == 1) {
                if (preg_match("/Not/",getActualLink($reverseLogLinesMMDVM, "DMR Slot 2"))) {
		    echo "<div class='divTableCell cell_content middle' title='Unlinked'><div style=\"background: $tableRowEvenBg;\">Unlinked</div></div>\n";
		} else {
		    $slot2Link = substr(getActualLink($reverseLogLinesMMDVM, "DMR Slot 2"), -11); 
		    if (file_exists("/etc/.TGNAMES")) {
			$slot2Link = str_replace("TG ", "", $slot2Link);
			if (strlen($slot2Link) >= 2) {
			    if (strlen($slot2Link) >= 7 && substr( $slot2Link, 0, 1 ) === "5" && $_SESSION['DMRGatewayConfigs']['DMR Network 4']['Enabled'] == "1") {
				$target_lookup = trim(exec("grep -w \"$slot2Link\" /usr/local/etc/TGList_TGIF.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'"));
				if (strlen($target_lookup) > 20) {
				    $target_lookup = substr($target_lookup, 0, 15) . '...';
				}
				$TGname = $target_lookup;
			    } else if (strlen($slot2Link) >= 6 && substr( $slot2Link, 0, 1 ) === "8" && $_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'] == "DMR+_IPSC2-FreeSTAR") {
				$target_lookup = exec("grep -w \"$slot2Link\" /usr/local/etc/TGList_FreeStarIPSC.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'");
				if (strlen($target_lookup) > 20) {
				    $target_lookup = substr($target_lookup, 0, 15) . '...';
				}
				$TGname = $target_lookup;
			    } else if (strlen($slot2Link) >= 6 && substr( $slot2Link, 0, 1 ) === "8" && startsWith($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'], "SystemX")) {
				$target_lookup = exec("grep -w \"$slot2Link\" /usr/local/etc/TGList_SystemX.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'"); 
				if (strlen($target_lookup) > 20) {
				    $target_lookup = substr($target_lookup, 0, 15) . '...';
				}
				$TGname = $target_lookup;
			    } else if (strlen($slot2Link) >= 6 && substr( $slot2Link, 0, 1 ) === "8" && startsWith($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'], "FreeDMR")) {
				$target_lookup = exec("grep -w \"$slot2Link\" /usr/local/etc/TGList_FreeDMR.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'"); 
				if (strlen($target_lookup) > 20) {
				    $target_lookup = substr($target_lookup, 0, 15) . '...';
				}
				$TGname = $target_lookup;
			    } else if (strlen($slot2Link) >= 6 && substr( $slot2Link, 0, 1 ) === "8" && startsWith($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'], "DMR+_IPSC2")) {
				$target_lookup = exec("grep -w \"$slot2Link\" /usr/local/etc/TGList_DMRp.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'"); 
				if (strlen($target_lookup) > 20) {
				    $target_lookup = substr($target_lookup, 0, 15) . '...';
				}
				$TGname = $target_lookup;
                            } else if (strlen($slot2Link) >= 6 && substr( $slot2Link, 0, 1 ) === "7" && $_SESSION['DMRGatewayConfigs']['DMR Network 3']['Enabled'] == "1") {
                                if ($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Name'] == "DMR2YSF_Cross-over") {
				    $target_lookup = "DMR2YSF";
				    $TGname = $target_lookup;
                                } else if ($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Name'] == "DMR2NXDN_Cross-over") {
				    $target_lookup = "DMR2NXDN";
				    $TGname = $target_lookup;
                                }
			    } else {
				$target_lookup = trim(exec("grep -w \"$slot2Link\" /usr/local/etc/groups.txt | awk -F, '{print $1}' | head -1 | tr -d '\"'"));
				$TGname = str_replace("$slot2Link: ", "", $target_lookup);
				$stupid_bm = ['/ - 10 Minute Limit/', '/ NOT A CALL CHANNEL/', '/ NO NETS(.*?)/', '/!/'];
				$TGname = preg_replace($stupid_bm, "", $TGname); // strip stupid fucking comments from BM admins in TG names. Idiots.
				if (strlen($TGname) > 20) {
				    $TGname = substr($TGname, 0, 15) . '...';
				}
			    }
			    if (!empty($target_lookup)) {
				echo "<div class='divTableCell cell_content middle active-mode-cell' title='Time Slot 2 Enabled: TG $slot2Link' style='border: .5px solid $tableBorderColor;'>TG $slot2Link<br /><small>($TGname)</small></div>\n";
			    } else {
				if ($slot2Link == "Unlinked") {
				    echo "<div class='divTableCell cell_content middle active-mode-cell' title='Time Slot 1 Enabled: $slot2Link' style='border: .5px solid $tableBorderColor;'>$slot2Link</div>\n";
				} else {
				    echo "<div class='divTableCell cell_content middle active-mode-cell' title='Time Slot 1 Enabled: TG $slot2Link' style='border: .5px solid $tableBorderColor;'>TG $slot2Link</div>\n";
				}
			    }
			} else {
			    echo "<div class='divTableCell cell_content middle active-mode-cell' title='Time Slot 2 Enabled: TG $slot2Link' style='border: .5px solid $tableBorderColor;'>TG $slot2Link</div>\n";
			}
		    } else {
			echo "<div class='divTableCell cell_content middle active-mode-cell' title='Time Slot 2 Enabled: $slot2Link' style='border: .5px solid $tableBorderColor;'>$slot2Link</div>\n";
		    }
		}
	    } else {
		echo "<div class='divTableCell cell_content middle title='Time Slot 1 disabled' style='border: .5px solid $tableBorderColor;'>Disabled</div>\n";
	    }

	    ?>
    </div>
    <div class="divTableRow center">
      <div class="divTableHeadCell" title="DMR Roaming Beacons">Beacons</div>
           <?php
            if (getConfigItem("DMR", "Beacons", $_SESSION['MMDVMHostConfigs']) == 1 && getConfigItem("DMR", "BeaconInterval", $_SESSION['MMDVMHostConfigs']) != null) {
		echo "<div class='divTableCell cell_content middle;' title='Enabled: Timed Mode'>Timed Mode</div>\n";
	    } elseif  (getConfigItem("DMR", "Beacons", $_SESSION['MMDVMHostConfigs']) == 1 && getConfigItem("DMR", "BeaconInterval", $_SESSION['MMDVMHostConfigs']) == null) {
		echo "<div class='divTableCell cell_content middle;' title='Enabled: Network Mode'>Net. Mode</div>\n";
	    } else {
		echo "<div class='divTableCell cell_content middle'><div style=\"background: $tableRowEvenBg;\">Disabled</div></div>\n";
	    }
	    ?>
      </div>
  </div>
    <div class="divTableRow center">
      <div class="divTableHeadCell">DMR ID</div>
      <div class="divTableCell cell_content" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo getConfigItem("General", "Id", $_SESSION['MMDVMHostConfigs']); ?></div>
    </div>
    <div class="divTableRow center">
      <div class="divTableHeadCell">DMR CC</div>
      <div class="divTableCell cell_content" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo getConfigItem("DMR", "ColorCode", $_SESSION['MMDVMHostConfigs']); ?></div>
    </div>
</div>
<div class="divTable">
  <?php if ($numDMRmasters <= 1) { ?>
  <div class="divTableHead"><?php echo $lang['dmr_master'];?></div>
  <?php } else { ?>
  <div class="divTableHead">DMR Masters</div>
  <?php } ?>
  <div class="divTableBody">
	    <?php
	    if (getEnabled("DMR Network", $_SESSION['MMDVMHostConfigs']) == 1) {
		if ($dmrMasterHost == '127.0.0.1') {
		    if (isProcessRunning("DMRGateway")) {
			if ( !isset($_SESSION['DMRGatewayConfigs']['XLX Network 1']['Enabled']) && isset($_SESSION['DMRGatewayConfigs']['XLX Network']['Enabled']) && $_SESSION['DMRGatewayConfigs']['XLX Network']['Enabled'] == 1) {
			    $xlxMasterHostLinkState = "";
			    
                            if (file_exists("/var/log/pi-star/DMRGateway-".gmdate("Y-m-d").".log")) {
				$xlxMasterHostLinkState = exec('grep \'XLX, Linking\|XLX, Unlinking\|XLX, Logged\' /var/log/pi-star/DMRGateway-'.gmdate("Y-m-d").'.log | tail -1 | awk \'{print $5 " " $8 " " $9}\'');
				if(empty($xlxMasterHostLinkState)) {
				    $xlxMasterHostLinkState = exec('grep \'XLX, Linking\|XLX, Unlinking\|XLX, Logged\' /var/log/pi-star/DMRGateway-'.gmdate("Y-m-d", time() - 86340).'.log | tail -1 | awk \'{print $5 " " $8 " " $9}\'');
				}
			    } else {
				$xlxMasterHostLinkState = exec('grep \'XLX, Linking\|XLX, Unlinking\|XLX, Logged\' /var/log/pi-star/DMRGateway-'.gmdate("Y-m-d", time() - 86340).'.log | tail -1 | awk \'{print $5 " " $8 " " $9}\'');
			    }
                            if ($xlxMasterHostLinkState != "") {
                                if ( strpos($xlxMasterHostLinkState, 'Linking') !== false ) {
                                    $xlxMasterHost1 = str_replace('Linking ', '', $xlxMasterHostLinkState);
				    $xlxMasterHost1 = str_replace(" ", " Module ", $xlxMasterHost1); 
                                    $xlxMasterHost1Tooltip= $xlxMasterHost1; 
                                }
                                else if ( strpos($xlxMasterHostLinkState, 'Unlinking') !== false ) {
                                    $xlxMasterHost1 = "XLX Not Linked";
                                    $xlxMasterHost1Tooltip= $xlxMasterHost1;
                                }
                                else if ( strpos($xlxMasterHostLinkState, 'Logged') !== false ) {
                                    $xlxMasterHost1 = "XLX Not Linked";
                                    $xlxMasterHost1Tooltip= $xlxMasterHost1;
                                }
                            }
                            else {
                                // There is no trace of XLX in the logfile.
                                $xlxMasterHost1 = "".$xlxMasterHost1." ".$_SESSION['DMRGatewayConfigs']['XLX Network']['Module']."";
                                $xlxMasterHost1Tooltip= $xlxMasterHost1;    
                            }
 
			    echo "<div class='divTableRow center'><div class='divTableCell'><div " .GetActiveConnectionStyle($remoteDMRgwResults, "xlx")." title=\"".$xlxMasterHost1Tooltip."\">".$xlxMasterHost1."</div></div></div>\n";
			}
			if ($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Enabled'] == 1) {
			    echo "<div class='divTableRow center'><div class='divTableCell'><div " .GetActiveConnectionStyle($remoteDMRgwResults, "net1")." title=\"".$dmrMasterHost1Tooltip."\">".$dmrMasterHost1."</div></div></div>\n";
			}
			if ($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Enabled'] == 1) {
			    echo "<div class='divTableRow center'><div class='divTableCell'><div ".GetActiveConnectionStyle($remoteDMRgwResults, "net2")." title=\"".$dmrMasterHost2Tooltip."\">".$dmrMasterHost2."</div></div></div>\n";
			}
			if ($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Enabled'] == 1) {
			    echo "<div class='divTableRow center'><div class='divTableCell'><div ".GetActiveConnectionStyle($remoteDMRgwResults, "net3")." title=\"".$dmrMasterHost3Tooltip."\">".$dmrMasterHost3."</div></div></div>\n";
			}
			if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 4']['Enabled'])) {
			    if ($_SESSION['DMRGatewayConfigs']['DMR Network 4']['Enabled'] == 1) {
				echo "<div class='divTableRow center'><div class='divTableCell'><div ".GetActiveConnectionStyle($remoteDMRgwResults, "net4")." title=\"".$dmrMasterHost4Tooltip."\">".$dmrMasterHost4."</div></div></div>\n";
			    }
			}
			if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Enabled'])) {
			    if ($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Enabled'] == 1) {
				echo "<div class='divTableRow center'><div class='divTableCell'><div ".GetActiveConnectionStyle($remoteDMRgwResults, "net5")." title=\"".$dmrMasterHost5Tooltip."\">".$dmrMasterHost5."</div></div></div>\n";
			    }
			}
		    }
		    else {
			echo "<div class='divTableRow center'><div class='divTableCell cell_content'><div class='inactive-mode-cell'>Service Not Started</div></div></div>\n";
		    }
		}
		else {
		    echo "<div class='divTableRow center'><div class='divTableCell'><div ".GetActiveConnectionStyle($remoteDMRgwResults, "dmr")." title=\"".$dmrMasterHostTooltip."\">".$dmrMasterHost."</div></div></div>\n";
		}
	    }
	    else {
		echo "<div class='divTableRow center'><div class='divTableCell cell_content'><div class='inactive-mode-cell'>No DMR Network</div></div></div>\n";
	    }
        ?>
      </div>
    </div>
<br />
<?php
}

	$testMMDVModeYSF = getConfigItem("System Fusion Network", "Enable", $_SESSION['MMDVMHostConfigs']);
	if ( isset($_SESSION['DMR2YSFConfigs']['Enabled']['Enabled']) ) {
	    $testDMR2YSF = $_SESSION['DMR2YSFConfigs']['Enabled']['Enabled'];
	}
	if ( $testMMDVModeYSF == 1 || isPaused("YSF") || (isset($testDMR2YSF) && $testDMR2YSF == 1) ) { //Hide the YSF information when System Fusion Network mode not enabled.
		if (isPaused("YSF")) {
			$ysfLinkedTo = "Mode Paused";
			$ysfLinkStateTooltip = $ysfLinkedTo;
		} else {
            		$ysfLinkedTo = getActualLink($reverseLogLinesYSFGateway, "YSF");
		}
	    if ($ysfLinkedTo == 'Not Linked' || $ysfLinkedTo == 'Service Not Started') {
                $ysfLinkedToTxt = $ysfLinkedTo;
		$ysfLinkState = '';
		$ysfLinkStateTooltip = $ysfLinkedTo;
	    }
	    else {
                $ysfHostFile = fopen("/usr/local/etc/YSFHosts.txt", "r");
                $ysfLinkedToTxt = "null";
                while (!feof($ysfHostFile)) {
                    $ysfHostFileLine = fgets($ysfHostFile);
                    $ysfRoomTxtLine = preg_split('/;/', $ysfHostFileLine);
		    
		    if (empty($ysfRoomTxtLine[0]) || empty($ysfRoomTxtLine[1]))
			continue;
		    
                    if (($ysfRoomTxtLine[0] == $ysfLinkedTo) || ($ysfRoomTxtLine[1] == $ysfLinkedTo)) {
			$ysfRoomNo = "YSF".$ysfRoomTxtLine[0];
                        $ysfLinkedToTxt = $ysfRoomTxtLine[1];
                        break;
                    }
                }
		fclose($ysfHostFile);
                $fcsHostFile = fopen("/usr/local/etc/FCSHosts.txt", "r");
                $ysfLinkedToTxt = "null";
                while (!feof($fcsHostFile)) {
                    $ysfHostFileLine = fgets($fcsHostFile);
                    $ysfRoomTxtLine = preg_split('/;/', $ysfHostFileLine);

                    if (empty($ysfRoomTxtLine[0]) || empty($ysfRoomTxtLine[1]))
                        continue;

                    if (($ysfRoomTxtLine[0] == $ysfLinkedTo) || ($ysfRoomTxtLine[1] == $ysfLinkedTo)) {
                        $ysfLinkedToTxt = $ysfRoomTxtLine[1];
			$ysfRoomNo = $ysfRoomTxtLine[0];
                        break;
                    }
                }
		fclose($fcsHostFile);

		if ($ysfLinkedToTxt != "null") {
		    //$ysfLinkedToTxt = "Room: ".$ysfLinkedToTxt;
		    $ysfLinkState = ' [In Room]';
		    $ysfLinkStateTooltip = 'In Room: ';
		}
		else {
		    //$ysfLinkedToTxt = "Linked to: ".$ysfLinkedTo;
		    $ysfLinkedToTxt = $ysfLinkedTo;
		    $ysfLinkState = ' [Linked]';
		    $ysfLinkStateTooltip = 'Linked to ';
		}
		
                $ysfLinkedToTxt = str_replace('_', ' ', $ysfLinkedToTxt);
            }

            if (empty($ysfRoomNo) || ($ysfRoomNo == "null")) {
	        $ysfTableData = $ysfLinkedToTxt;
            } else {
                $ysfTableData = $ysfLinkedToTxt."<br />(".$ysfRoomNo.")";
	    }
	    if ($ysfLinkedTo == 'Not Linked') {
	    	$ysfLinkedToTooltip = $ysfLinkStateTooltip;
	    } else {
		$ysfLinkedToTooltip = $ysfLinkStateTooltip.$ysfLinkedToTxt;
	    }
            if (strlen($ysfLinkedToTxt) > 20) {
		$ysfLinkedToTxt = substr($ysfLinkedToTxt, 0, 15) . '...';
	    }
	    ?>
<div class="divTable">
<?php
if (isPaused("YSF")) {
?>
  <div class="divTableHead"><?php echo $lang['ysf_net'];?></div>
  <div class="divTableBody">
<?php
} else {
?>
  <div class="divTableHead"><?php echo $lang['ysf_net']." ".$ysfLinkState; ?></div>
  <div class="divTableBody">
<?php
}
?>
    <div class="divTableRow center">
<?php
	if (isProcessRunning("YSFGateway")) {
	    echo "<div class='divTableCell cell_content'><div ".GetActiveConnectionStyle($remoteYSFGResults, "ysf")." title=\"".$ysfLinkedToTooltip."\">".$ysfTableData."</div></div>\n";
	} else {
	    echo "<div class='divTableCell cell_content'><div class='inactive-mode-cell'>Service Not Started</div></div>\n";
	}	      
?>
    </div>
  </div>
</div>
<br />
<?php
}

if (getServiceEnabled('/etc/dgidgateway') == 1 )  { // Hide DGId GW info when GW not enabled
?>
<div class="divTable">
  <div class="divTableHead">DG-ID Gateway Status</div>
  <div class="divTableHead">Current DG-ID</div>
  <div class="divTableBody">
    <div class="divTableRow center">
<?php
        if (isPaused("YSF")) {
            echo "<div class='divTableCell cell_content'><div style=\"background: $tableRowEvenBg;\" title=\"YSF Mode Paused\">YSF Mode Paused</div></div>\n";
        }
          else if (isProcessRunning("DGIdGateway")) {
            echo "<div class='divTableCell cell_content'><div style=\"background: $tableRowEvenBg;\" title=\"".str_replace("<br />", " ", getDGIdLinks())."\">".getDGIdLinks()."</div></div>\n";
        } else {
            echo "<div class='divTableCell cell_content'><div class='inactive-mode-cell'>Service Not Started</div></div>\n";
        }
?>
    </div>
  </div>
</div>
<br />
<?php
}

	$testYSF2DMR = 0;
	if ( isset($_SESSION['YSF2DMRConfigs']['Enabled']['Enabled']) ) {
	    $testYSF2DMR = $_SESSION['YSF2DMRConfigs']['Enabled']['Enabled'];
	}
	if ($testYSF2DMR == 1) { //Hide the YSF2DMR information when YSF2DMR Network mode not enabled.
            $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
            $dmrMasterHost = $_SESSION['YSF2DMRConfigs']['DMR Network']['Address'];
            while (!feof($dmrMasterFile)) {
                $dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
                if ((count($dmrMasterHostF) >= 2) && (strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
                    if ($dmrMasterHost == $dmrMasterHostF[2]) {
			$dmrMasterHost = str_replace('_', ' ', $dmrMasterHostF[0]);
		    }
                }
            }
	    $dmrMasterHostTooltip = $dmrMasterHost;
            if (strlen($dmrMasterHost) > 25) {
		$dmrMasterHost = substr($dmrMasterHost, 0, 23) . '...';
	    }
            fclose($dmrMasterFile);
	    ?>
<div class="divTable">
  <div class="divTableHead">YSF2DMR</div>
  <div class="divTableBody">
    <div class="divTableRow center">
<?php
	    echo "<div class='divTableHeadCell'>DMR ID</div><div class='divTableCell cell_content'><div style=\"background: $tableRowEvenBg;\">".$_SESSION['YSF2DMRConfigs']['DMR Network']['Id']."</div></div>\n";
	    echo '</div><div class="divTableRow center">';
	    echo "<div class='divTableHeadCell'>YSF2".$lang['dmr_master']."</div>\n";
            echo "<div class='divTableCell cell_content'><div style=\"background: $tableRowEvenBg;\" title=\"".$dmrMasterHostTooltip."\">".$dmrMasterHost."</div></div>\n";
?>
    </div>
  </div>
</div>
<br />
<?php
	}
	$testMMDVModeP25 = getConfigItem("P25 Network", "Enable", $_SESSION['MMDVMHostConfigs']);
	if ( isset($_SESSION['YSF2P25Configs']['Enabled']['Enabled']) ) { $testYSF2P25 = $_SESSION['YSF2P25Configs']['Enabled']['Enabled']; }
	if ( $testMMDVModeP25 == 1 || $testYSF2P25 || isPaused("P25") ) { //Hide the P25 information when P25 Network mode not enabled.
?>
<div class="divTable">
  <div class="divTableHead"><?php echo $lang['p25_radio']; ?></div>
  <div class="divTableBody">
    <div class="divTableRow center">
<?php
	    if (getConfigItem("P25", "NAC", $_SESSION['MMDVMHostConfigs'])) {
		echo "<div class='divTableHeadCell'>NAC</div><div class='divTableCell cell_content'><div style=\"background: $tableRowEvenBg;\">".getConfigItem("P25", "NAC", $_SESSION['MMDVMHostConfigs'])."</div></div>\n";
	    }
	    echo "</div>\n<div class='divTableRow center'>\n";
	    echo "<div class='divTableHeadCell'>".$lang['p25_net']."</div>\n";
	    if (isPaused("P25")) {
		echo "<div class='divTableCell cell_content'><div style=\"background: $tableRowEvenBg;\">Mode Paused</div></div>\n";
	    } else {
		$P25tg = str_replace("TG", "", getActualLink($logLinesP25Gateway, "P25"));
		if (strpos($P25tg, 'Not Linked') || strpos($P25tg, 'Service Not Started')) {
		    echo "<div class='divTableCell cell_content'><div ".GetActiveConnectionStyle($remoteP25GResults, "p25").">$P25tg</div></div>\n";
		} else {
		    if (empty($P25tg)) {
			echo "<div class='divTableCell cell_content'><div class='inactive-mode-cell'>Not Linked</div></div>\n";
		    } else {
			if (file_exists("/etc/.TGNAMES")) {
			    $P25_target = exec("grep -w \"$P25tg\" /usr/local/etc/TGList_P25.txt | awk -F';' '{print $2}'");
			    echo "<div class='divTableCell cell_content'><div ".GetActiveConnectionStyle($remoteP25GResults, "p25").">TG $P25tg<br /><small>($P25_target)</small></div></div>\n";			    } else {
			    echo "<div class='divTableCell cell_content'><div ".GetActiveConnectionStyle($remoteP25GResults, "p25").">TG $P25tg</div></div>\n";
			}
		    }
		}
	    }
?>
    </div>
  </div>
</div>
<br />
<?php
	}
	
	$testMMDVModeNXDN = getConfigItem("NXDN Network", "Enable", $_SESSION['MMDVMHostConfigs']);
	if ( isset($_SESSION['YSF2NXDNConfigs']['Enabled']['Enabled']) ) {
	    if ($_SESSION['YSF2NXDNConfigs']['Enabled']['Enabled'] == 1) {
		$testYSF2NXDN = 1;
	    }
	}
	if ( isset($_SESSION['DMR2NXDNConfigs']['Enabled']['Enabled']) ) {
	    if ($_SESSION['DMR2NXDNConfigs']['Enabled']['Enabled'] == 1) {
		$testDMR2NXDN = 1;
	    }
	}
	if ( $testMMDVModeNXDN == 1 || isset($testYSF2NXDN) || isset($testDMR2NXDN) || isPaused("NXDN") ) { //Hide the NXDN information when NXDN Network mode not enabled.
if (getConfigItem("NXDN", "RAN", $_SESSION['MMDVMHostConfigs'])) {
?>
<div class="divTable">
  <div class="divTableHead"><?php echo $lang['nxdn_radio']; ?></div>
  <div class="divTableBody">
    <div class="divTableRow center">
<?php
	echo "<div class='divTableHeadCell'>RAN</div>";
	echo "<div class='divTableCell cell_content'><div style=\"background: $tableRowEvenBg;\">".getConfigItem("NXDN", "RAN", $_SESSION['MMDVMHostConfigs'])."</div></div>\n";
	echo "</div>\n<div class='divTableRow center'>";
	echo "<div class='divTableHeadCell'>".$lang['nxdn_net']."</div>\n";
	if (isPaused("NXDN")) {
	    echo "<div class='divTableCell cell_content'><div style=\"background: $tableRowEvenBg;\">Mode Paused</div></div>\n";
	} else {
	    $NXDNtg = str_replace("TG", "", getActualLink($logLinesNXDNGateway, "NXDN"));
	    if (strpos($NXDNtg, 'Not Linked') || strpos($NXDNtg, 'Service Not Started')) {
		echo "<div class='divTableCell cell_content'><div ".GetActiveConnectionStyle($remoteNXDNGResults, "nxdn").">$NXDNtg</div></div>\n";
	    } else {
		if (empty($P25tg)) {
		    echo "<div class='divTableCell cell_content'><div class='inactive-mode-cell'>Not Linked</div></div>\n";
		} else {
		    if (file_exists("/etc/.TGNAMES")) {
			$NXDN_target = exec("grep -w \"$NXDNtg\" /usr/local/etc/TGList_NXDN.txt | awk -F';' '{print $2}'");
			echo "<div class='divTableCell cell_content'><div ".GetActiveConnectionStyle($remoteNXDNGResults, "nxdn").">TG $NXDNtg<br /><small>($NXDN_target)</small></div></div>\n";
		    } else {
			echo "<div class='divTableCell cell_content'><div ".GetActiveConnectionStyle($remoteNXDNGResults, "nxdn").">TG $NXDNtg</div></div>\n";
		    }
		}
	    }
	}
?>
    </div>
  </div>
</div>
<br />
<?php
  }
}
	$testMMDVModeM17 = getConfigItem("M17", "Enable", $_SESSION['MMDVMHostConfigs']);
	$M17can = getConfigItem("M17", "CAN", $_SESSION['MMDVMHostConfigs']);
        $configm17gateway = $_SESSION['M17GatewayConfigs'];
	if ( $testMMDVModeM17 == 1 || isPaused("M17") ) { //Hide the M17 Reflector information when M17 Network not enabled.
?>
<div class="divTable">
  <div class="divTableHead">M17 Status</div>
  <div class="divTableBody">
    <div class="divTableRow center">
<?php
	echo "<div class='divTableHeadCell'>RPT</div>\n<div class='divTableCell cell_content'>\n<div style=\"background: $tableRowEvenBg;\">".str_replace(' ', '&nbsp;', $configm17gateway['General']['Callsign'])."&nbsp;".str_replace(' ', '&nbsp;', $configm17gateway['General']['Suffix'])."</div></div>\n";
	echo "</div>\n<div class='divTableRow center'>";
	echo "<div class='divTableHeadCell'>CAN</div>\n<div class='divTableCell cell_content'>\n<div style=\"background: $tableRowEvenBg;\">$M17can</div></div>\n";
	echo "</div>\n<div class='divTableRow center'>";
	echo "<div class='divTableHeadCell'>Reflector</div>\n";
	if (isPaused("M17")) {
	    echo "<div class='divTableCell cell_content'><div style=\"background: $tableRowEvenBg;\">Mode Paused</div></div>\n";
	} else {
	    echo "<div class='divTableCell cell_content'><div ".GetActiveConnectionStyle($remoteM17GWResults, "m17").">".getActualLink($reverseLogLinesM17Gateway, "M17")."</div></div>\n";
	}
?>
    </div>
  </div>
</div>
<br />
<?php
	}
	
	$testMMDVModePOCSAG = getConfigItem("POCSAG Network", "Enable", $_SESSION['MMDVMHostConfigs']);
	if ( $testMMDVModePOCSAG == 1 || isPaused("POCSAG")) { //Hide the POCSAG information when POCSAG Network mode not enabled.
?>
<div class="divTable">
  <div class="divTableHead">POCSAG Status</div>
  <div class="divTableBody">
    <div class="divTableRow center">
<?php
	echo "<div class='divTableHeadCell'>TX</div>\n<div class='divTableCell cell_content'>\n<div style=\"background: $tableRowEvenBg;\">".getMHZ(getConfigItem("POCSAG", "Frequency", $_SESSION['MMDVMHostConfigs']))."</div></div>\n";
	echo "</div>\n</div>\n</div>\n";
	echo "<div class='divTable'>\n";
	if (isPaused("POCSAG")) {
		$dapnetGatewayRemoteAddr = "Mode Paused";
		$dapnetGatewayRemoteTooltip = $dapnetGatewayRemoteAddr;
	    } else {
		if (isset($_SESSION['DAPNETGatewayConfigs']['DAPNET']['Address'])) {
		    $dapnetGatewayRemoteAddr = $_SESSION['DAPNETGatewayConfigs']['DAPNET']['Address'];
		    $dapnetGatewayRemoteTooltip = $dapnetGatewayRemoteAddr;
		    if (strlen($dapnetGatewayRemoteAddr) > 20) {
		        $dapnetGatewayRemoteAddr = substr($dapnetGatewayRemoteAddr, 0, 15) . ' ...';
		    }
		}
	    }
	    echo "<div class='divTableHead'>DAPNET Master</div>\n";
	    echo "<div class='divTableBody'>\n";
	    echo "<div class='divTableRow center'>\n";
	    if (isProcessRunning("DAPNETGateway")) {
		echo "<div class='divTableCell cell_content'><div style=\"background: $tableRowEvenBg;\" title=\"".$dapnetGatewayRemoteTooltip."\">".$dapnetGatewayRemoteAddr."</div></div>\n";
	    }
	    else {
		echo "<div class='divTableCell cell_content'><div class='inactive-mode-cell'>Service Not Started</div></div>\n";
	    }
?>
    </div>
  </div>
</div>
<br />
<?php
	}
    $m17gatewayConfigFile = '/etc/m17gateway';
    $mmdvmhostConfigFile = '/etc/mmdvmhost';
    if (fopen($m17gatewayConfigFile,'r')) { $configm17gateway = parse_ini_file($m17gatewayConfigFile, true); }
    $configmmdvm = parse_ini_file($mmdvmhostConfigFile, true);
    $testAPRSdmr = $_SESSION['DMRGatewayConfigs']['APRS']['Enable'];
    $testAPRSysf = $_SESSION['YSFGatewayConfigs']['APRS']['Enable'];
    $testAPRSm17 = $configm17gateway['APRS']['Enable'];
    $testAPRSnxdn = $_SESSION['NXDNGatewayConfigs']['APRS']['Enable'];
    $testAPRSdgid = $_SESSION['DGIdGatewayConfigs']['APRS']['Enable'];
    $testAPRSircddb = $_SESSION['ircDDBConfigs']['aprsEnabled'];
    if (getServiceEnabled('/etc/aprsgateway') == 1 || isPaused("APRS"))  { // Hide APRS-IS GW info when GW not enabled
?>
<div class="divTable">
  <div class="divTableHead">APRS Gateway Status</div>
  <div class="divTableBody">
    <div class="divTableRow center">
<?php
if (!isProcessRunning("APRSGateway")) {
?>
<?php echo "<div class='divTableCell cell_content'><div class='inactive-mode-cell'>Service Not Started</div></div>\n"; ?>
    </div>
  </div>
</div>
<br />
<?php
} else {
?>
    </div>
  </div>
</div>
<div class="divTable">
  <div class="divTableBody">
    <div class="divTableRow center">
      <div class='divTableHeadCell'>Pool</div> 
<?php echo "<div class='divTableCell cell_content center' title=\"Connected to Pool: ".$_SESSION['APRSGatewayConfigs']['APRS-IS']['Server']."\">".substr($_SESSION['APRSGatewayConfigs']['APRS-IS']['Server'], 0, 23)."</div>\n"; ?>
    </div>
    <div class="divTableRow center">
      <div class="divTableHeadCell">Server</div>
<?php 
echo "<div class='divTableCell cell_content center'><div style=\"background: $tableRowEvenBg;\">".getAPRSISserver()."</div></div>\n"; ?>
    </div>
  </div>
</div>
<div class="mode_flex">
  <div class="mode_flex row">
    <div class="mode_flex column">
      <div class="divTableHead">Enabled for Modes</div>
    </div>
  </div>
<?php
    if ($testAPRSdmr == 0 && $testAPRSircddb == 0 && $testAPRSysf == 0 && $testAPRSdgid == 0 && $testAPRSnxdn == 0 && $testAPRSm17 == 0) {
?>
  <div class="divTable">
    <div class="divTableBody">
      <div class="divTableRow center">
<?php echo "<div class='divTableCell cell_content center' style=\"background: $tableRowEvenBg;\" title=\"No Mode(s) Selected\"><a href='/admin/configure.php#APRSgw'>No Mode(s) Selected</a></div>\n"; ?>
      </div>
    </div>
  </div>
</div>
<br />
<?php
    } else {
?>
  <div class="mode_flex row">
    <div class="mode_flex column">
<?php	if ($testAPRSdmr == 1) { echo "<div class=\"divTableCell\"><div class=\"active-mode-cell\">DMR</div></div>\n"; } else { echo "<div class=\"divTableCell\"><div class=\"disabled-mode-cell\">DMR</div></div>\n"; } ?>
    </div>
    <div class="mode_flex column">
<?php	if ($testAPRSircddb == 1) { echo "<div class=\"divTableCell\"><div class=\"active-mode-cell\">ircDDB</div></div>\n"; } else { echo "<div class=\"divTableCell\"><div class=\"disabled-mode-cell\">ircDDB</div></div>\n"; } ?>
    </div>
  </div>
  <div class="mode_flex row">
    <div class="mode_flex column">
<?php	if ($testAPRSysf == 1) { echo "<div class=\"divTableCell\"><div class=\"active-mode-cell\">YSF</div></div>\n"; } else { echo "<div class=\"divTableCell\"><div class=\"disabled-mode-cell\">YSF</div></div>\n"; } ?>
      </div>
    <div class="mode_flex column">
<?php	if ($testAPRSdgid == 1) { echo "<div class=\"divTableCell\"><div class=\"active-mode-cell\">DGId</div></div>\n"; } else { echo "<div class=\"divTableCell\"><div class=\"disabled-mode-cell\">DGId</div></div>\n"; } ?>
      </div>
    </div>
  <div class="mode_flex row">
    <div class="mode_flex column">
<?php	if ($testAPRSnxdn == 1) { echo "<div class=\"divTableCell\"><div class=\"active-mode-cell\">NXDN</div></div>\n"; } else { echo "<div class=\"divTableCell\"><div class=\"disabled-mode-cell\">NXDN</div></div>\n"; } ?>
    </div>
    <div class="mode_flex column">
<?php	if ($testAPRSm17 == 1) { echo "<div class=\"divTableCell\"><div class=\"active-mode-cell\">M17</div></div>\n"; } else { echo "<div class=\"divTableCell\"><div class=\"disabled-mode-cell\">M17</div></div>\n"; } ?>
    </div>
  </div>

</div>
<br />
<?php
      }
    }
}
?>
