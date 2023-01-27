<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';         // Version Lib
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code

if (isset($_SESSION['CSSConfigs']['ExtraSettings']['LastHeardRows']) && $_SESSION['PiStarRelease']['Pi-Star']['ProcNum'] >= 4) {
    $lastHeardRows = $_SESSION['CSSConfigs']['ExtraSettings']['LastHeardRows'];
    if ($lastHeardRows > 100) {  
	$lastHeardRows = "100";  // need an internal limit
    }
} else {
    $lastHeardRows = "40";
}
if (isset($_SESSION['CSSConfigs']['Background'])) {
    $backgroundModeCellActiveColor = $_SESSION['CSSConfigs']['Background']['ModeCellActiveColor'];
    $backgroundModeCellPausedColor = $_SESSION['CSSConfigs']['Background']['ModeCellPausedColor'];
    $backgroundModeCellInactiveColor = $_SESSION['CSSConfigs']['Background']['ModeCellInactiveColor'];
}

if (isset($_SESSION['PiStarRelease']['Pi-Star']['CallLookupProvider'])) {
    $callsignLookupSvc = $_SESSION['PiStarRelease']['Pi-Star']['CallLookupProvider'];
} else {
    $callsignLookupSvc = "QRZ";
}
if (($callsignLookupSvc != "RadioID") && ($callsignLookupSvc != "QRZ")) {
    $callsignLookupSvc = "QRZ";
}
$idLookupUrl = "https://database.radioid.net/database/view?id=";
if ($callsignLookupSvc == "RadioID") {
    $callsignLookupUrl = "https://database.radioid.net/database/view?callsign=";
}
if ($callsignLookupSvc == "QRZ") {
    $callsignLookupUrl = "https://www.qrz.com/db/";
}

// geoLookup/flags
if (!class_exists('xGeoLookup')) require_once($_SERVER['DOCUMENT_ROOT'].'/classes/class.GeoLookup.php');
$Flags = new xGeoLookup();
$Flags->SetFlagFile("/usr/local/etc/country.csv");
$Flags->LoadFlags();

// for name column
$testMMDVModeDMR = getConfigItem("DMR", "Enable", $_SESSION['MMDVMHostConfigs']);
?>
<?php 
if (getEnabled("DMR", $_SESSION['MMDVMHostConfigs']) == 1) {
    if(file_exists('/etc/.CALLERDETAILS')) {
?>
<input type="hidden" name="lh-talias" value="OFF" />
  <div style="float: right; vertical-align: bottom; padding-top: 0px;" id="lhTA">
        <div class="grid-container" style="display: inline-grid; grid-template-columns: auto 40px; padding: 1px; grid-column-gap: 5px;">
            <div class="grid-item menuta" style="padding: 10px 0 0 20px;">DMR Talker Alias
            </div>
            <div class="grid-item">
                <div style="padding: 6px 0 0 0;">
                  <input id="toggle-lh-talias" class="toggle toggle-round-flat" type="checkbox" name="lh-talias" value="ON" <?php if(file_exists('/etc/.SHOWDMRTA')) { echo 'checked="checked"';}?> aria-checked="true" aria-label="Show Talker Alias" onchange="setTalkerAlias(this)" /><label for="toggle-lh-talias" ></label>
                </div>
            </div>
        </div>
    </div>
<?php
    }
}
?>
<input type="hidden" name="filter-activity" value="OFF" />
<div style="float: right; vertical-align: bottom; padding-top: 0px;" id="lhAc">
  <div class="grid-container" style="display: inline-grid; grid-template-columns: auto 40px; padding: 1px;; grid-column-gap: 5px;">
    <div class="grid-item filter-activity" style="padding: 10px 0 0 20px;" title="Hide Kerchunks">Hide Kerchunks: 
    </div>
      <div class="grid-item">
        <div style="padding-top:6px;">
          <input id="toggle-filter-activity" class="toggle toggle-round-flat" type="checkbox" name="display-lastcaller" value="ON" <?php if ( file_exists( '/etc/.FILTERACTIVITY' ) ) { echo 'checked="checked"'; }; ?> aria-checked="true" aria-label="Filter Out Kerchunks (< 1s)" onchange="setFilterActivity(this)" /><label for="toggle-filter-activity" ></label>
      </div>
    </div>
  </div>
  <?php 
    if ( file_exists( '/etc/.FILTERACTIVITY' ) ) : ?>
      <div class="filter-activity-max-wrap">
        < <input onChange='setFilterActivityMax(this)' class='filter-activity-max' style="width:40px;" type='number' step='0.5' min='0.5' name='filter-activity-max' value='<?php echo file_get_contents( '/etc/.FILTERACTIVITY' ); ?>' /> s
      </div>
  <?php endif; ?>
</div>
            <input type="hidden" name="display-lastcaller" value="OFF" />
            <div style="float: right; vertical-align: bottom; padding-top: 0px;" id="lhCN">
               <div class="grid-container" style="display: inline-grid; grid-template-columns: auto 40px; padding: 1px;; grid-column-gap: 5px;">
                <?php if(isset($_SESSION['PiStarRelease']['Pi-Star']['ProcNum']) && ($_SESSION['PiStarRelease']['Pi-Star']['ProcNum'] >= 4)) { ?>
                 <div class="grid-item menucaller" style="padding: 10px 0 0 20px;" title="Display Caller Details">Caller Details: </div>
                   <div class="grid-item">
                    <div style="padding-top:6px;">
                        <input id="toggle-display-lastcaller" class="toggle toggle-round-flat" type="checkbox" name="display-lastcaller" value="ON" <?php if(file_exists('/etc/.CALLERDETAILS')) { echo 'checked="checked"';}?> aria-checked="true" aria-label="Display Caller Details" onchange="setLastCaller(this)" /><label for="toggle-display-lastcaller" ></label>
                <?php } else { ?>
                 <div class="grid-item menucaller" style="padding: 10px 0 0 20px;opacity: 0.5;" title="Function Disabled: Hardware too weak.">Caller Details: </div>
                   <div class="grid-item">
                    <div style="padding-top:6px;">
                        <input id="toggle-display-lastcaller" class="toggle toggle-round-flat" type="checkbox" name="display-lastcaller" value="ON"  aria-checked="true" aria-label="Display Last Caller Details" disabled="disabled" title="Function Disabled: Hardware too weak." /><label for="toggle-display-lastcaller" title="Function Disabled: Hardware too weak."></label>
                        <?php } ?>
                    </div>
                   </div>
                 </div>
            </div>
<?php if (getEnabled("DMR", $_SESSION['MMDVMHostConfigs']) == 1 || getEnabled("NXDN", $_SESSION['MMDVMHostConfigs']) == 1 || getEnabled("P25", $_SESSION['MMDVMHostConfigs']) == 1) { ?>
<input type="hidden" name="lh-tgnames" value="OFF" />
  <div style="float: right; vertical-align: bottom; padding-top: 0px;" id="lhTGN">
        <div class="grid-container" style="display: inline-grid; grid-template-columns: auto 40px; padding: 1px; grid-column-gap: 5px;">
            <div class="grid-item menutgnames" style="padding-top: 10px;">Display TG Names
            </div>
            <div class="grid-item">
                <div style="padding: 6px 20px 0 0;">
		  <input id="toggle-lh-tgnames" class="toggle toggle-round-flat" type="checkbox" name="lh-tgnames" value="ON" <?php if(file_exists('/etc/.TGNAMES')) { echo 'checked="checked"';}?> aria-checked="true" aria-label="Show TG Names" onchange="setLHTGnames(this)" /><label for="toggle-lh-tgnames" ></label>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<div style="vertical-align: bottom; font-weight: bold; padding-top:14px;text-align:left;"><?php echo $lang['last_heard_list'];?></div>
  <table>
    <tr>
      <th width="250px"><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)<span><b>Time in <?php echo date('T')?> time zone</b></span></a></th>
      <th width="85px"><a class="tooltip" href="#"><?php echo $lang['callsign'];?><span><b>Callsign</b></span></a></th>
      <th width="50px"><a class="tooltip" href="#">Country<span><b>Country</b></span></a></th>
<?php
    if (file_exists("/etc/.CALLERDETAILS") && $testMMDVModeDMR == 1 ) {
?>
      <th class="noMob"><a class="tooltip" href="#">Name<span><b>Name</b></span></a></th>
<?php
    }
?>
      <th><a class="tooltip" href="#"><?php echo $lang['mode'];?><span><b>Transmitted Mode</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['target'];?><span><b>Target, D-Star Reflector, DMR Talk Group etc</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['src'];?><span><b>Received from source</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['dur'];?>(s)<span><b>Duration in Seconds</b></span></a></th>
      <th class="noMob"><a class="tooltip" href="#"><?php echo $lang['loss'];?><span><b>Packet Loss</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['ber'];?><span><b>Bit Error Rate</b></span></a></th>
    </tr>
<?php
$i = 0;
for ($i = 0;  ($i <= $lastHeardRows - 1); $i++) {
	if (isset($lastHeard[$i])) {
		$listElem = $lastHeard[$i];
		if ( $listElem[2] ) {
                $utc_time = $listElem[0];
                $utc_tz =  new DateTimeZone('UTC');
                $local_tz = new DateTimeZone(date_default_timezone_get ());
                $dt = new DateTime($utc_time, $utc_tz);
                $dt->setTimeZone($local_tz);
                if (constant("TIME_FORMAT") == "24") {
                    $local_time = $dt->format('H:i:s M j');
                } else {
                    $local_time = $dt->format('h:i:s A M j');
                }
		if (preg_match('/[\s-]/', $listElem[2])) { // handle and display calls with certain suffixes:
		    if ($listElem[1] == "M17") {  // M17 supports two suffix types: "-n" and a "/n". MMDVMHost uses multiple, spaces instead of a "/". Let's parse those suffixes...
			$listElem[2] = preg_replace('!\s+!', ',', $listElem[2]);
			$listElem[2] = preg_replace('/-/', ',', $listElem[2]);
		    } else { // all other modes with dash and/or single space
			$listElem[2] = preg_replace('/[\s+-]/', ',', $listElem[2]);
		    }
		    $callBase = explode(",", $listElem[2]);
		    $callPre = $callBase[0];
		    if (empty($callBase[1])) { // handler for suffix specified, but has space or is empty (e.g. clueless YSF users)
			$callSuff = ""; // kill invalid suffix
		    } else {
			$callSuff = "-$callBase[1]"; // "CALL-SUFF" format
		    }
		} else { // no suffix
		    $callPre = $listElem[2];
		    $callSuff = "";
		}
		// init geo/flag class
		list ($Flag, $Name) = $Flags->GetFlag($listElem[2]);
		if (is_numeric($listElem[2]) !== FALSE || !preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $listElem[2])) {
		    $flContent = " ";
		} elseif (file_exists($_SERVER['DOCUMENT_ROOT']."/images/flags/".$Flag.".png")) {
		    $flContent = "<a class='tooltip' href=\"$callsignLookupUrl"."$callPre\" target=\"_blank\"><div style='padding: 0 12px;'><img src='/images/flags/$Flag.png?version=$versionCmd' alt='' style='height:18px;' /></div><span>$Name</span></a>";
		} else {
		    $flContent = " ";
		}
		echo"<tr>";
		echo"<td align=\"left\" title='Row #".($i+1)."'>$local_time</td>";
		if (is_numeric($listElem[2])) {
		    if (file_exists("/etc/.CALLERDETAILS") && $testMMDVModeDMR == 1 ) {
			if ($flContent = " " && empty($listElem[11])) {
			    if ($listElem[2] > 9999) {
			    	echo "<td class='noMob' align=\"left\"><a href=\"".$idLookupUrl.$listElem[2]."\" target=\"_blank\">$listElem[2]</a></td><td align=\"left\" colspan='2'>&nbsp</td>";
			    } else {
			    	echo "<td class='noMob' align=\"left\">$callPre$callSuff</td><td align=\"left\" colspan='2'>&nbsp</td>";
			    }
			} else {
			    if ($listElem[2] > 9999) {
                            	echo "<td align=\"left\"><a href=\"".$idLookupUrl.$listElem[2]."\" target=\"_blank\">$listElem[2]</a></td><td>$flContent</td><td align='left' class='noMob'>$listElem[11]</td>";
			    } else {
                            	echo "<td align=\"left\">$callPre$callSuff</td><td>$flContent</td><td align='left' class='noMob'>$listElem[11]</td>";
			    }
			}
		    } else {
			if ($listElem[2] > 9999) {
                            echo "<td align=\"left\"><a href=\"".$idLookupUrl.$listElem[2]."\" target=\"_blank\">$listElem[2]</a></td><td>$flContent</td>";
			} else {
                            echo "<td align=\"left\">$callPre$callSuff</td><td>$flContent</td>";
			}
		    }
		} elseif (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $listElem[2])) {
		    if (file_exists("/etc/.CALLERDETAILS") && $testMMDVModeDMR == 1 ) {
			if ($flContent = " " && empty($listElem[11])) {
			    echo "<td class='noMob' align=\"left\">$callPre$callSuff</td><td align=\"left\" colspan='2'>&nbsp</td>";
			} else {
                            echo "<td align=\"left\">$callPre$callSuff</td><td>$flContent</td><td align='left' class='noMob'>$listElem[11]</td>";
			}
		    } else {
                        echo "<td align=\"left\">$callPre$callSuff</td><td>$flContent</td>";
		    }
		} else {
		    if ( $listElem[3] && $listElem[3] != '    ' ) {
			if (file_exists("/etc/.CALLERDETAILS") && $testMMDVModeDMR == 1 ) {
			    echo "<td align=\"left\"><a href=\"$callsignLookupUrl"."$callPre\" target=\"_blank\">$listElem[2]</a>/$listElem[3]</td><td>$flContent</td><td align='left' class='noMob'>$listElem[11]</td>";
			} else {
			    echo "<td align=\"left\"><a href=\"$callsignLookupUrl"."$listElem[2]\" target=\"_blank\">$listElem[2]</a>/$listElem[3]</td><td>$flContent</td>";
			}
		    } else {
			if (file_exists("/etc/.CALLERDETAILS") && $testMMDVModeDMR == 1 ) {
			    echo "<td align=\"left\"><a href=\"$callsignLookupUrl"."$callPre\" target=\"_blank\">$callPre</a>$callSuff</td><td>$flContent</td><td align='left' class='noMob'>$listElem[11]</td>";
			} else {
			    echo "<td align=\"left\"><a href=\"$callsignLookupUrl"."$callPre\" target=\"_blank\">$callPre</a>$callSuff</td><td>$flContent</td></td>";
			}
		    }
		}

		echo "<td align=\"left\">".str_replace('Slot ', 'TS', $listElem[1])."</td>";

		if (file_exists("/etc/.TGNAMES")) {
		    if ($listElem[8] == null) {
			$ber = "&nbsp;";
		    } else {
			$mode = $listElem[8];
		    }
		    if ($listElem[1] == null) {
			$ber = "&nbsp;";
		    } else {
			$mode = $listElem[1];
		    }

                    if (strpos($listElem[4], "via ")) {
                        $listElem[4] = preg_replace("/via (.*)/", "<span class='noMob'> $1</span>", $listElem[4]);
                    }
                    if (strpos($listElem[4], "at ")) {
                        $listElem[4] = preg_replace("/at (.*)/", "<span class='noMob'>at $1</span>", $listElem[4]);
                    }

		    if (strlen($listElem[4]) == 1) { $listElem[4] = str_pad($listElem[4], 8, " ", STR_PAD_LEFT); }
                    if ( substr($listElem[4], 0, 6) === 'CQCQCQ' ) {
                        $target = $listElem[4];
                    } else {
                        $target = str_replace(" "," ", $listElem[4]);
                    }
		    $target = preg_replace('/TG /', '', $listElem[4]);
                    $target = tgLookup($mode, $target);

		    echo "<td align=\"left\">$target</td>";
		} else {
                    if (strpos($listElem[4], "via ")) {
                        $listElem[4] = preg_replace("/via (.*)/", "<span class='noMob'> $1</span>", $listElem[4]);
                    }
                    if (strpos($listElem[4], "at ")) {
                        $listElem[4] = preg_replace("/at (.*)/", "<span class='noMob'>at $1</span>", $listElem[4]);
                    }
                    
                    if ( substr($listElem[4], 0, 6) === 'CQCQCQ' ) {
		    	echo "<td align=\"left\">$listElem[4]</td>";
                    } else {
			echo "<td align=\"left\">".str_replace(" "," ", $listElem[4])."</td>";
                    }  
		}

		if ($listElem[5] == "RF") {
			echo "<td><span style='color:$backgroundModeCellInactiveColor;font-weight:bold;'>RF</span></td>";
		} else {
			echo "<td>$listElem[5]</td>";
		}
		if ($listElem[6] == null && (file_exists("/etc/.CALLERDETAILS")))  {
			echo "<td colspan =\"3\" class='activity-duration' style=\"background:#d11141;color:#fff;\">TX</td>";
		} else if ($listElem[6] == null) {
			// Live duration
			$utc_time = $listElem[0];
			$utc_tz =  new DateTimeZone('UTC');
			$now = new DateTime("now", $utc_tz);
			$dt = new DateTime($utc_time, $utc_tz);
			$duration = $now->getTimestamp() - $dt->getTimestamp();
			$duration_string = $duration<999 ? round($duration) . "+" : "&infin;";
			echo "<td colspan=\"3\" ass='activity-duration' tyle=\"background:#d11141;color:#fff;\">TX " . $duration_string . " sec</td>";
		} else if ($listElem[6] == "DMR Data") {
			echo "<td class='noMob' colspan =\"3\" style=\"background:#00718F;color:#fff;\">DMR Data</td>";
		} else if ($listElem[6] == "POCSAG") {
			echo "<td class='noMob' colspan=\"3\" style=\"background:#00718F;color:#fff;\">POCSAG</td>";
		} else {
			echo "<td class='activity-duration'>$listElem[6]</td>";

			// Colour the Loss Field
			if (floatval($listElem[7]) < 1) { echo "<td class='noMob'>$listElem[7]</td>"; }
			elseif (floatval($listElem[7]) == 1) { echo "<td class='noMob'><span style='color:$backgroundModeCellActiveColor;font-weight:bold'>$listElem[7]</span></td>"; }
			elseif (floatval($listElem[7]) > 1 && floatval($listElem[7]) <= 3) { echo "<td class='noMob'><span style='color:$backgroundModeCellPausedColor;font-weight:bold'>$listElem[7]</span></td>"; }
			else { echo "<td class='noMob'><span style='color:$backgroundModeCellInactiveColor;font-weight:bold;'>$listElem[7]</span></td>"; }

			// Colour the BER Field
			if (floatval($listElem[8]) == 0) { echo "<td>$listElem[8]</td>"; }
			elseif (floatval($listElem[8]) >= 0.0 && floatval($listElem[8]) <= 1.9) { echo "<td><span style='color:$backgroundModeCellActiveColor;font-weight:bold'>$listElem[8]</span></td>"; }
			elseif (floatval($listElem[8]) >= 2.0 && floatval($listElem[8]) <= 4.9) { echo "<td><span style='color:$backgroundModeCellPausedColor;font-weight:bold'>$listElem[8]</span></td>"; }
			else { echo "<td><span style='color:$backgroundModeCellInactiveColor;font-weight:bold;'>$listElem[8]</span></td>"; }
		}
		echo"</tr>\n";
	    }
	}
}
?>
  </table>
  <script>clear_activity();</script>

