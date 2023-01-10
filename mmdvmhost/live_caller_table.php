<?php
if (file_exists('/etc/.CALLERDETAILS')) {
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';         // Version Lib
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

    // geoLookup/flags
    if (!class_exists('xGeoLookup')) require_once($_SERVER['DOCUMENT_ROOT'].'/classes/class.GeoLookup.php');
    $Flags = new xGeoLookup();
    $Flags->SetFlagFile("/usr/local/etc/country.csv");
    $Flags->LoadFlags();

if (isset($_SESSION['CSSConfigs']['Background'])) {
    $backgroundModeCellActiveColor = $_SESSION['CSSConfigs']['Background']['ModeCellActiveColor'];
    $backgroundModeCellPausedColor = $_SESSION['CSSConfigs']['Background']['ModeCellPausedColor'];
    $backgroundModeCellInactiveColor = $_SESSION['CSSConfigs']['Background']['ModeCellInactiveColor'];
}

// get the data from the MMDVMHost logs
$i = 0;
for ($i = 0;  ($i <= 0); $i++) { //Last 20  calls
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
            // YSF sometimes has malformed calls with a space and freeform text...address these
            if (preg_match('/ /', $listElem[2])) {
                $listElem[2] = preg_replace('/ .*$/', "", $listElem[2]);
            }
            // end cheesy YSF hack
            if (is_numeric($listElem[2]) || strpos($listElem[2], "openSPOT") !== FALSE) {
                $callsign = $listElem[2];
            } elseif (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $listElem[2])) {
                $callsign = $listElem[2];
            } else {
                if (strpos($listElem[2],"-") > 0) {
                    $listElem[2] = substr($listElem[2], 0, strpos($listElem[2],"-"));
                }
		if ( $listElem[3] && $listElem[3] != '    ' ) {
		    $callsign = "<a href=\"http://www.qrz.com/db/$listElem[2]\" target=\"_blank\">$listElem[2]</a>/$listElem[3]";
		} else {
		    $callsign = "<a href=\"http://www.qrz.com/db/$listElem[2]\" target=\"_blank\">$listElem[2]</a>";
		}
            }

	    if (strpos($listElem[4], "via ")) {
		$listElem[4] = preg_replace("/via (.*)/", "<span class='noMob'> $1</span>", $listElem[4]);
	    }
	    
	    if ( substr($listElem[4], 0, 6) === 'CQCQCQ' ) {
		$target = $listElem[4];
	    } else {
		$target = str_replace(" ","&nbsp;", $listElem[4]);
	    }
		
	    $target = preg_replace('/TG /', '', $listElem[4]);

	    $source = $listElem[5];
	
	    if ($listElem[6] == null) {
		// Live duration
		$utc_time = $listElem[0];
		$utc_tz =  new DateTimeZone('UTC');
		$now = new DateTime("now", $utc_tz);
		$dt = new DateTime($utc_time, $utc_tz);
		$duration = $now->getTimestamp() - $dt->getTimestamp();
		$duration_string = $duration<999 ? round($duration) . "+" : "&infin;";
		$duration = "<td style=\"background:#d11141;color:#fff;font-size:1.3em;\">TX " . $duration_string . " sec</td>";
		// dynamic TX <title>
		echo "<script>if(typeof window.original_title === 'undefined'){window.original_title = jQuery('title').text();}</script>";
		echo $_SESSION['MYCALL'] != $listElem[2] ? "<script>jQuery('title').text('>$listElem[2]<');</script>" : "<script>jQuery('title').text('TX');</script>";
	    } else if ($listElem[6] == "DMR Data") {
		$duration =  "<td style=\"background:#00718F;color:#ff;font-size:1.3em;\">DMR Data</td>";
	    } else if ($listElem[6] == "POCSAG") {
		$duration =  "<td style=\"background:#00718F;color:#fff;font-size:1.3em;\">POCSAG</td>";
	    } else {
		$utc_time = $listElem[0];
		$utc_tz =  new DateTimeZone('UTC');
		$now = new DateTime("now", $utc_tz);
 		$dt = new DateTime($utc_time, $utc_tz);
		$TA = timeago( $dt->getTimestamp(), $now->getTimestamp() );
		$duration = "<td style='font-size:1.3em;'>$listElem[6]s <span class='noMob'>($TA)</span></td>";
		// dynamic <title> reset
		echo "<script>if(typeof window.original_title !== 'undefined'){jQuery('title').text(window.original_title)}</script>";
	    }

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

	    $mode = str_replace("Slot ", "TS", $mode);
			
	    if (!is_numeric($listElem[2])) {
        	$searchCall = $listElem[2];
        	$callMatch = array();
		if ($mode == "NXDN") {
		    $handle = @fopen("/usr/local/etc/NXDN.csv", "r");
		} else { # all other modes
		    $handle = @fopen("/usr/local/etc/stripped.csv", "r");
		}
        	if ($handle)
        	{ 
                    while (!feof($handle))
                    {
                	$buffer = fgets($handle);
                	if (strpos($buffer, $searchCall) !== FALSE)
                	{
			    $csvBuffer = explode(",", $buffer);
			    if(strpos($searchCall, $csvBuffer[1]) !== FALSE)
				$callMatch[] = $buffer;
			}
		    }
		    fclose($handle);
		}
		$callMatch = explode(",", $callMatch[0]);
		$name = sentence_cap(" ", "$callMatch[2] $callMatch[3]");
		$city = ucwords(strtolower($callMatch[4]));
		$state = ucwords(strtolower($callMatch[5]));
		$country = ucwords(strtolower($callMatch[6]));
		if(strpos($country, "United States") !== false) {
		   $country = str_replace("United States", "USA", $country);
		}
		if (strlen($country) > 150) {
		    $country = substr($country, 0, 120) . '...';
		}	
		if (empty($callMatch[0])) {
		    $name = getName($listElem[2]);
	    	    // init geo/flag class for country name as fallback
	    	    list ($Flag, $Name) = $Flags->GetFlag($listElem[2]);
		    $country = $Name;
		}
	    }

	    if (file_exists("/etc/.TGNAMES")) {
		$target = tgLookup($mode, $target);
	    } else {
		$modeArray = array('DMR', 'NXDN', 'P25');
		if (strpos($mode, $modeArray[0]) !== false) {
		    $target = "TG $target";
		} else {
		    $target = $target;
		}
	    }

	    if($listElem[2] == "4000" || $listElem[2] == "9990" || $listElem[2] == "DAPNET") {
		$name = "---";
		$city = "";
		$state = "";
		$country = "---";
		if ($listElem[2] == "DAPNET") {
		    $target = "---";
		}
		$duration = "<td style='font-size:1.3em;'>---</td>";
	    }
	    // init geo/flag class
	    list ($Flag, $Name) = $Flags->GetFlag($listElem[2]);
	    if (is_numeric($listElem[2]) || strpos($listElem[2], "openSPOT") !== FALSE || !preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $listElem[2])) {
 		$flContent = "---";
	    } elseif (file_exists($_SERVER['DOCUMENT_ROOT']."/images/flags/".$Flag.".png")) {
		$flContent = "<a class='tooltip' href=\"http://www.qrz.com/db/$listElem[2]\" target=\"_blank\"><img src='/images/flags/$Flag.png?version=$versionCmd' alt='' style='height:25px;' /><span>$Name</span></a>";
	    } else {
		$flContent = "---";
	    }

?>
<div style="vertical-align: bottom; font-weight:bold;text-align:left;margin-top:-12px;">Current / Last Caller Details</div>
  <table style="word-wrap: break-word; white-space:normal;">
    <tr>
      <th><a class="tooltip" href="#"><?php echo $lang['callsign'];?><span><b>Callsign</b></span></a></th>
      <th width="50px">Country</th>
      <th>Name</th>
      <th class='noMob'>Location</th>
      <th><a class="tooltip" href="#"><?php echo $lang['mode'];?><span><b>Transmitted Mode</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['target'];?><span><b>Target, D-Star Reflector, DMR Talk Group etc</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['src'];?><span><b>Received from source</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['dur'];?>(s)<span><b>Duration in Seconds</b></span></a></th>
    </tr>

    <tr>
      <td style="padding:3px 20px 5px 20px;"><strong style="font-size:1.5em;"><?php echo $callsign ?? ' '; ?></strong></td>
      <td><?php echo $flContent; ?></td>
      <td style="font-size:1.3em;"><?php echo $name ?? ' '; ?></td>
      <td class='noMob' style="font-size:1.3em;"><?php
		if (!empty($city)) {
			echo $city .", ";
		}
		if (!empty($state)) {
			echo $state . ", ";
		} if (!empty($country)) { 
			echo $country; 
		} ?></td>
      <td style="font-size:1.3em;"><?php echo $mode ?? ' '; ?></td>
      <td style="font-size:1.3em;"><?php echo $target ?? ' '; ?></td>
      <?php
	if ($listElem[5] == "RF") {
		echo "<td style='font-size:1.3em;'><span style='color:$backgroundModeCellInactiveColor;font-weight:bold;'>RF</span></td>";
	} else {
    		echo" <td style='font-size:1.3em;'>".$source ?? ' '."</td>";
	}
        echo $duration;
    ?>
     </tr>
<?php
	    }
	}
    }
?>
  </table>
<br />
<?php
}
?>
