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
    $backgroundModeCellActiveColor = $_SESSION['CSSConfigs']['Background']['ModeCellActiveColor'];
    $backgroundModeCellPausedColor = $_SESSION['CSSConfigs']['Background']['ModeCellPausedColor'];
    $backgroundModeCellInactiveColor = $_SESSION['CSSConfigs']['Background']['ModeCellInactiveColor'];
} else {
    $tableRowEvenBg = "inherit";
}

?>

<div class="divTable">
  <div class="divTableBody">
    <div class="divTableRow center">
      <div class="divTableHeadCell" style="width:250px;">Radio Status</div>
      <div class="divTableHeadCell">TX Freq.</div>
      <div class="divTableHeadCell">RX Freq.</div>
      <div class="divTableHeadCell">Radio Mode</div>
      <div class="divTableHeadCell">Modem Firmware</div>
      <div class="divTableHeadCell">TCXO Freq.</div>
      <div class="divTableHeadCell">Modem Port</div>
      <div class="divTableHeadCell">Modem Speed</div>
    </div>
    <div class="divTableRow center">
        <?php
        // TRX Status code
        if (isset($lastHeard[0])) {
            $isTXing = false;
            // Go through the whole LH array, backward, looking for transmission.
            for (end($lastHeard); (($currentKey = key($lastHeard)) !== null); prev($lastHeard)) {                                                                         
                    $listElem = current($lastHeard);
                    if ($listElem[2] && ($listElem[6] == null) && ($listElem[5] !== 'RF')) {                                                                              
                        $isTXing = true;
                        // Get rid of 'Slot x' for DMR, as it is meaningless, when 2 slots are txing at the same time.
                        $txMode = preg_split('#\s+#', $listElem[1])[0];
                        echo "<div class=\"divTableCell middle cell_content\" style=\"background:#d11141; color:#ffffff; font-weight:bold;padding:2px;\">TX: $txMode</div>\n";
                        break;
                    }
            }
            if ($isTXing == false) {
                    $listElem = $lastHeard[0];
                if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'idle') {
		    if (isProcessRunning("MMDVMHost")) {
                    	echo "<div class=\"divTableCell middle cell_content\" style=\"font-weight:bold;padding:2px;\">IDLE</div>\n";
		    }
		    else { 
                        echo "<div class='error-state-cell divTableCell middle cell_content' style=\"font-weight:bold;padding:2px;\">OFFLINE</div>\n";
		    }
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === NULL) {
                    if (isProcessRunning("MMDVMHost")) {
			echo "<div class=\"divTableCell middle cell_content\" style=\"font-weight:bold;padding:2px;\">IDLE</div>\n";
                    }
                    else {
                        echo "<div class='error-state-cell divTableCell middle cell_content' style=\"font-weight:bold;color:#ffffff;padding:2px;\">OFFLINE</div>\n";
                    }
                }
                else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'D-Star') {
                    echo "<div class=\"divTableCell middle active-mode-cell\" style=\"font-weight:bold;padding:2px;\">RX: D-Star</div>\n";
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'D-Star') {
                    echo "<div class=\"divTableCell middle cell_content\" style=\"background:#ffc425;color:#000000;font-weight:bold;padding:2px;\">Standby: D-Star</div>\n";
                }
                else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'DMR') {
                    echo "<div class=\"divTableCell middle cell_content active-mode-cell\" style=\"font-weight:bold;padding:2px;\">RX: DMR</div>\n";
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'DMR') {
                    echo "<div class=\"divTableCell middle cell_content\" style=\"background:#ffc425;color:#000000;font-weight:bold;padding:2px;\">Standby: DMR</div>\n";
                }
                else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'YSF') {
                    echo "<div class=\"divTableCell middle cell_content active-mode-cell\" style=\"font-weight:bold;padding:2px;\">RX: YSF</div>\n";
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'YSF') {
                    echo "<div class=\"divTableCell middle cell_content\" style=\"background:#ffc425;color:#000000;font-weight:bold;padding:2px;\">Standby: YSF</div>\n";
                }
                else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'P25') {
                    echo "<div class=\"divTableCell middle cell_content active-mode-cell\" style=\"font-weight:bold;padding:2px;\">RX: P25</div>\n";
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'P25') {
                    echo "<div class=\"divTableCell middle cell_content\" style=\"background:#ffc425;color:#000000;font-weight:bold;padding:2px;\">Standby: P25</div>\n"; 
                }
                else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'M17') {
                    echo "<div class=\"divTableCell middle cell_content active-mode-cell\" style=\"padding:2px;font-weight:bold;\">RX M17</div>\n";
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'M17') {
                    echo "<div class=\"divTableCell middle cell_content\" style=\"background:#ffc425;color:#000000;padding:2px;font-weight:bold;\">Standby: M17</div>\n";
                }
                else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'NXDN') {
                    echo "<div class=\"divTableCell middle cell_content active-mode-cell\" style=\"font-weight:bold;padding:2px;\">RX: NXDN</div>\n";
                }   
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'NXDN') {
                    echo "<div class=\"divTableCell middle cell_content\" style=\"background:#ffc425;color:#000000;font-weight:bold;padding:2px;\">Standby: NXDN</div>\n"; 
                }   
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'POCSAG') {
                    echo "<div class=\"divTableCell middle cell_content\" style=\"color:#fff; background:#d11141; font-weight:bold;padding:2px;\">TX: POCSAG</div>\n";
                }   
                else {
                    echo "<div class=\"divTableCell middle cell_content\">".getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs'])."</div>\n";
                }   
            }   
        }   
        else {
            echo "<div class=\"divTableCell middle cell_content\" style=\"font-weight:bold;padding:2px;\">IDLE</div>\n";
        }
        ?>
      <div class="divTableCell cell_content middle" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo getMHZ(getConfigItem("Info", "TXFrequency", $_SESSION['MMDVMHostConfigs'])); ?></div>
      <div class="divTableCell cell_content middle" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo getMHZ(getConfigItem("Info", "RXFrequency", $_SESSION['MMDVMHostConfigs'])); ?></div>
      <div class="divTableCell cell_content middle" style="background: <?php echo $tableRowEvenBg; ?>;"><?php if(getConfigItem("General", "Duplex", $_SESSION['MMDVMHostConfigs']) == "1") { echo "Duplex"; } else { echo "Simplex"; } ?></div>
      <div class="divTableCell cell_content middle" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo $_SESSION['DvModemFWVersion']; ?></div>
      <div class="divTableCell cell_content middle" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo $_SESSION['DvModemTCXOFreq']; ?></div>
      <div class="divTableCell cell_content middle" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo getConfigItem("Modem", "UARTPort", $_SESSION['MMDVMHostConfigs']); ?></div>
<?php if (!empty(getConfigItem("Modem", "UARTSpeed", $_SESSION['MMDVMHostConfigs']))) { ?>
      <div class="divTableCell cell_content middle" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo number_format(getConfigItem("Modem", "UARTSpeed", $_SESSION['MMDVMHostConfigs'])); ?> bps</div>
<?php } else { ?>
      <div class="divTableCell cell_content middle" style="background: <?php echo $tableRowEvenBg; ?>;"><a href="/admin/configure.php#modem_speed">Please Set Modem Speed...</a></div>
<?php } ?>
    </div>
  </div>
</div>

