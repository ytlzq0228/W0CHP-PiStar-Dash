<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code
$localTXList = $lastHeard;

if (isset($_SESSION['CSSConfigs']['Background'])) {
    $backgroundModeCellActiveColor = $_SESSION['CSSConfigs']['Background']['ModeCellActiveColor'];
    $backgroundModeCellPausedColor = $_SESSION['CSSConfigs']['Background']['ModeCellPausedColor'];
    $backgroundModeCellInactiveColor = $_SESSION['CSSConfigs']['Background']['ModeCellInactiveColor'];
}

?>
  <div style="vertical-align: bottom; font-weight: bold; text-align:left;"><?php echo $lang['local_tx_list'];?></div>
  <table style="white-space:normal; word-wrap:break;">
    <tr>
      <th width="250px"><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)<span><b>Time in <?php echo date('T')?> time zone</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['callsign'];?><span><b>Callsign</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['mode'];?><span><b>Transmitted Mode</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['target'];?><span><b>Target, D-Star Reflector, DMR Talk Group etc</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['src'];?><span><b>Received from source</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['dur'];?>(s)<span><b>Duration in Seconds</b></span></a></th>
      <th style="min-width:5ch" class="noMob"><a class="tooltip" href="#"><?php echo $lang['ber'];?><span><b>Bit Error Rate</b></span></a></th>
      <th style="min-width:8ch" class="noMob"><a class="tooltip" href="#">RSSI<span><b>Received Signal Strength Indication</b></span></a></th>
    </tr>
<?php
$counter = 0;
$i = 0;
$TXListLim = count($localTXList);
for ($i = 0; $i < $TXListLim; $i++) {
		$listElem = $localTXList[$i];
		if ($listElem[5] == "RF" && ($listElem[1] == "D-Star" || startsWith($listElem[1], "DMR") || $listElem[1] == "YSF" || $listElem[1]== "P25" || $listElem[1]== "NXDN" || $listElem[1] == "M17")) {
			if ($counter <= 19) { //last 20 calls
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
			echo"<tr>";
			echo"<td align=\"left\">$local_time</td>";
			if (is_numeric($listElem[2]) || strpos($listElem[2], "openSPOT") !== FALSE) {
				echo "<td align=\"left\">$listElem[2]</td>";
			} elseif (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $listElem[2])) {
				echo "<td align=\"left\">$listElem[2]</td>";
			} else {
				if (strpos($listElem[2],"-") > 0) { $listElem[2] = substr($listElem[2], 0, strpos($listElem[2],"-")); }
				if ($listElem[3] && $listElem[3] != '    ' ) {
					echo "<td align=\"left\"><a href=\"http://www.qrz.com/db/$listElem[2]\" target=\"_blank\">$listElem[2]</a>/$listElem[3]</td>";
				} else {
					echo "<td align=\"left\"><a href=\"http://www.qrz.com/db/$listElem[2]\" target=\"_blank\">$listElem[2]</a></td>";
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
			    if ( substr($listElem[4], 0, 6) === 'CQCQCQ' ) {
				$target = $listElem[4];
			    } else {
				$target = str_replace(" ","&nbsp;", $listElem[4]);
			    }
			    $target = preg_replace('/TG /', '', $listElem[4]);
			    if (strlen($target) >= 2) {
			    	if (strpos($mode, 'DMR') !== false) {
				    $target_lookup = exec("grep -w \"$target\" /usr/local/etc/groups.txt | awk -F, '{print $1}' | head -1 | tr -d '\"'");
				    if (!empty($target_lookup)) {
				        $target = $target_lookup;
				        $stupid_bm = ['/ - 10 Minute Limit/', '/ NOT A CALL CHANNEL/', '/ NO NETS(.*?)/', '/!/'];
				        $target = preg_replace($stupid_bm, "", $target); // strip stupid fucking comments from BM admins in TG names. Idiots.
				        $target = str_replace(": ", " <span class='noMob'>(BM ", $target.")</span>");
				        $target = "TG $target";
				    } else {
				        $target = "TG $target";
				    }
				} else if (strpos($mode, 'NXDN') !== false) {
				    $target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_NXDN.txt | awk -F';' '{print $2}'");
				    if (!empty($target_lookup)) {
					$target = "TG $target <span class='noMob'>($target_lookup)</span>";
				    }
				} else if (strpos($mode, 'P25') !== false) {
				    $target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_P25.txt | awk -F';' '{print $2}'");
				    if (!empty($target_lookup)) {
					$target = "TG $target <span class='noMob'>($target_lookup)</span>";
				    }
				} else {
				    $target = $target;
				}
			    } else {
				$modeArray = array('DMR', 'NXDN', 'P25');
				if (strpos($mode, $modeArray[0]) !== false) {
				    $target = "TG $target";
				} else {
				    $target = $target;
				}
			    }
			    echo "<td align=\"left\">$target</td>";
			} else {
			    if (strlen($listElem[4]) == 1) { $listElem[4] = str_pad($listElem[4], 8, " ", STR_PAD_LEFT); }
			    if ( substr($listElem[4], 0, 6) === 'CQCQCQ' ) {
				echo "<td align=\"left\">$listElem[4]</td>";
			    } else {
				echo "<td align=\"left\">".str_replace(" ","&nbsp;", $listElem[4])."</td>";
			    }
			}

			if ($listElem[5] == "RF"){
				echo "<td><span style='color:$backgroundModeCellInactiveColor;font-weight:bold;'>RF</span></td>";
			} else {
				echo "<td>$listElem[5]</td>";
			}
			if ($listElem[6] == null && (file_exists("/etc/.CALLERDETAILS")))  {
			    echo "<td colspan =\"3\" style=\"background:#d11141;color:#fff;\">TX</td>";
			} else if ($listElem[6] == null) {
				// Live duration
				$utc_time = $listElem[0];
				$utc_tz =  new DateTimeZone('UTC');
				$now = new DateTime("now", $utc_tz);
				$dt = new DateTime($utc_time, $utc_tz);
				$duration = $now->getTimestamp() - $dt->getTimestamp();
				$duration_string = $duration<999 ? round($duration) . "+" : "&infin;";
				echo "<td colspan =\"3\" style=\"background:#d11141;color:#fff;\">TX " . $duration_string . " sec</td>";
			} else if ($listElem[6] == "DMR Data") {
				echo "<td colspan =\"3\" style=\"background:#00718F;color:#fff;\">DMR Data</td>";
			} else {
				$utc_time = $listElem[0];
				$utc_tz =  new DateTimeZone('UTC');
				$now = new DateTime("now", $utc_tz);
 				$dt = new DateTime($utc_time, $utc_tz);
				$TA = timeago( $dt->getTimestamp(), $now->getTimestamp() );
				$duration = "<td>$listElem[6]s <span class='noMob'>($TA)</span></td>";
				echo "$duration"; //duration
				
				// Colour the BER Field
				if (floatval($listElem[8]) == 0) { echo "<td class='noMob'>$listElem[8]</td>"; }
				elseif (floatval($listElem[8]) >= 0.0 && floatval($listElem[8]) <= 1.9) { echo "<td><span style='color:$backgroundModeCellActiveColor;font-weight:bold'>$listElem[8]</span></td>"; }
				elseif (floatval($listElem[8]) >= 2.0 && floatval($listElem[8]) <= 4.9) { echo "<td><span style='color:$backgroundModeCellPausedColor;font-weight:bold'>$listElem[8]</span></td>"; }
				else { echo "<td><span style='color:$backgroundModeCellInactiveColor;font-weight:bold;'>$listElem[8]</span></td>"; }

				echo"<td class='noMob'>$listElem[9]</td>"; //rssi
			}
			echo"</tr>\n";
			$counter++; }
		}
	}

?>
  </table>
