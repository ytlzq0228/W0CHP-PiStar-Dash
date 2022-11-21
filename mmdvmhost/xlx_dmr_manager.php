<?php
//if ($_SERVER["PHP_SELF"] == "/admin/index.php") { // Stop this working outside of the admin page
    
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

    // Check if XLX is Enabled
    if ($_SESSION['DMRGatewayConfigs']['XLX Network']['Enabled'] == 1) { 
	if (!empty($_POST) && isset($_POST["xlxMgrSubmit"])) {
	    $remoteCommand = "";
	    // Handle Posted Data
	    $xlxLinkHost = $_POST['xlxLinkHost'];
	    $startupModule = $_POST['dmrMasterHost3StartupModule'];
	    $xlxLinkToHost = "";
 	    if ($xlxLinkHost == "None") { // Unlinking
		$remoteCommand = 'sudo mount -o remount,rw / ; sudo sed -i "/Startup=/c\\Startup=" /etc/dmrgateway ; sudo systemctl restart dmrgateway.service ; sudo touch /etc/.XLX_paused';
		$xlxLinkToHost = "Unlinking";
	    } elseif ($xlxLinkHost != "None") {
	        $remoteCommand = 'sudo mount -o remount,rw / ; sudo sed -i "/Module=/c\\Module='.$startupModule.'" /etc/dmrgateway; sudo sed -i "/Startup=/c\\Startup='.$xlxLinkHost.'" /etc/dmrgateway ; sudo systemctl restart dmrgateway.service ; sudo rm /etc/.XLX_paused';
		$xlxLinkToHost = "Link set to XLX-".$xlxLinkHost.", Module ".$startupModule."";
	    }
	else {
	    echo "<div style='text-align:left;font-weight:bold;'>XLX DMR Link Manager</div>\n";
	    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
		    echo "<p>";
		    echo "Something wrong with your input, (Neither Link nor Unlink Sent) - please try again";
		    echo "<br />Page reloading...";
		    echo "</p>\n";
		    echo "</td></tr>\n</table>\n";
		    unset($_POST);
		    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
		}
		if (empty($_POST['xlxLinkHost'])) {
		    echo "<div style='text-align:left;font-weight:bold;'>XLX DMR Link Manager</div>\n";
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
		    echo "<div style='text-align:left;font-weight:bold;'>XLX DMR Link Manager</div>\n";
		    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
		    echo "<p>$xlxLinkToHost.<br />Re-Initializing DMRGateway and reloading page...";
		    echo "</p>\n";
		    exec($remoteCommand);
		    echo "</td></tr>\n</table>\n";
		    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
		}
	    }
	    else {
	    // Output HTML
	    ?>
    		<div style="text-align:left;font-weight:bold;">XLX DMR Link Manager</div>
		<form action="./?func=xlx_man" method="post">
		    <table>
			<tr>
			    <th width="150"><a class="tooltip" href="#">Select Reflector<span><b>Select Reflector</b></span></a></th>
			    <th><a class="tooltip" href="#">Module<span><b>Module</b></span></a></th>
			    <th width="150"><a class="tooltip" href="#">Current Link<span><b>Current Link</b></span></a></th>
			    <th width="150"><a class="tooltip" href="#">Action<span><b>Action</b></span></a></th>
			    <th></th>
			</tr>
			<tr>
			<td><select name="xlxLinkHost" class="dmrMasterHost3Startup">
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
$target = `cd /var/log/pi-star; /usr/local/bin/RemoteCommand 7643 hosts | egrep -oh 'XLX(.*)\"' | awk {'print $1'} | sed 's/"//g' | sed 's/_/ Module /g'`; 
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
        		    <td style="white-space:normal;padding: 3px;">Select reflector "None" to unlink and pause all XLX DMR traffic.</td>
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
?>
