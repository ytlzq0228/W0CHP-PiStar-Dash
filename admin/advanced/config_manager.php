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

// Load the language support
require_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/advanced/config_manager.php") {
    // Sanity Check Passed.
    header('Cache-Control: no-cache');

if (isset($_SESSION['CSSConfigs']['Background'])) {
    $backgroundModeCellActiveColor = $_SESSION['CSSConfigs']['Background']['ModeCellActiveColor'];
}

$config_dir = "/etc/WPSD_config_mgr";
$curr_config = trim(file_get_contents('/etc/.WPSD_config'));
$saved = date("M. d Y @ h:i A", filemtime("$config_dir" . "/". "$curr_config"));
if (file_exists('/etc/.WPSD_config') && count(glob("$config_dir/*")) > 0) {
    if (is_dir("$config_dir" . "/" ."$curr_config") != false ) {
    	 $curr_config = "<span style='color:$backgroundModeCellActiveColor;'>".trim(file_get_contents('/etc/.WPSD_config'))."</span><br /><small>(Saved: ".$saved."</small>)";
    } else {
	$curr_config = "Current Config Deleted! You may want to restore a saved config, or save a new config.";
    }
} else {
    $curr_config = "No saved configs yet!";
}
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
	<head>
	    <meta name="robots" content="index" />
	    <meta name="robots" content="follow" />
	    <meta name="language" content="English" />
	    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	    <meta http-equiv="pragma" content="no-cache" />
	    <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
	    <meta http-equiv="Expires" content="0" />
	    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']."";?> - Configuration/Profile Manager</title>
	    <link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/config/browserdetect.php'; ?>
	    <script type="text/javascript" src="/js/jquery.min.js?version=<?php echo $versionCmd; ?>"></script>
	    <script type="text/javascript" src="/js/functions.js?version=<?php echo $versionCmd; ?>"></script>
	</head>
	<body>
	    <div class="container">
<?php include './header-menu.inc'; ?>
		<div class="contentwide">
		    <?php if (!empty($_POST)) { ?>
			<table width="100%">
			    <tr><th colspan="3">Configuration / Profile Manager</th></tr>
			    <?php
			    if ( escapeshellcmd($_POST["save_current_config"]) ) {
				if (!ctype_alnum($_POST['config_desc'])) {
				   echo '<tr><td colspan="3"><br />No Spaces nor Non-Alpha-Numeric Characters are Permitted...
				   <br />Page reloading...<br /><br />
				   <script language="JavaScript" type="text/javascript">
                                   setTimeout("location.href = \''.$_SERVER["PHP_SELF"].'\'", 3000);
				   </script>
				   </td></tr>';
				} else {
				   $desc = $_POST['config_desc'];
				   echo '<tr><td colspan="3"><br />Saving Current Config, "'.$desc.'"...
				   <br />Page reloading...<br /><br />
				   <script language="JavaScript" type="text/javascript">
                                   setTimeout("location.href = \''.$_SERVER["PHP_SELF"].'\'", 3000);
				   </script>
				   </td></tr>';
				   exec('sudo mount -o remount,rw /');
				   exec("sudo mkdir -p /etc/WPSD_config_mgr/$desc > /dev/null");
				   $backupDir = "/etc/WPSD_config_mgr/$desc";
                            	   exec("sudo rm -rf $backupDir > /dev/null")."\n";
                            	   exec("sudo mkdir $backupDir > /dev/null")."\n";
                            	   if (exec('cat /etc/dhcpcd.conf | grep "static ip_address" | grep -v "#"')) {
                                       exec("sudo cp /etc/dhcpcd.conf $backupDir > /dev/null")."\n";
                            	   }
                            	   exec("sudo cp /etc/wpa_supplicant/wpa_supplicant.conf $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/hostapd/hostapd.conf $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/pistar-css.ini $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/aprsgateway $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/ircddbgateway $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/mmdvmhost $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/dstarrepeater $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/dapnetgateway $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/pistar-css.ini $backupDir > /dev/null");
                            	   exec("sudo cp /etc/p25gateway $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/ysfgateway $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/dmr2nxdn $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/dmr2ysf $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/nxdn2dmr $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/ysf2dmr $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/dgidgateway $backupDir > /dev/null");
                            	   exec("sudo cp /etc/nxdngateway $backupDir > /dev/null");
                            	   exec("sudo cp /etc/m17gateway $backupDir > /dev/null");
                            	   exec("sudo cp /etc/ysf2nxdn $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/ysf2p25 $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/dmrgateway $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/starnetserver $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/timeserver $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/dstar-radio.* $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/pistar-remote $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/hosts $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/hostname $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/bmapi.key $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/dapnetapi.key $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/default/gpsd $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/*_paused $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/.CALLERDETAILS $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/.pistar-css.ini.user $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/.TGNAMES $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /etc/.SHOWDMRTA $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /usr/local/etc/RSSI.dat $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /var/www/dashboard/config/ircddblocal.php $backupDir > /dev/null")."\n";
                            	   exec("sudo cp /var/www/dashboard/config/config.php $backupDir > /dev/null")."\n";
			    	   exec("sudo cp /var/www/dashboard/config/language.php $backupDir > /dev/null")."\n";
				   exec("sudo sh -c 'cp -a /root/*Hosts.txt' $backupDir > /dev/null")."\n";
				   exec("sudo sh -c \"echo $desc > /etc/.WPSD_config\"");
				}
			    }
			    else if ( escapeshellcmd($_POST["restore_config"]) ) {
				   $backupDir = '/etc/WPSD_config_mgr/'.$_POST['configs'].'';
				   echo '<tr><td colspan="3"><br />Restoring and Applying Config, "' .$_POST['configs'].'"...
				   <br />Page reloading...<br /><br />
				   <script language="JavaScript" type="text/javascript">
                                   setTimeout("location.href = \''.$_SERVER["PHP_SELF"].'\'", 3000);
				   </script>
				   </td></tr>';
				   exec('sudo mount -o remount,rw /');
				   exec("sudo sh -c 'mv $backupDir/*.php /var/www/dashboard/config/' > /dev/null");
				   exec("sudo sh -c 'cp -a $backupDir/*Hosts.txt /root/' > /dev/null");
				   exec("sudo sh -c 'rm -rf $backupDir/*Hosts.txt' > /dev/null");
				   exec("sudo sh -c 'cp -a $backupDir/* /etc/' > /dev/null");
				   exec("sudo sh -c 'cp -a $backupDir/.CALLERDETAILS /etc/' > /dev/null");
				   exec("sudo sh -c 'cp -a $backupDir/.TGNAMES /etc/' > /dev/null");
				   exec("sudo sh -c 'cp -a $backupDir/.SHOWDMRTA /etc/' > /dev/null");
				   exec("sudo sh -c 'cp -a $backupDir/.pistar-css.ini.user /etc/' > /dev/null");
                                   exec("sudo cp /var/www/dashboard/config/ircddblocal.php $backupDir > /dev/null")."\n";
                                   exec("sudo cp /var/www/dashboard/config/config.php $backupDir > /dev/null")."\n";
                                   exec("sudo cp /var/www/dashboard/config/language.php $backupDir > /dev/null")."\n";
				   exec("sudo chown www-data:www-data /var/www/dashboard/ > /dev/null");
				   exec("sudo sh -c 'cp -a /root/*Hosts.txt $backupDir' > /dev/null");
				   exec("sudo sh -c \"echo ".$_POST['configs']." > /etc/.WPSD_config\"");
				   exec("sudo pistar-services restart > /dev/null &");
			    }
			    else if ( escapeshellcmd($_POST["remove_config"]) ) {
				   echo '<tr><td colspan="3"><br />Deleting Config, "' .$_POST['delete_configs'].'"...
				   <br />Page reloading...<br /><br />
				   <script language="JavaScript" type="text/javascript">
                                   setTimeout("location.href = \''.$_SERVER["PHP_SELF"].'\'", 3000);
				   </script>
				   </td></tr>';
				   exec('sudo mount -o remount,rw /');
				   exec("sudo rm -rf /etc/WPSD_config_mgr/".$_POST['delete_configs']." > /dev/null");
			    }
			    unset($_POST);
			    ?>
			</table>
		    <?php }
		    else { ?>
                        <?php
                        // check that no modes are paused. If so, bail and direct user to unpause...
                        $is_paused = glob('/etc/*_paused');
                        $repl_str = array('/\/etc\//', '/_paused/');
                        $paused_modes = preg_replace($repl_str, '', $is_paused);
                        if (!empty($is_paused)) {
                                echo '<h1>IMPORTANT:</h1>';
                                echo '<p><b>One or more modes have been detected to have been "paused" by you</b>:</p>';
                                foreach($paused_modes as $mode) {
                                        echo "<h3>$mode</h3>";
                                }
                                echo '<p>You must "resume" all of the modes you have paused in order to make any configuration changes...</p>';
                                echo '<p>Go the <a style="text-decoration:underline;color:inherit;" href="/admin/?func=mode_man">Instant Mode Manager page to Resume the paused mode(s)</a>. Once that\'s completed, this configuration page will be enabled.</p>';
                                echo '<br />'."\n";
                                echo '<br />';
                        } else {
                        ?>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			    <table width="100%">
				<tr>
				    <th colspan="3">Configuration / Profile Manager</th>
				</tr>
				<tr>
				    <th>Current Running Config / Profile</th>
				    <th>Save Current Config</th>
                                    <th>Restore Config / Profile</th>
				</tr>
				<tr>
				  <td style="white-space:normal;"><?php echo $curr_config; ?></td>
					<td>
						<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="save_config">
							<label for="config">Description:</label>
							<input type="text" placeholder="Enter Short Description" name="config_desc" size="27" maxlength="27">
							<input type="submit" name="save_current_config" value="Save Config/Profile">
						</form>
					</td>

					<td>
						<?php
						if (count(glob("$config_dir/*")) == 0) {
						?>
							No saved configs yet!
						<?php } else { ?>
						<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="list_configs">
							<label for="configs">Select:</label>
							<select name="configs" id="configs" form="list_configs">
							<?php
							foreach ( glob("$config_dir/*") as $dir ) {
								$config_file = str_replace("$config_dir/", "", $dir);
								echo "<option name='selected_config' value='$config_file'>$config_file</option>";
							}
							?>
							</select>
							<input type="submit" name="restore_config" value="Restore &amp; Apply Config/Profile">
						</form>
						<?php } ?>
					</td>
				</tr>

				<tr>
					<td colspan="3" style="white-space:normal;padding: 3px;">This function allows you save multiple versions and configurations ("profiles") of your setup;  and then restore/re-apply them as-needed for different uses, etc.<br />Restoring and re-applying a configuration / profile is instant.</td>
				</tr>
			</table>
		</form>
	<br />
	<br />

	<table>
		<tr>
			<th colspan="3">Delete a Saved Config / Profile</th>
		</tr>
		<tr>
			<td colspan="3">
                                <?php
                                if (count(glob("$config_dir/*")) == 0) {
                                ?>
				No saved configs yet!
				<?php } else { ?>
				<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="del_configs">
					<label for="delete_configs">Select:</label>
					<select name="delete_configs" id="configs" form="del_configs">
				<?php
				foreach ( glob("$config_dir/*") as $dir ) {
					$config_file = str_replace("$config_dir/", "", $dir);
					echo "<option name='selected_config' value='$config_file'>$config_file</option>";
				}
				?>
					</select>
					<input style="background:crimson;color:white;" type="submit" name="remove_config" value="Delete Config/Profile">
				</form>
				<?php } ?>
			</td>
		</tr>
	</table>
	<?php } ?>
    <?php } ?>

		</div>
		<div class="footer">
		    Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
			<a href="https://w0chp.net/w0chp-pistar-dash/" style="color: #ffffff; text-decoration:underline;">W0CHP-PiStar-Dash</a> enhancements by W0CHP<br />
		</div>
	    </div>
	</body>
    </html>
<?php
}
?>
