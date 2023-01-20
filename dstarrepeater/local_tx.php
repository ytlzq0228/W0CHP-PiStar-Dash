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

include_once $_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code

if (isset($_SESSION['PiStarRelease']['Pi-Star']['CallLookupProvider'])) {
    $callsignLookupSvc = $_SESSION['PiStarRelease']['Pi-Star']['CallLookupProvider'];
} else {
    $callsignLookupSvc = "QRZ";
}
if (($callsignLookupSvc != "RadioID") && ($callsignLookupSvc != "QRZ")) {
    $callsignLookupSvc = "QRZ";
}
if ($callsignLookupSvc == "RadioID") {
    $callsignLookupUrl = "https://database.radioid.net/database/view?callsign=";
}
if ($callsignLookupSvc == "QRZ") {
    $callsignLookupUrl = "https://www.qrz.com/db/";
}

?>
<div style="text-align:left;font-weight:bold;"><?php echo $lang['local_tx_list'];?></div>
<table>
    <tr>
	<th><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)</a></th>
	<th><a class="tooltip" href="#"><?php echo $lang['callsign'];?></a></th>
	<th><a class="tooltip" href="#"><?php echo $lang['target'];?></a></th>
	<th><a class="tooltip" href="#">RPT 1</a></th>
	<th><a class="tooltip" href="#">RPT 2</a></th>
    </tr>
    <?php
    // Headers.log sample:
    // 0000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
    // 1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
    // 2012-05-29 20:31:53: Repeater header - My: PE1AGO  /HANS  Your: CQCQCQ    Rpt1: PI1DEC B  Rpt2: PI1DEC G  Flags: 00 00 00
    //
    exec('(grep "Repeater header" '.$hdrLogPath.'|sort -r -k7,7|sort -u -k7,8|sort -r >/tmp/worked.log) 2>&1 &');
    $ci = 0;
    if ($WorkedLog = fopen("/tmp/worked.log",'r')) {
	while ($linkLine = fgets($WorkedLog)) {
            if(preg_match_all('/^(.{19}).*My: (.*).*Your: (.*).*Rpt1: (.*).*Rpt2: (.*).*Flags: (.*)$/',$linkLine,$linx) > 0) {
		$ci++;
		if($ci > 1) {
		    $ci = 0;
		}
                print "<tr>";
		$QSODate = date("d-M-Y H:i:s", strtotime(substr($linx[1][0],0,19)));
                $MyCall = str_replace(' ', '', substr($linx[2][0],0,8));
		$MyCallLink = strtok(substr($linx[2][0],0,8), " ");
                $MyId = str_replace(' ', '', substr($linx[2][0],9,4));
                $YourCall = str_replace(' ', '&nbsp;', substr($linx[3][0],0,8));
                $Rpt1 = str_replace(' ', '&nbsp;', substr($linx[4][0],0,8));
                $Rpt2 = str_replace(' ', '&nbsp;', substr($linx[5][0],0,8));
		$utc_time = $QSODate;
                $utc_tz =  new DateTimeZone('UTC');
                $local_tz = new DateTimeZone(date_default_timezone_get ());
                $dt = new DateTime($utc_time, $utc_tz);
                $dt->setTimeZone($local_tz);
                if (constant("TIME_FORMAT") == "24") {
                    $local_time = date('H:i:s M j');
                } else {
                    $local_time = date('h:i:s A M j');
                }
                print "<td align=\"left\">$local_time</td>";
		print "<td align=\"left\"><a href=\"".$callsignLookupUrl.$MyCallLink."\" target=\"_blank\">$MyCall</a>";
                if($MyId) { print "/".$MyId."</td>"; } else { print "</td>"; }
                print "<td align=\"left\">$YourCall</td>";
                print "<td align=\"left\">$Rpt1</td>";
                print "<td align=\"left\">$Rpt2</td>";
                print "</tr>\n";
	    }
	}
	fclose($WorkedLog);
    }
    ?>
</table>
