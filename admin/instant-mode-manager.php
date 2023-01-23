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

$mode_cmd = '/usr/local/sbin/pistar-mmdvmhost-module';

$mmdvmConfigFile = '/etc/mmdvmhost';
$configmmdvm = parse_ini_file($mmdvmConfigFile, true);
$aprsConfigFile = '/etc/aprsgateway';
$configaprsgw = parse_ini_file($aprsConfigFile, true);

function manageGateways($func,$service) { // we need to also stop the associated mode gateways when pausing modes...
// ...otherwise if the mode is TX'ing when paused, it will show infinite TX in the dashboard.
    $service = escapeshellcmd($_POST['mode_sel']);
    $service = strtolower($service);

    // POSCAG mode uses DAPNETGateway service; translate it...
    if ($service == "pocsag") {
	$service = "dapnet";
    }
    // D-Star mode uses ircddbgateway service; translate...
    if ($service == "dstar") {
	$service = "ircddb";
    }

    // manage the services...
    exec("sudo systemctl $func $service"."gateway.timer");
    exec("sudo systemctl $func $service"."gateway.service");

    // check that no other modes are paused. If so, we can manage the watchdog service.
    // if we don't stop the watchdog, it will (re-)start the stopped gateway for the paused mode. We don't want that...
    $is_paused = glob('/etc/*_paused');
    if (empty($is_paused) == TRUE) {
        exec("sudo systemctl $func pistar-watchdog.timer");
        exec("sudo systemctl $func pistar-watchdog.service");
    }
}

// check status of supported modes
$DSTAR  = ($configmmdvm['D-Star']['Enable']);
$DMR    = ($configmmdvm['DMR']['Enable']);
$YSF    = ($configmmdvm['System Fusion']['Enable']);
$P25    = ($configmmdvm['P25']['Enable']);
$NXDN   = ($configmmdvm['NXDN']['Enable']);
$M17    = ($configmmdvm['M17']['Enable']);
$AX25   = ($configmmdvm['AX.25']['Enable']);
$POCSAG = ($configmmdvm['POCSAG']['Enable']);
$APRS   = ($configaprsgw['Enabled']['Enabled']);

// take action based on form submission
if (!empty($_POST["submit_mode"]) && empty($_POST["mode_sel"])) { //handler for nothing selected
    $mode = escapeshellcmd($_POST['mode_sel']); // get selected mode from for post
    // Output to the browser
    echo "<b>Instant Mode Manager</b>\n";
    echo "<table>\n";
    echo "  <tr>\n";
    echo "    <th>ERROR</th>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td><p>No Mode Selected; Nothing To Do!<br />Page Reloading...</p></td>\n";
    echo "  </tr>\n";
    echo "</table>\n";
    // Clean up...
    unset($_POST);
    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
} elseif
    (!empty($_POST['submit_mode']) && escapeshellcmd($_POST['mode_action'] == "Pause")) {
    $mode = escapeshellcmd($_POST['mode_sel']); // get selected mode from form post
    if (isPaused($mode)) { //check if already paused
        // Output to the browser
        echo "<b>Instant Mode Manager</b>\n";
        echo "<table>\n";
        echo "  <tr>\n";
        echo "    <th>ERROR</th>\n";
        echo "  </tr>\n";
        echo "  <tr>\n";
        echo "    <td><p>$mode mode already paused! Did you mean to \"resume\" $mode mode?<br />Page Reloading...</p></td>\n";
        echo "  </tr>\n"; 
        echo "</table>\n";
        // Clean up...
        unset($_POST);
        echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
    } else { // looks good!
	manageGateways("stop", $service);
        exec("sudo $mode_cmd $mode Disable"); // pause the seleced $mode
        // Output to the browser
        echo "<b>Instant Mode Manager</b>\n";
        echo "<table>\n";
        echo "  <tr>\n";
        echo "    <th>Status</th>\n";
        echo "  </tr>\n";
        echo "  <tr>\n";
        echo "    <td><p>Selected Mode ($mode) Paused!<br />Page Reloading...</p></td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
        // Clean up...
        unset($_POST);
        echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
    }
} elseif
    (!empty($_POST['submit_mode']) && escapeshellcmd($_POST['mode_action'] == "Resume")) {
    $mode = escapeshellcmd($_POST['mode_sel']); // get selected mode from for post
    if (!isPaused($mode)) { //check if already running
        // Output to the browser
        echo "<b>Instant Mode Manager</b>\n";
        echo "<table>\n";
        echo "  <tr>\n";
        echo "    <th>ERROR</th>\n";
        echo "  </tr>\n";
        echo "  <tr>\n";
        echo "    <td><p>$mode mode already running! Did you mean to \"pause\" $mode mode?<br />Page Reloading...</p></td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
        // Clean up...
        unset($_POST);
        echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
    } else { // looks good!
        exec("sudo $mode_cmd $mode Enable"); // resume the seleced $mode
	manageGateways("start", $service);
        // Output to the browser
        echo "<b>Instant Mode Manager</b>\n";
        echo "<table>\n";
        echo "  <tr>\n";
        echo "    <th>Status</th>\n";
        echo "  </tr>\n";
        echo "  <tr>\n";
        echo "    <td><p>Selected Mode ($mode) Resumed!<br />Page Reloading...</p></td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
        // Clean up...
        unset($_POST);
        echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
    }
} else {
    // no form post: output html...
    print '
    <div style="text-align:left;font-weight:bold;">Instant Mode Manager</div>'."\n".'
    <form id="action-form" action="'.htmlentities($_SERVER['PHP_SELF']).'?func=mode_man" method="post">
      <table style="white-space: normal;">
        <tr>
	  <th>Pause / Resume</th>
	  <th>Select Mode</th>
	  <th>Action</th>
	</tr>
	<tr>
          <td style="white-space:nowrap;">
            <input name="mode_action" access="false" id="pause-res-0" value="Pause" type="radio" checked="checked">
            <label for="pause-res-0">Pause</label>
            <input name="mode_action" access="false" id="pause-res-1"  value="Resume" type="radio">
            <label for="pause-res-1">Resume</label>
          </td>
          <td style="white-space:nowrap;"><br />
            <input name="mode_sel" '.(($DMR=='0' && !isPaused("DMR")?'disabled="disabled"':"")).' access="false" id="mode-sel-0"  value="DMR" type="radio">
            <label for="mode-sel-0"'.(($DMR=='0' && isPaused("DMR")?" class='paused-mode-span' title='Paused'":"")).'>DMR</label>
            &nbsp;| <input name="mode_sel" '.(($YSF=='0' && !isPaused("YSF")?'disabled="disabled"':"")).'  access="false" id="mode-sel-1"  value="YSF" type="radio">
            <label for="mode-sel-1"'.(($YSF=='0' && isPaused("YSF")?" class='paused-mode-span' title='Paused'":"")).'>YSF</label>
            &nbsp;| <input name="mode_sel" '.(($DSTAR=='0' && !isPaused("D-Star")?'disabled="disabled"':"")).' access="false" id="mode-sel-2"  value="D-Star" type="radio">
            <label for="mode-sel-2"'.(($DSTAR=='0' && isPaused("D-Star")?" class='paused-mode-span' title='Paused'":"")).'>D-Star</label>
            &nbsp;| <input name="mode_sel" '.(($P25=='0' && !isPaused("P25")?'disabled="disabled"':"")).' access="false" id="mode-sel-3"  value="P25" type="radio">
            <label for="mode-sel-3"'.(($P25=='0' && isPaused("P25")?" class='paused-mode-span' title='Paused'":"")).'>P25</label>
            &nbsp;| <input name="mode_sel" '.(($NXDN=='0' && !isPaused("NXDN")?'disabled="disabled"':"")).' access="false" id="mode-sel-4"  value="NXDN" type="radio">
            <label for="mode-sel-4"'.(($NXDN=='0' && isPaused("NXDN")?" class='paused-mode-span' title='Paused'":"")).'>NXDN</label>
            &nbsp;| <input name="mode_sel" '.(($M17=='0' && !isPaused("M17")?'disabled="disabled"':"")).' access="false" id="mode-sel-5"  value="M17" type="radio">
            <label for="mode-sel-5"'.(($M17=='0' && isPaused("M17")?" class='paused-mode-span' title='Paused'":"")).'>M17</label>
<!--
            &nbsp;| <input name="mode_sel" '.(($AX25=='0' && !isPaused("AX25")?'disabled="disabled"':"")).' access="false" id="mode-sel-6"  value="AX25" type="radio">
            <label for="mode-sel-6"'.(($AX25=='0' && isPaused("AX25")?" class='paused-mode-span' title='Paused'":"")).'>AX.25</label>
-->
            &nbsp;| <input name="mode_sel" '.(($POCSAG=='0' && !isPaused("POCSAG")?'disabled="disabled"':"")).' access="false" id="mode-sel-7"  value="POCSAG" type="radio">
            <label for="mode-sel-7"'.(($POCSAG=='0' && isPaused("POCSAG")?" class='paused-mode-span' title='Paused'":"")).'>POCSAG</label>
<!--
	    &nbsp;| <input name="mode_sel" '.(($APRS=='0' && !isPaused("APRS")?'disabled="disabled"':"")).' access="false" id="mode-sel-8"  value="APRS" type="radio">
            <label for="mode-sel-8"'.(($APRS=='0' && isPaused("APRS")?" class='paused-mode-span' title='Paused'":"")).'>APRS</label>
-->
	    <br /><br />
          </td>
          <td>
            <input type="hidden" name="func" value="mode_man">
            <input type="submit" class="btn-default btn" name="submit_mode" value="Submit" access="false" style="default" id="submit-mode" title="Submit">
          </td>
        </tr>
        <tr>
          <td colspan="3" style="white-space:normal;padding: 3px;">This function allows you to instantly pause or resume selected radio modes. Handy for attending nets, quieting a busy mode, to temporarily eliminate "mode monopolization", etc.</td>
        </tr>
        <tr>
	  <td colspan="3" style="white-space:normal;padding: 3px;"><b>Note:</b> Modes you not have <a href="/admin/configure.php">configured/enabled globally</a>, are not selectable in the Instant Mode Manager.</td>
        </tr>
      </table>
    </form>
';
}

?>
