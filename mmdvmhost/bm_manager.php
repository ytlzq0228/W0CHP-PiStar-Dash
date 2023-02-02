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

// Check if DMR is Enabled
$testMMDVModeDMR = getConfigItem("DMR", "Enable", $_SESSION['MMDVMHostConfigs']);
$mmdvmconfigs = $_SESSION['MMDVMHostConfigs'];

if ( $testMMDVModeDMR == 1 ) {
    $bmEnabled = true;

    //setup BM API Key
    $bmAPIkeyFile = '/etc/bmapi.key';
    if (file_exists($bmAPIkeyFile) && fopen($bmAPIkeyFile,'r')) {
      $configBMapi = parse_ini_file($bmAPIkeyFile, true);
      $bmAPIkey = $configBMapi['key']['apikey'];
      $sanitizedKey = str_replace('$', '\$', $bmAPIkey);
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
   
    if ((substr($dmrMasterHost, 0, 3) == "BM ") && ($bmEnabled == true)) { 
	// OK this is Brandmeister, get some configs and output the HTML
	
	// If there is a BM API Key
	if (!empty($_POST) && (!empty($_POST["tgStaticDropAll"]) || !empty($_POST["tgStaticReAdd"]) || !empty($_POST["tgStaticBatch"]))) {  // Data has been posted for this page
            // Static TG handling...
	    // Drop all static:
	    $bmStaticDropAllCmd = ("sudo /usr/local/sbin/bm_static_tgs_dropall $sanitizedKey $dmrID");
	    if (!empty(escapeshellcmd($_POST["tgStaticDropAll"]))) {
	        exec($bmStaticDropAllCmd);
                // Output to the browser
		echo '<br /><div style="text-align:left;font-weight:bold;" id="cmdOut">BrandMeister Manager</div>'."\n";
                echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
                print "<p>All Static Talkgroups Dropped!<br /> Page reloading...</p>";
                echo "</td></tr>\n</table>\n";
                // Clean up...
                unset($_POST);
                echo '<script type="text/javascript">setTimeout(function() { window.location.href = "./?func=bm_man";},3000);</script>';
	    }
	    // re-add all static
            $bmStaticAddAllCmd = ("sudo /usr/local/sbin/bm_static_tgs_addall $sanitizedKey $dmrID");
            if (!empty(escapeshellcmd($_POST["tgStaticReAdd"]))) {
	        // make certain that a previous saved/dropped file actually exists
	        if (file_exists("/etc/.bm_tgs.json.saved")) {
            	    exec($bmStaticAddAllCmd);
            	    // Output to the browser
		    echo '<br /><div style="text-align:left;font-weight:bold;" id="cmdOut">BrandMeister Manager</div>'."\n";
            	    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
            	    print "<p>All Previous Static Talkgroups Re-Added!<br /> Page reloading...</p>";
            	    echo "</td></tr>\n</table>\n";
            	    // Clean up...
            	    unset($_POST);
            	    echo '<script type="text/javascript">setTimeout(function() { window.location.href = "./?func=bm_man";},3000);</script>';
	        }
	        else {
            	    // Output to the browser
		    echo '<br /><div style="text-align:left;font-weight:bold;" id="cmdOut">BrandMeister Manager</div>'."\n";
            	    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
            	    print "<p>No Previous Static Talkgroups Dropped. Nothing To Do!!<br /> Page reloading...</p>";
            	    echo "</td></tr>\n</table>\n";
            	    // Clean up...
            	    unset($_POST);
            	    echo '<script type="text/javascript">setTimeout(function() { window.location.href = "./?func=bm_man";},3000);</script>';
	        }
            }
	    // batch-add/delete static
	    if ( getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == "0" ) {
		unset($_POST["massTGslotSelected"]);
		$massTGslot = "0";
		$dispSlot = "2";
	    } else {
		$massTGslot = escapeshellcmd($_POST["massTGslotSelected"]);
		$dispSlot = $massTGslot;
	    }
	    
            $bmStaticMassAddCmd = ("sudo /usr/local/sbin/bm_static_tgs_batchadd $sanitizedKey $dmrID $massTGslot");
            $bmStaticMassDelCmd = ("sudo /usr/local/sbin/bm_static_tgs_batchdel $sanitizedKey $dmrID $massTGslot");
	    if (!empty(escapeshellcmd($_POST["tgStaticBatch"]))) {
                $massTGs = escapeshellcmd($_POST['massTGlist']);
                if (strlen($massTGs)==0) {
                    // Output to the browser
		    echo '<br /><div style="text-align:left;font-weight:bold;" id="cmdOut">BrandMeister Manager</div>'."\n";
                    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
                    print "<p>No talkgroups defined! <br /> Page reloading...</p>";
                    echo "</td></tr>\n</table>\n";
                    // Clean up...
                    unset($_POST);
                    echo '<script type="text/javascript">setTimeout(function() { window.location.href = "./?func=bm_man";},3000);</script>';
                }
                else  {
		    if (escapeshellcmd($_POST["massTGaction"] == "ADD")) {
		        // keep newlines, but remove non-numeric chars
	                $massTGs = preg_replace("/[^0-9\r\n]/", "", $massTGs);
		        // sep. the data posted
                        $massTGs = explode("\n", str_replace("\r", "", $massTGs));
		        // put data posted into clean array with newline as delimeter
		        $massTGs = implode("\n", $massTGs);
		        // limit the number of talkgroups per form entry (10, for now).
                        $massTGcount = substr_count($massTGs, "\n") + 1;
		        if ($massTGcount > "10") {
                            // Output to the browser
		    	    echo '<br /><div style="text-align:left;font-weight:bold;" id="cmdOut">BrandMeister Manager</div>'."\n";
                            echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
                            print "<p>No more than 10 talkgroups can be defined at a time! <br /> Page reloading...</p>";
                            echo "</td></tr>\n</table>\n";
                            // Clean up...
                            unset($_POST);
                            echo '<script type="text/javascript">setTimeout(function() { window.location.href = "./?func=bm_man";},3000);</script>';
                       } else // 10 or less tgs submitted. keep going...
		           {
                            exec('sudo mount -o remount,rw /');
                            $handleBatch = fopen("/var/www/dashboard/.bm_tgs.batch", 'w+');
                            fwrite($handleBatch, $massTGs);
                            fclose($handleBatch);
			    // need to add a last newline to the file so that the shell script can parse the last (or first and only) TG
		            file_put_contents('/var/www/dashboard/.bm_tgs.batch', "\n".PHP_EOL , FILE_APPEND | LOCK_EX);
                            exec($bmStaticMassAddCmd);
                            // Output to the browser
			    $str = preg_replace('#\s+#',', ',trim($massTGs));
		    	    echo '<br /><div style="text-align:left;font-weight:bold;" id="cmdOut">BrandMeister Manager</div>'."\n";
                            echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
                            print "<p>All Submitted Static Talkgroups ($str) Added to slot $dispSlot! <br /> Page reloading...</p>";
                            echo "</td></tr>\n</table>\n";
                            // Clean up...
                            unset($_POST);
                            echo '<script type="text/javascript">setTimeout(function() { window.location.href = "./?func=bm_man";},3000);</script>';
		        }
		    } elseif
		        (escapeshellcmd($_POST["massTGaction"] == "DEL")) {
	                $massTGs = preg_replace("/[^0-9\r\n]/", "", $massTGs);
                        $massTGs = explode("\n", str_replace("\r", "", $massTGs));
		        $massTGs = implode("\n", $massTGs);
                        $massTGcount = substr_count($massTGs, "\n") + 1;
		        if ($massTGcount > "10") {
                            // Output to the browser
		    	    echo '<br /><div style="text-align:left;font-weight:bold;" id="cmdOut">BrandMeister Manager</div>'."\n";
                            echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
                            print "<p>No more than 10 talkgroups can be defined at a time! <br /> Page reloading...</p>";
                            echo "</td></tr>\n</table>\n";
                            // Clean up...
                            unset($_POST);
                            echo '<script type="text/javascript">setTimeout(function() { window.location.href = "./?func=bm_man";},3000);</script>';
                       } else
		           {
                            exec('sudo mount -o remount,rw /');
                            $handleBatch = fopen("/var/www/dashboard/.bm_tgs.batch", 'w+');
                            fwrite($handleBatch, $massTGs);
                            fclose($handleBatch);
		            file_put_contents('/var/www/dashboard/.bm_tgs.batch', "\n".PHP_EOL , FILE_APPEND | LOCK_EX);
                            exec($bmStaticMassDelCmd);
                            // Output to the browser
			    $str = preg_replace('#\s+#',', ',trim($massTGs)); 
		    	    echo '<br /><div style="text-align:left;font-weight:bold;" id="cmdOut">BrandMeister Manager</div>'."\n";
                            echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
                            print "<p>All Submitted Static Talkgroups ($str) Deleted from slot $dispSlot!<br /> Page reloading...</p>";
                            echo "</td></tr>\n</table>\n";
                            // Clean up...
                            unset($_POST);
                            echo '<script type="text/javascript">setTimeout(function() { window.location.href = "./?func=bm_man";},3000);</script>';
		       }
                   }
                }
            }
 	} else { 
	    // begin single TG management / native api funcs
      if ( (isset($bmAPIkey)) && ( !empty($_POST) && ( isset($_POST["dropDyn"]) || isset($_POST["dropQso"]) || isset($_POST["tgSubmit"]) ) ) ) { // Data has been posted for this page
          $bmAPIurl = 'https://api.brandmeister.network/v1.0/repeater/';
          // Are we a repeater
          if ( getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == "0" ) {
              unset($_POST["TS"]);
              $targetSlot = "0";
            } else {
              $targetSlot = $_POST["TS"];
          }
          // Figure out what has been posted
          if (isset($_POST["dropDyn"])) { $bmAPIurl = $bmAPIurl."setRepeaterTarantool.php?action=dropDynamicGroups&slot=".$targetSlot."&q=".$dmrID; }
          if (isset($_POST["dropQso"])) { $bmAPIurl = $bmAPIurl."setRepeaterDbus.php?action=dropCallRoute&slot=".$targetSlot."&q=".$dmrID; }
          if ( ($_POST["TGmgr"] == "ADD") && (isset($_POST["tgSubmit"])) ) { $bmAPIurl = $bmAPIurl."talkgroup/?action=ADD&id=".$dmrID; }
          if ( ($_POST["TGmgr"] == "DEL") && (isset($_POST["tgSubmit"])) ) { $bmAPIurl = $bmAPIurl."talkgroup/?action=DEL&id=".$dmrID; }
          if ( (isset($_POST["tgNr"])) && (isset($_POST["tgSubmit"])) ) { $targetTG = preg_replace("/[^0-9]/", "", $_POST["tgNr"]); }
          // Build the Data
          if ( (!isset($_POST["dropDyn"])) && (!isset($_POST["dropQso"])) && isset($targetTG) ) {
            $postDataTG = array(
              'talkgroup' => $targetTG,
              'timeslot' => $targetSlot,
            );
          }
          // Build the Query
          $postData = '';
          if (isset($_POST["tgSubmit"])) { $postData = http_build_query($postDataTG); }
          $postHeaders = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: '.strlen($postData),
            'Authorization: Basic '.base64_encode($bmAPIkey.':'),
            'User-Agent: Pi-Star Dashboard for '.$dmrID,
          );

          $opts = array(
            'http' => array(
            'header'  => $postHeaders,
            'method'  => 'POST',
            'content' => $postData,
            'password' => '',
            'success' => '',
            'timeout' => 2,
            ),
          );
          $context = stream_context_create($opts);
          $result = @file_get_contents($bmAPIurl, false, $context);
          $feedback=json_decode($result);
          // Output to the browser
	  echo '<br /><div style="text-align:left;font-weight:bold;" id="cmdOut">BrandMeister Manager</div>'."\n";
          echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td><p>";
          //echo "Sending command to BrandMeister API";
          if (isset($feedback)) { print "BrandMeister APIv1: ".$feedback->{'message'}; } else { print "BrandMeister APIv1: No Response"; }
          echo " <br />Page reloading...</p></td></tr>\n</table>\n";
          // Clean up...
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location.href = "./?func=bm_man";},3000);</script>';
	  }
      elseif ( (isset($bmAPIkeyV2)) && ( (isset($bmAPIkeyV2)) && ( !empty($_POST) && ( isset($_POST["dropDyn"]) || isset($_POST["dropQso"]) || isset($_POST["tgSubmit"]) ) ) ) ) { // Data has been posted for this page
          $bmAPIurl = 'https://api.brandmeister.network/v2/device/';
          // Are we a repeater
          if ( getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == "0" ) {
              unset($_POST["TS"]);
              $targetSlot = "0";
            } else {
              $targetSlot = $_POST["TS"];
          }
          // Set the API URLs
          if (isset($_POST["dropDyn"])) { $bmAPIurl = $bmAPIurl.$dmrID."/action/dropDynamicGroups/".$targetSlot; $method = "GET"; }
          if (isset($_POST["dropQso"])) { $bmAPIurl = $bmAPIurl.$dmrID."/action/dropCallRoute/".$targetSlot; $method = "GET"; }
          if ( (isset($_POST["tgNr"])) && (isset($_POST["tgSubmit"])) ) { $targetTG = preg_replace("/[^0-9]/", "", $_POST["tgNr"]); }
          if ( ($_POST["TGmgr"] == "ADD") && (isset($_POST["tgSubmit"])) ) { $bmAPIurl = $bmAPIurl.$dmrID."/talkgroup/"; $method = "POST"; }
          if ( ($_POST["TGmgr"] == "DEL") && (isset($_POST["tgSubmit"])) ) { $bmAPIurl = $bmAPIurl.$dmrID."/talkgroup/".$targetSlot."/".$targetTG; $method = "DELETE"; }
          
          // Build the Data
          if ( (!isset($_POST["dropDyn"])) && (!isset($_POST["dropQso"])) && isset($targetTG) && $_POST["TGmgr"] == "ADD" ) {
            $postDataTG = array(
              'slot' => $targetSlot,
              'group' => $targetTG              
            );
          }
	  // for feedback
	  if ($_POST["TGmgr"] == "ADD") {
	    $v2fb = "Added";
	  }
	  elseif ($_POST["TGmgr"] == "DEL") {
	    $v2fb = "Deleted";
	  }
          // Build the Query
          $postData = '';
          if ($_POST["TGmgr"] == "ADD") { $postData = json_encode($postDataTG); }
          $postHeaders = array(
            'Content-Type: accept: application/json',
            'Content-Length: '.strlen($postData),
            'Authorization: Bearer '.$bmAPIkeyV2,
            'User-Agent: Pi-Star Dashboard for '.$dmrID,
          );

          $opts = array(
            'http' => array(
            'header'  => $postHeaders,
            'method'  => $method,
            'content' => $postData,
            'password' => '',
            'success' => '',
            'timeout' => 2,
            ),
          );
          $context = stream_context_create($opts);
          $result = @file_get_contents($bmAPIurl, false, $context);
          $feedback=json_decode($result);
          // Output to the browser
	  echo '<br /><div style="text-align:left;font-weight:bold;" id="cmdOut">BrandMeister Manager</div>'."\n";
          echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td><p>";
          //echo "Sending command to BrandMeister API";
          //if (isset($feedback)) { print "BrandMeister APIv2: ".$feedback->{'message'}; } else { print "BrandMeister APIv2: No Response"; }
	  if ($targetSlot == "0") {
	      $dispSlot= "2";
          } else {
	      $dispSlot = $targetSlot;
	  }
          if (isset($feedback)) { print "TG $targetTG on Timeslot $dispSlot $v2fb;<br />BrandMeister APIv2: Success."; } else { print "BrandMeister APIv2: No Response"; }
          echo " <br />Page reloading...</p></td></tr>\n</table>\n";
          // Clean up...
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location.href = "./?func=bm_man"; },3000);</script>';
	    }
	    else { // Do this when we are not handling post data
		    // If there is a BM API Key
            if (isset($bmAPIkey) || isset($bmAPIkeyV2)) {

	    $jsonContext = stream_context_create(array('http'=>array('timeout' => 2, 'header' => 'User-Agent: Pi-Star '.$_SESSION['PiStarRelease']['Pi-Star']['Version'].'W0CHP-Dashboard for '.$dmrID) )); // Add Timout and User Agent to include DMRID
	    if (isset($bmAPIkeyV2)) {
		$json = json_decode(@file_get_contents("https://api.brandmeister.network/v2/device/$dmrID/profile", true, $jsonContext));
	    } else {
		$json = json_decode(@file_get_contents("https://api.brandmeister.network/v1.0/repeater/?action=PROFILE&q=$dmrID", true, $jsonContext));
	    }  
	    // Set some Variables
	    $bmStaticTGList = "";
	    if (isset($json->staticSubscriptions)) { $bmStaticTGListJson = $json->staticSubscriptions; }
		    echo '<br /><div style="text-align:left;font-weight:bold;" id="cmdOut">BrandMeister Manager</div>'."\n";
		    echo '<form id="bm_man" action="'.htmlentities($_SERVER['PHP_SELF']."?func=bm_man#cmdOut").'" method="post">'."\n";
		    echo '<table style="white-space: normal;">'."\n";
		    echo '  <tr>'."\n";
		    echo '    <th colspan="3">Single Static Talkgroup Tools</th>'."\n";
		    echo '    <th rowspan="2">Other Talkgroup Tools</th>'."\n";
		    echo '  </tr>'."\n";
		    echo '  <tr>'."\n";
		    echo '    <th><a class=tooltip href="#">Enter Static Talkgroup:<span><b>Enter the Talkgroup number</b></span></a></th>',"\n";
 		    echo '    <th><a class=tooltip href="#">Timeslot<span><b>Where to link/unlink</b></span></a></th>'."\n";
		    echo '    <th><a class=tooltip href="#">Add / Remove<span><b>Add or Remove</b></span></a></th>'."\n";
		    echo '  </tr>'."\n";
		    echo '  <tr>';
		    echo '    <td><input type="text" id="tgNr" name="tgNr" size="10" maxlength="7" oninput="enableOnNonEmpty(\'tgNr\', \'tgSubmit\', \'tgAdd\', \'tgDel\'); return false;"/></td>'."\n";
		    if (getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) == "1") {
		        echo '    <td><input type="radio" id="ts1" name="TS" value="1" /><label for="ts1"/>TS1</label>&nbsp;';
		    } else {
		        echo '    <td><input type="radio" id="ts1" name="TS" value="1" disabled="disabled" access="false"/><label for="ts1"/>TS1</label>&nbsp;';
		    }
		    echo '    <input type="radio" id="ts2" name="TS" value="2" checked="checked"/><label for="ts2"/>TS2</td>'."\n";
		    echo '    <td style="white-space:nowrap;"><input type="radio" id="tgAdd" name="TGmgr" value="ADD" checked="checked" /><label for="tgAdd">Add</label> &nbsp;<input type="radio" id="tgDel" name="TGmgr" value="DEL" checked="checked" /><label for="tgDel">Delete</label>&nbsp;<input type="submit" value="Add/Delete Static" id="tgSubmit" name="tgSubmit"/></td>'."\n";
		    echo '    <td><input type="submit" value="Drop QSO" title="Drop current QSO" name="dropQso" />&nbsp;'."\n";
		    echo '      <input type="submit" value="Drop All Dynamic" title="Drop all dynamic groups" name="dropDyn" /></td>'."\n";
		    echo '  </tr>'."\n";
		    echo '  <tr>'."\n";
		    echo '    <th><a class=tooltip href="#">Mass Drop / Mass Re-Add Static Talkgroups<span><b>Mass Drop / Mass Re-Add Static Talkgroups</b></span></a></th>'."\n";
		    echo '    <th colspan="3"><a class=tooltip href="#">Bulk-Add/Bulk-Delete Static Talkgroups<span><b>Bulk-Add/Bulk-Delete Static Talkgroups</b></span></a></th>'."\n";
		    echo '  </tr>'."\n";
		    echo '  <tr>'."\n";
		    if (!file_exists("/etc/.bm_tgs.json.saved") && !empty($bmStaticTGListJson)) {
			echo '    <td><input type="submit" value="Drop All Static TGs" id="tgStaticDropAll" name="tgStaticDropAll" /><br />'."\n";
		    } else {
			echo '    <td><input type="button" disabled value="Drop All Static TGs" id="tgStaticDropAll" name="tgStaticDropAll" /><br />'."\n";
		    }
		    if (file_exists("/etc/.bm_tgs.json.saved") && empty($bmStaticTGListJson)) { 
			echo '      <input type="submit" value="Re-Add All Previous  Static TGs" id="tgStaticReAdd" name="tgStaticReAdd"/></td>'."\n";
		    } else {
			echo '      <input type="button" disabled value="Re-Add All Previous  Static TGs" id="tgStaticReAdd" name="tgStaticReAdd"/></td>'."\n";
		    }
		    echo '    <td><b>Enter Talkgroups:</b><br /><small>(max. 10)</small><p><textarea style="vertical-align: middle; resize: none;" rows="5" cols="20" name="massTGlist" placeholder="One per line."></textarea></p></td>'."\n";
		    echo '    <td><b>Timelot:</b><br /><br />';
		    if (getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) == "1") {
		        echo '    <input type="radio" id="massts1" name="massTGslotSelected" value="1"/><label for="massts1"/>TS1</label>&nbsp;';
		    } else {
		        echo '    <input type="radio" id="massts1" name="massTGslotSelected" value="1" disabled="disabled" access="false"/><label for="massts1"/>TS1</label>&nbsp;';
		    }
		    echo '        <input type="radio" id="massts2" name="massTGslotSelected" value="2" checked="checked"/><label for="massts2"/>TS2</label></td>'."\n";
		    echo '    <td><input type="radio" id="masstgAdd" name="massTGaction" value="ADD" /><label for="masstgAdd">Add</label> &nbsp;<input type="radio" id="masstgDel" name="massTGaction" value="DEL" checked="checked" /><label for="masstgDel">Delete</label>&nbsp;<input type="submit" value="Bulk Add/Delete Static TGs" id="tgStaticBatch" name="tgStaticBatch"/></td>'."\n";
		    echo '  </tr>'."\n";
		    echo '  <tr>'."\n";
		    echo '    <td style="white-space:normal;padding: 3px;">This function drops all current static talkgroups, OR re-adds the previously-dropped static talkgroups.</td>'."\n";
		    echo '    <td colspan="3" style="white-space:normal;padding: 3px;">This function mass/bulk-adds or deletes up to 10 static talkgroups. Enter one talkgroup per line.'."\n";
		    echo '  </tr>'."\n";
		    echo '  <tr>'."\n";
		    echo '    <td colspan="4" style="white-space:normal;padding: 3px;">(Note: Give all mass/bulk static talkgroup management functions some time to process, due to the nature of BrandMeister not natively supporting mass-management functions for static takgroups.)'."\n";
		    echo '  </tr>'."\n";
		    echo '</table>'."\n";
		    echo '</form>'."\n";
	        }
	    }
        }
    }
}
?>
