<?php
if ($_SERVER["PHP_SELF"] == "/admin/index.php") { // Stop this working outside of the admin page
    
    if (isset($_COOKIE['PHPSESSID']))
    {
	session_id($_COOKIE['PHPSESSID']); 
    }
    if (session_status() != PHP_SESSION_ACTIVE) {
	session_start();
    }
    
    if (!isset($_SESSION) || !is_array($_SESSION) || (count($_SESSION, COUNT_RECURSIVE) < 10)) {
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

?>

<div style="text-align:left;font-weight:bold;">DMR Network Manager</div>

<?php
if (!empty($_POST) && isset($_POST["dmrNetMan"])) {
    $remoteCommand = "";

    // map / get net names & actionsfrom options form
    $selectedNet = $_POST['dmrNet'];
    $state = $_POST['netState'];
    switch ($selectedNet) {
       	case 'net1':
	    $netName = $_SESSION['DMRGatewayConfigs']['DMR Network 1']['Name'];
            break;
     	case 'net2':
	    $netName = $_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'];
            break;
     	case 'net3':
	    $netName = $_SESSION['DMRGatewayConfigs']['DMR Network 3']['Name'];
            break;
     	case 'net4':
	    $netName = $_SESSION['DMRGatewayConfigs']['DMR Network 4']['Name'];
            break;
     	case 'net5':
	    $netName = $_SESSION['DMRGatewayConfigs']['DMR Network 5']['Name'];
            break;
     	case 'xlx':
	    $netName = "XLX-".$_SESSION['DMRGatewayConfigs']['XLX Network']['Startup']."";
            break;

    }
    switch ($state) {
	case 'disable':
	    $action = "touch";
	    break;
	case 'enable':
	    $action = "rm -rf";
	    break;
    }

    $remoteCommand = "sudo systemctl stop cron && sudo mount -o remount,rw / && sudo ".$action." /etc/.dmr-".$selectedNet."_disabled && cd /var/log/pi-star ; /usr/local/bin/RemoteCommand ".$_SESSION['DMRGatewayConfigs']['Remote Control']['Port']. " $state $selectedNet && sudo systemctl start cron";
    if (isset($remoteCommand)) {
	echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
	echo "<p>Selected DMR Network, \"".str_replace('_', ' ' , $netName)."\" set to \"".ucfirst($state)."d\"";
	echo "<br />Reloading page...";
	echo "</p>\n";
	exec($remoteCommand);
	echo "</td></tr>\n</table>\n";
	unset($_POST);
	echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
    }
} else {
?>
<form action="" method="post">
  <table>
    <tr>
      <th>Select DMR Network</th>
      <th>Function</th>
      <th>Action</th>
      <th></th>
    </tr>
    <tr>
      <td>
	<select name="dmrNet">
<?php
if ($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Enabled'] == "1") {
    echo "<option value='net1'>".str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 1']['Name'])."</option>";
}
if ($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Enabled'] == "1") {
    echo "<option value='net2'>".str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 2']['Name'])."</option>";
}
if ($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Enabled'] == "1") {
    echo "<option value='net3'>".str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 3']['Name'])."</option>";
}
if ($_SESSION['DMRGatewayConfigs']['DMR Network 4']['Enabled'] == "1") {
    echo "<option value='net4'>".str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 4']['Name'])."</option>";
}
if ($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Enabled'] == "1") {
    echo "<option value='net5'>".str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 5']['Name'])."</option>";
}
if ($_SESSION['DMRGatewayConfigs']['XLX Network']['Enabled'] == "1") {
    echo "<option value='xlx'>XLX-".$_SESSION['DMRGatewayConfigs']['XLX Network']['Startup']."</option>";
}
?>
	</select>
      </td>
      <td>
	<input type="radio" name="netState" value="disable" id="disableNet"/>  <label for="disableNet">Disable</label>
	<input type="radio" name="netState" value="enable" id="enableNet"/> <label for="enableNet">Enable</label>
      </td>
      <td>
	<input type="submit" value="Request Change" name="dmrNetMan" />
      </td>
      <td>
	Instantly disable / enable DMR Networks.
      </td>
    </tr>
  </table>
</form>

<?php
}
    // Check if XLX is Enabled
    if ( !isset($_SESSION['DMRGatewayConfigs']['XLX Network 1']['Enabled']) && isset($_SESSION['DMRGatewayConfigs']['XLX Network']['Enabled']) && $_SESSION['DMRGatewayConfigs']['XLX Network']['Enabled'] == 1) {
	if (!empty($_POST) && isset($_POST["xlxMgrSubmit"])) {
	    $remoteCommand = "";
	    // Handle Posted Data
	    $xlxLinkHost = $_POST['dmrMasterHost3Startup'];
	    $startupModule = $_POST['dmrMasterHost3StartupModule'];
	    $xlxLinkToHost = "";
 	    if ($xlxLinkHost == "None") { // Unlinking
		$remoteCommand = 'sudo systemctl stop cron && sudo mount -o remount,rw / ; sudo sed -i "/Module=/c\\Module=@" /etc/dmrgateway ; sudo systemctl restart dmrgateway.service ; sudo touch /etc/.XLX_paused && sudo systemctl start cron';
		$xlxLinkToHost = "Unlinking";
	    } elseif ($xlxLinkHost != "None") {
	        $remoteCommand = 'sudo systemctl stop cron && sudo mount -o remount,rw / ; sudo sed -i "/Module=/c\\Module='.$startupModule.'" /etc/dmrgateway ; sudo sed -i "/Startup=/c\\Startup='.$xlxLinkHost.'" /etc/dmrgateway ; sudo systemctl restart dmrgateway.service ; sudo rm /etc/.XLX_paused && sudo systemctl start cron';
		$xlxLinkToHost = "Link set to XLX-".$xlxLinkHost.", Module ".$startupModule."";
	    }
	else {
	    	    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
		    echo "<p>";
		    echo "Something wrong with your input, (Neither Link nor Unlink Sent) - please try again";
		    echo "<br />Page reloading...";
		    echo "</p>\n";
		    echo "</td></tr>\n</table>\n";
		    unset($_POST);
		    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
		}
		if (empty($_POST['dmrMasterHost3Startup'])) {
		    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
		    echo "<p>";
		    echo "Something wrong with your input, (No target specified) -  please try again";
		    echo "<br />Page reloading...";
		    echo "</p>\n";
		    echo "</td></tr>\n</table>\n";
		    unset($_POST);
		    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
		}

		if (isset($remoteCommand)) {
		    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
		    echo "<p>$xlxLinkToHost.<br />Re-Initializing DMRGateway and reloading page...";
		    echo "</p>\n";
		    exec($remoteCommand);
		    echo "</td></tr>\n</table>\n";
		    unset($_POST);
		    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
		}
	    }
	    else {
	    // Output HTML
	    ?>

		<?php  if ($_SESSION['DMRGatewayConfigs']['XLX Network']['Enabled'] == 1) { ?>
		<br />
		<div style="text-align:left;font-weight:bold;">XLX Link Manager</div>
		<form action="" method="post">
		    <table>
			<tr>
			    <th width="150"><a class="tooltip" href="#">Select Reflector<span><b>Select Reflector</b></span></a></th>
			    <th><a class="tooltip" href="#">Module<span><b>Module</b></span></a></th>
			    <th><a class="tooltip" href="#">Current Link<span><b>Current Link</b></span></a></th>
			    <th width="150"><a class="tooltip" href="#">Action<span><b>Action</b></span></a></th>
			    <th></th>
			</tr>
			<tr>
			<td><select name="dmrMasterHost3Startup" class="dmrMasterHost3Startup">
			    <?php
	$configdmrgateway = $_SESSION['DMRGatewayConfigs'];
	$dmrMasterFile3 = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	if (isset($configdmrgateway['XLX Network']['Startup'])) { $testMMDVMdmrMaster3 = $configdmrgateway['XLX Network']['Startup']; }
	if (isset($configdmrgateway['XLX Network']['Startup'])) {
		echo '      <option value="None">None</option>'."\n";
	}
	else {
		echo '      <option value="None" selected="selected">None</option>'."\n";
	}
	while (!feof($dmrMasterFile3)) {
		$dmrMasterLine3 = fgets($dmrMasterFile3);
                $dmrMasterHost3 = preg_split('/\s+/', $dmrMasterLine3);
                if ((strpos($dmrMasterHost3[0], '#') === FALSE ) && (substr($dmrMasterHost3[0], 0, 3) == "XLX") && ($dmrMasterHost3[0] != '')) {
                        if ($testMMDVMdmrMaster3 == $dmrMasterHost3[2]) { echo "      <option value=\"$dmrMasterHost3[2],$dmrMasterHost3[3],$dmrMasterHost3[4],$dmrMasterHost3[0]\" selected=\"selected\">$dmrMasterHost3[0]</option>\n"; }
			if ('XLX_'.$testMMDVMdmrMaster3 == $dmrMasterHost3[0]) { echo "      <option value=\"".str_replace('XLX_', '', $dmrMasterHost3[0])."\" selected=\"selected\">$dmrMasterHost3[0]</option>\n"; }
                        else { echo "      <option value=\"".str_replace('XLX_', '', $dmrMasterHost3[0])."\">$dmrMasterHost3[0]</option>\n"; }
                }
	}
	fclose($dmrMasterFile3);
?>
    </select></td>
    <?php if (isset($configdmrgateway['XLX Network']['TG'])) { ?>
    <td><select name="dmrMasterHost3StartupModule" class="ModSel">
<?php
       if ((isset($configdmrgateway['XLX Network']['Module'])) && ($configdmrgateway['XLX Network']['Module'] != "@")) {                                                 
                echo '        <option value="'.$configdmrgateway['XLX Network']['Module'].'" selected="selected">'.$configdmrgateway['XLX Network']['Module'].'</option>'."\n";
        }
?>
	<option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
        <option value="D">D</option>
        <option value="E">E</option>
        <option value="F">F</option>
        <option value="G">G</option>
        <option value="H">H</option>
        <option value="I">I</option>
        <option value="J">J</option>
        <option value="K">K</option>
        <option value="L">L</option>
        <option value="M">M</option>
        <option value="N">N</option>
        <option value="O">O</option>
        <option value="P">P</option>
        <option value="Q">Q</option>
        <option value="R">R</option>
        <option value="S">S</option>
        <option value="T">T</option>
        <option value="U">U</option>
        <option value="V">V</option>
        <option value="W">W</option>
        <option value="X">X</option>
        <option value="Y">Y</option>
        <option value="Z">Z</option>
    </select>
	</td>
    <?php }
if(getDMRnetStatus("xlx") == "disabled") {
    $target = "<span class='paused-mode-span title='User Disabled'>User Disabled</span>";
} else {
    $target = exec('cd /var/log/pi-star; /usr/local/bin/RemoteCommand ' .$_SESSION['DMRGatewayConfigs']['Remote Control']['Port']. ' hosts | sed "s/ /\n/g" | egrep -oh "XLX(.*)" | sed "s/\"//g" | sed "s/_/ Module /g"'); 
}
?>
<script>
          $(document).ready(function(){
            setInterval(function(){
                $(".CheckLink").load(window.location.href + " .CheckLink" );
                },3000);
            });
</script>
			    <?php if (!empty($target)) { ?>
			    <td><strong class="CheckLink"><?php echo $target; ?></strong></td>
			    <?php } else { ?>
			    <td><strong class="CheckLink">Unlinked</strong></td>
			    <?php } ?>
			    <td>
				<input type="hidden" name="Link" value="LINK" />
				<input type="submit" name="xlxMgrSubmit" value="Request Change" />
			    </td>
        		    <td style="white-space:normal;padding: 3px;">Instantly change XLX reflectors and modules.</td>
			</tr>
                        <tr>
                          <td colspan="5" style="white-space:normal;padding: 3px;">
                            <b><a href="https://w0chp.net/xlx-reflectors/" target="_blank">List of XLX Reflectors (searchable/downloadable)</a></b>
			      (Note: Not all XLX Reflectors support DMR.)
                          </td>
                        </tr>
		    </table>
		</form>
<?php
	    }
	}
    }
}
?>
