<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';         // Version Lib
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
// geoLookup/flags
if (!class_exists('xGeoLookup')) require_once($_SERVER['DOCUMENT_ROOT'].'/classes/class.GeoLookup.php');                                                              
$Flags = new xGeoLookup();
$Flags->SetFlagFile("/usr/local/etc/country.csv");
$Flags->LoadFlags();
if (constant("TIME_FORMAT") == "24") {
    $local_time = date('H:i:s M j');
} else {
    $local_time = date('h:i:s A M j');
}

// Get the CPU temp and color value accordingly...
// Values/thresholds gathered from: 
// <https://www.rs-online.com/designspark/how-does-raspberry-pi-deal-with-overheating>
$cpuTempCRaw = exec('cat /sys/class/thermal/thermal_zone0/temp');
if ($cpuTempCRaw > 1000) { $cpuTempC = sprintf('%.0f',round($cpuTempCRaw / 1000, 1)); } else { $cpuTempC = sprintf('%.0f',round($cpuTempCRaw, 1)); }
$cpuTempF = sprintf('%.0f',round(+$cpuTempC * 9 / 5 + 32, 1));
if ($cpuTempC <= 59) { $cpuTempHTML = "<span class='cpu_norm'>".$cpuTempF."&deg;F / ".$cpuTempC."&deg;C</span>\n"; }
if ($cpuTempC >= 60) { $cpuTempHTML = "<span class='cpu_warm'>".$cpuTempF."&deg;F / ".$cpuTempC."&deg;C</span>\n"; }
if ($cpuTempC >= 80) { $cpuTempHTML = "<apan class='cpu_hot'>".$cpuTempF."&deg;F / ".$cpuTempC."&deg;C</span>\n"; }

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
            // YSF sometimes has malformed calls with a space and freeform text...address these
            if (preg_match('/ /', $listElem[2])) {
                $listElem[2] = preg_replace('/ .*$/', "", $listElem[2]);
            }
            // end cheesy YSF hack
            if (is_numeric($listElem[2]) || strpos($listElem[2], "openSPOT") !== FALSE) {
                $listElem[2] = $listElem[2];
            } elseif (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $listElem[2])) {
                $listElem[2] = $listElem[2];
            } else {
                if (strpos($listElem[2],"-") > 0) {
                    $listElem[2] = substr($listElem[2], 0, strpos($listElem[2],"-"));
                }
            }
        }
    }
}

if (strpos($listElem[4], "via ")) {
    $listElem[4] = preg_replace("/via (.*)/", "$1", $listElem[4]);
}

if ( substr($listElem[4], 0, 6) === 'CQCQCQ' ) {
	$target = $listElem[4];
} else {
	$target = str_replace(" ","&nbsp;", $listElem[4]);
}
		
$target = preg_replace('/TG /', '', $listElem[4]);
		
if ($listElem[5] == "RF"){
	$source = "<span class='source_rf'>RF</span>";
} else {
	$source = "<span class='source_other'>$listElem[5]</span>";
}

if ($listElem[6] == null) {
	// Live duration
	$utc_time = $listElem[0];
	$utc_tz =  new DateTimeZone('UTC');
	$now = new DateTime("now", $utc_tz);
	$dt = new DateTime($utc_time, $utc_tz);
	$duration = $now->getTimestamp() - $dt->getTimestamp();
	$duration_string = $duration<999 ? round($duration) . "+" : "&infin;";
	$duration = "<span class='dur_tx'>TX " . $duration_string . " sec</span>";
	// dynamic TX <title>
	echo "<script>if(typeof window.original_title === 'undefined'){window.original_title = jQuery('title').text();}</script>";
	echo $_SESSION['MYCALL'] != $listElem[2] ? "<script>jQuery('title').text('>$listElem[2]<');localStorage.setItem(last_caller,'$listElem[2]');jQuery('.last-caller').hide();</script>" : "<script>jQuery('title').text('TX');</script>";
  
} else if ($listElem[6] == "DMR Data")
    {
	$duration =  "<span class='dur_data'>DMR Data</span>";
} else if ($listElem[6] == "POCSAG") {
        $duration =  "<span class='dur_data'>POCSAG</span>";
} else {
  $utc_time = $listElem[0];
  $utc_tz =  new DateTimeZone('UTC');
  $now = new DateTime("now", $utc_tz);
  $dt = new DateTime($utc_time, $utc_tz);
  $duration = $listElem[6].'s (' . timeago( $dt->getTimestamp(), $now->getTimestamp() ) . ')';
  // dynamic <title> reset
  echo "<script>if(typeof window.original_title !== 'undefined'){jQuery('title').text(window.original_title)};jQuery('.last-caller').hide();</script>";
}

if ($listElem[7] == null) {
	$loss = "&nbsp;&nbsp;&nbsp;";
	}
	elseif (floatval($listElem[7]) < 1) { $loss = "<span>".$listElem[7]."</span>";
	}
	elseif (floatval($listElem[7]) == 1) { $loss = "<span class='loss_ok'>".$listElem[7]."</span>";
	}
	elseif (floatval($listElem[8]) > 1 && floatval($listElem[7]) <= 3) { $loss = "<span class='loss_med'>".$listElem[7]."</span>";
} else {
	$loss = "<span class='loss_bad'>".$listElem[7]."</span>";
}
			
if ($listElem[8] == null) {
	$ber = "&nbsp;&nbsp;&nbsp;&nbsp;";
} else {
	$mode = $listElem[8];
}

if ($listElem[1] == null) {
	$ber = "&nbsp;&nbsp;&nbsp;&nbsp;";
} else {
	$mode = $listElem[1];
}
			
// Color the BER Field
if (floatval($listElem[8]) == 0) {
	$ber = $listElem[8];
}
elseif (floatval($listElem[8]) >= 0.0 && floatval($listElem[8]) <= 1.9)
{
	$ber = "<span class='ber_ok'>".$listElem[8]."</span>";
}
	elseif (floatval($listElem[8]) >= 2.0 && floatval($listElem[8]) <= 4.9)
{
	$ber = "<span class='ber_med'>".$listElem[8]."</span>";
} else {
	$ber = "<span class='ber_bad'>".$listElem[8]."</span>";
}

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
	    if (strpos($buffer, $searchCall) !== FALSE) {
		$csvBuffer = explode(",", $buffer);
		if(strpos($searchCall, $csvBuffer[1]) !== FALSE)
		$callMatch[] = $buffer;
	    }
	}
	fclose($handle);
    }
    $callMatch = explode(",", $callMatch[0]);
    $name = ucwords(strtolower("$callMatch[2] $callMatch[3]"));
    $city = ucwords(strtolower($callMatch[4]));
    $state = ucwords(strtolower($callMatch[5]));
    $country = ucwords(strtolower($callMatch[6]));
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
 
if (strlen($target) >= 2) {
    if (strpos($mode, 'DMR') !== false) {
	if (strlen($target) >= 7 && substr( $target, 0, 1 ) === "5" && $_SESSION['DMRGatewayConfigs']['DMR Network 4']['Enabled'] == "1") {
	    $target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_TGIF.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'");
	    if (!empty($target_lookup)) {
		$target = "TG $target ($target_lookup)";
	    } else {
		$target = "TG $target";
	    }
	} else if (substr( $target, 0, 1 ) === "8" && $_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'] == "DMR+_IPSC2-FreeSTAR") { 
	    $target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_FreeStarIPSC.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'");                                     
	    if (!empty($target_lookup)) {
		$target = "TG $target ($target_lookup)";                                                                                                
	    } else {
		$target = "TG $target";
	    }
	} else if (substr( $target, 0, 1 ) === "8" && startsWith($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'], "SystemX")) {
	    $target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_SystemX.txt | awk -F, '{print $2}' | head -1 | tr -d '\"'"); 
	    if (!empty($target_lookup)) {
		$target = "TG $target ($target_lookup)";                                                                                                
	    } else {
		$target = "TG $target";
	    }
	} else {
	    $target_lookup = exec("grep -w \"$target\" /usr/local/etc/groups.txt | awk -F, '{print $1}' | head -1 | tr -d '\"'");
	    if (!empty($target_lookup)) {
		$target = $target_lookup;
		$stupid_bm = ['/ - 10 Minute Limit/', '/ NOT A CALL CHANNEL/', '/ NO NETS(.*?)/', '/!/'];
		$target = preg_replace($stupid_bm, "", $target); // strip stupid fucking comments from BM admins in TG names. Idiots.
		$target = str_replace(": ", " (", $target.")");
		$target = "TG $target";
	    } else {
		$target = "TG $target";
	    }
	}
    } else if (strpos($mode, 'NXDN') !== false) {
	$target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_NXDN.txt | awk -F';' '{print $2}'");
	if (!empty($target_lookup)) {
	    $target = "TG $target ($target_lookup)";
	}
    } else if (strpos($mode, 'P25') !== false) {
	$target_lookup = exec("grep -w \"$target\" /usr/local/etc/TGList_P25.txt | awk -F';' '{print $2}'");
	if (!empty($target_lookup)) {
	    $target = "TG $target ($target_lookup)";
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

if ($listElem[2] == "4000" || $listElem[2] == "9990" || $listElem[2] == "DAPNET") {
	$name = "";
	$city = "";
	$state = "";
	$country = "";
	$loss = "";
	$ber = "";
	$duration = "";
}

// init geo/flag class
list ($Flag, $Name) = $Flags->GetFlag($listElem[2]);
if (is_numeric($listElem[2]) || strpos($listElem[2], "openSPOT") !== FALSE || !preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $listElem[2])) {
    $flContent = "";
} else {
    if (file_exists($_SERVER['DOCUMENT_ROOT']."/images/flags/250px/".$Flag.".png")) {
	    $flContent = "<img src='/images/flags/250px/$Flag.png?version=$versionCmd' alt='$Name' title='$Name' style='width:200px;' />";
    } else {
	$flContent = "";
    }
}   
?>
<div class='live-page-wrapper'>
  <div class='row'>
    <div class='column'>
      <div class='orange-column'>
        <span class='oc_call'><?php echo "$listElem[2]"; ?></span>
      </div>
    </div>
    <div class='column'>
      <div class='orange-column'>
        <span style="position: relative; top: 30%; transform: translateY(-50%);"><?php echo $flContent; ?></span>
      </div>
    </div>
    <div class='column'>
      <div class='orange-column'>
        <span class='oc_caller'>
	  <span class='oc_name'>
	    <?php  echo $name;  ?>
	  </span>
	    <?php
	    if (!empty($city)) {
		echo "<br /> $city";
	    }  
	    if (!empty($state)) {
		echo "<br />$state";
	    } 
	    echo "<br />$country";
	    ?>
	</span>
      </div>
    </div>
  </div>
  <div class='row'>
    <div class='column'>
      <div class='dark-column'>
	<span class='dc_info'>
	  Source: 
	  <span class='dc_info_def'>
	    <?php echo $source; ?>
	  </span>
	  <br />
	  Mode: 
	  <span class='dc_info_def'>
	    <?php echo $mode; ?>
	  </span>
	  <br />
	  Target: 
	  <span class='dc_info_def'>
	    <?php echo $target; ?>
	  </span>
	</span>
      </div>
    </div>
    <div class='column'>
      <div class='dark-column'>
	<span class='dc_info'>
	  TX Duration: 
	  <span class='dc_info_def'>
	    <?php echo $duration ?>
	  </span>
	  <br />
          Packet Loss: 
	  <span class='dc_info_def'>
	    <?php echo $loss ?>
	  </span>
	  <br />
          Bit Error Rate: 
	  <span class='dc_info_def'>
	    <?php echo $ber ?>
	  </span>
    <span class="last-caller" style="display:none;"><br />Last Caller ID: <span class='dc_info_def'></span></span>
	</span>
      </div>
    </div>
  </div>
  <div class='row'>
    <div class='column'>
      <div class='footer-column'>
        <span class='foot_left'>
	  <a href="/">Main Dashboard</a>
	</span>
        <span class='foot_right'>
          Hotspot Time:
          <span class='hw_info_def'>
            <?php echo $local_time; ?>
          </span>
	</span>
      </div>
    </div>
  </div>
  <div class='row'>
    <div class='column'>
      <div class='footer-column'>
        <span class='foot_left'>
          Hostname: <?php echo exec('cat /etc/hostname'); ?>
        </span>
        <span class='foot_right'>
          <div class='hw_info'>
             CPU Temp:
             <span class='hw_info_def'>
                <?php echo $cpuTempHTML; ?>
             </span>
          </div>
	</span>
      </div>
    </div>
  </div>
</div>

<?php if ( $listElem[6] == null && $_SESSION['MYCALL'] == $listElem[2] ) : ?>
<script>
  if(typeof localStorage.getItem('last_caller') !== 'undefined' ) {
    jQuery('.last-caller').show().find('span').html(localStorage.getItem('last_caller'));
  }
</script>
<?php endif;
