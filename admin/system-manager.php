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

$fw_enable    = "sudo /usr/local/sbin/pistar-system-manager -efw";
$fw_disable   = "sudo /usr/local/sbin/pistar-system-manager -dfw";
$cron_enable  = "sudo /usr/local/sbin/pistar-system-manager -ec";
$cron_disable = "sudo /usr/local/sbin/pistar-system-manager -dc";
$psr_enable   = "sudo mount -o remount,rw / ; sudo sed -i '/enabled=/c enabled=true' /etc/pistar-remote ; sudo systemctl unmask pistar-remote.service ; sudo systemctl unmask pistar-remote.timer ; sudo systemctl enable pistar-remote.service ; sudo systemctl start pistar-remote.service; sudo systemctl start pistar-remote.timer";
$psr_disable  = "sudo mount -o remount,rw / ; sudo sed -i '/enabled=/c enabled=false' /etc/pistar-remote ; sudo systemctl stop pistar-remote.timer ; sudo systemctl stop pistar-remote.service ; sudo systemctl disable pistar-remote.service; sudo systemctl disable pistar-remote.timer ; sudo systemctl mask pistar-remote.service ; sudo systemctl mask pistar-remote.timer";
$psw_enable   = "sudo mount -o remount,rw / ; sudo systemctl unmask pistar-watchdog.service ; sudo systemctl enable pistar-watchdog.service ; sudo systemctl unmask pistar-watchdog.timer ; sudo systemctl enable pistar-watchdog.timer; sudo systemctl start pistar-watchdog.service ; sudo systemctl start pistar-watchdog.timer";
$psw_disable  = "sudo mount -o remount,rw / ; sudo systemctl disable pistar-watchdog.timer ; sudo systemctl mask pistar-watchdog.timer ; sudo systemctl disable pistar-watchdog.service; sudo systemctl mask pistar-watchdog.service ; sudo systemctl stop pistar-watchdog.timer ; sudo systemctl stop pistar-watchdog.service";

// take action based on form submission
if (!empty($_POST["submit_service"]) && empty($_POST["service_sel"])) { //handler for nothing selected
    $mode = escapeshellcmd($_POST['service_sel']); // get selected mode from for post
    // Output to the browser
    echo '<div style="text-align:left;font-weight:bold;">System Manager</div>'."\n";
    echo "<table>\n";
    echo "  <tr>\n";
    echo "    <th>ERROR</th>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td><p>No Service Selected; Nothing To Do!<br />Page Reloading...</p></td>\n";
    echo "  </tr>\n";
    echo "</table>\n";
    // Clean up...
    unset($_POST);
    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
} elseif
    (!empty($_POST['submit_service']) && escapeshellcmd($_POST['service_action'] == "Disable")) {
    $mode = escapeshellcmd($_POST['service_sel']); // get selected mode from for post
    if ($mode == "Cron" && (getCronState() == 0) || $mode == "PiStar-Remote" && (getPSRState() == 0) || $mode == "Firewall" && (getFWstate() == 0) || $mode == "PiStar-Watchdog" && (getPSWState() == 0)) { //check if already disabled
        // Output to the browser
	echo '<div style="text-align:left;font-weight:bold;">System Manager</div>'."\n";
        echo "<table>\n";
        echo "  <tr>\n";
        echo "    <th>ERROR</th>\n";
        echo "  </tr>\n";
        echo "  <tr>\n";
        echo "    <td><p>$mode already disabled! Did you mean to \"enable\" $mode?<br />Page Reloading...</p></td>\n";
        echo "  </tr>\n"; 
        echo "</table>\n";
        // Clean up...
        unset($_POST);
        echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
    } else { // looks good!
	    if ($mode == "Cron") {
	        system($cron_disable);
	    } elseif ($mode == "Firewall") {
	        system($fw_disable);
	    } elseif ($mode == "PiStar-Remote") {
	        system($psr_disable);
	    } elseif ($mode == "PiStar-Watchdog") {
	        system($psw_disable);
	    } else {
	        die;
	    }
            // Output to the browser
	    echo '<div style="text-align:left;font-weight:bold;">System Manager</div>'."\n";
            echo "<table>\n";
            echo "  <tr>\n";
            echo "    <th>Status</th>\n";
            echo "  </tr>\n";
            echo "  <tr>\n";
            echo "    <td><p>Selected Service ($mode) Disabled!<br />Page Reloading...</p></td>\n";
            echo "  </tr>\n";
            echo "</table>\n";
            // Clean up...
            unset($_POST);
            echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
        }
    } elseif
        (!empty($_POST['submit_service']) && escapeshellcmd($_POST['service_action'] == "Enable")) {
        $mode = escapeshellcmd($_POST['service_sel']); // get selected mode from for post
	if ($mode == "Cron" && (getCronState() == 1) || $mode == "PiStar-Remote" && (getPSRState() == 1) || $mode == "Firewall" && (getFWstate() == 1) || $mode == "PiStar-Watchdog" && (getPSWState() == 1)) {
            // Output to the browser
	    echo '<div style="text-align:left;font-weight:bold;">System Manager</div>'."\n";
            echo "<table>\n";
            echo "  <tr>\n";
            echo "    <th>ERROR</th>\n";
            echo "  </tr>\n";
            echo "  <tr>\n";
            echo "    <td><p>$mode already enabled! Did you mean to \"disable\" $mode?<br />Page Reloading...</p></td>\n";
            echo "  </tr>\n";
            echo "</table>\n";
            // Clean up...
            unset($_POST);
            echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
	} else { // looks good!
	    if ($mode == "Cron") {
		system($cron_enable);
	    } elseif ($mode == "Firewall") {
		sleep(5);
		system($fw_enable);
	    } elseif ($mode == "PiStar-Remote") {
		system($psr_enable);
	    } elseif ($mode == "PiStar-Watchdog") {
		system($psw_enable);
	    } else {
		die;
	    }
            // Output to the browser
	    echo '<div style="text-align:left;font-weight:bold;">System Manager</div>'."\n";
            echo "<table>\n";
            echo "  <tr>\n";
            echo "    <th>Status</th>\n";
            echo "  </tr>\n";
            echo "  <tr>\n";
            echo "    <td><p>Selected Service ($mode) Enabled!<br />Page Reloading...</p></td>\n";
            echo "  </tr>\n";
            echo "</table>\n";
            // Clean up...
            unset($_POST);
            echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
        }
} else {
    // no form post: output html...
    print '
    <div style="text-align:left;font-weight:bold;">System Manager</div>'."\n".'
    <form id="system-action-form" action="'.htmlentities($_SERVER['PHP_SELF']).'?func=sys_man" method="post">
      <table style="white-space: normal;">
        <tr>
	  <th>Enable / Disable</th>
	  <th>Select Service</th>
	  <th>Action</th>
	</tr>
	<tr>
	  <td>
	    <input name="service_action" access="false" id="en-dis-0" value="Disable" type="radio" checked="checked">
	    <label for="en-dis-0">Disable</label>
	    <input name="service_action" access="false" id="en-dis-1"  value="Enable" type="radio">
	    <label for="en-dis-1">Enable</label>
	  </td>
	  <td>
	    <input name="service_sel" id="service-sel-0" value="Firewall" type="radio">
	    <label for="service-sel-0"'.((getFWstate()=='0'? "class='paused-mode-span'":"")). 'title="Disabled">Firewall</label>
	    &nbsp;| <input name="service_sel" id="service-sel-1"  value="Cron" type="radio">
	    <label for="service-sel-1"'.((getCronstate()=='0'? "class='paused-mode-span'":"")). 'title="Disabled">Cron</label>
	    &nbsp;| <input name="service_sel" id="service-sel-2"  value="PiStar-Remote" type="radio">
	    <label for="service-sel-2"'.((getPSRState()=='0'? "class='paused-mode-span'":"")). 'title="Disabled">Pi-Star Remote</label>
	    &nbsp;| <input name="service_sel" id="service-sel-3"  value="PiStar-Watchdog" type="radio">
	    <label for="service-sel-3"'.((getPSWState()=='0'? "class='paused-mode-span'":"")). 'title="Disabled">Pi-Star Watchdog</label>
	    <br />
	  </td>
	  <td>
	    <input type="hidden" name="func" value="sys_man">
	    <input type="submit" class="btn-default btn" name="submit_service" value="Submit" access="false" style="default" id="submit-service" title="Submit">
	  </td>
	</tr>
	<tr>
	  <td colspan="3" style="white-space:normal;padding: 3px;">This function allows you to instantly disable or enable system services. For advanced users!</td>
	</tr>
    </table>
  </form>
';
}
?>
