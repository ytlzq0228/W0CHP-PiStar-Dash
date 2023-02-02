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
if ($_SERVER["PHP_SELF"] == "/admin/expert/log_manager.php") {
    // Sanity Check Passed.
    header('Cache-Control: no-cache');

if (isset($_SESSION['CSSConfigs']['Background'])) {
    $backgroundModeCellActiveColor = $_SESSION['CSSConfigs']['Background']['ModeCellActiveColor'];
}

$log_backup_dir = "/home/pi-star/.backup-mmdvmhost-logs/";
$log_dir = "/var/log/pi-star/";
$status = exec('systemctl status mmdvm-log-backup.timer | grep masked');
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
	<head>
	    <meta name="robots" content="index" />
	    <meta name="robots" content="follow" />
	    <meta name="language" content="English" />
	    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	    <meta name="Author" content="Andrew Taylor (MW0MWZ), Chip Cuccio (W0CHP)" />
	    <meta name="Description" content="Pi-Star Power" />
	    <meta name="KeyWords" content="MMDVMHost,ircDDBGateway,D-Star,ircDDB,DMRGateway,DMR,YSFGateway,YSF,C4FM,NXDNGateway,NXDN,P25Gateway,P25,Pi-Star,DL5DI,DG9VH,MW0MWZ,W0CHP" />
	    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	    <meta http-equiv="pragma" content="no-cache" />
	    <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
	    <meta http-equiv="Expires" content="0" />
	    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']."";?> - MMDVM Log Manager</title>
	    <link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
	    <link rel="stylesheet" type="text/css" href="/css/pistar-css.php?version=<?php echo $versionCmd; ?>" />
        <script type="text/javascript" src="/js/jquery.min.js?version=<?php echo $versionCmd; ?>"></script>
        <script type="text/javascript" src="/js/functions.js?version=<?php echo $versionCmd; ?>"></script>
	</head>
	<body>
	    <div class="container">
<?php include './header-menu.inc'; ?>
		<div class="contentwide">
		    <?php if (!empty($_POST)) { ?>
			<table width="100%">
			    <tr><th colspan="2">MMDVM Log Manager</th></tr>
			    <?php
			    if ( escapeshellcmd($_POST["purge_logs"]) ) {
				   echo '<tr><td colspan="2"><br />Purging all logs...
				   <br />Page reloading...<br /><br />
				   <script language="JavaScript" type="text/javascript">
                                   setTimeout("location.href = \''.$_SERVER["PHP_SELF"].'\'", 3000);
				   </script>
				   </td></tr>';
				   exec ("sudo pistar-services fullstop");
				   exec ('sudo mount -o remount,rw /');
                                   exec ('sudo systemctl stop mmdvm-log-backup.timer');
                                   exec ('sudo systemctl stop mmdvm-log-backup.service');
                                   exec ('sudo systemctl stop mmdvm-log-restore.service');
                                   exec ('sudo systemctl stop mmdvm-log-shutdown.service');
				   exec ("sudo rm -rf $log_dir/* $log_backup_dir/* > /dev/null");
				   exec ("sudo pistar-services start");
                                   exec ('sudo systemctl restart mmdvm-log-backup.timer');
                                   exec ('sudo systemctl restart mmdvm-log-backup.service');
                                   exec ('sudo systemctl restart mmdvm-log-restore.service');
                                   exec ('sudo systemctl restart mmdvm-log-shutdown.service');
			    }
			    else if ( escapeshellcmd($_POST["disable_backups"]) ) {
				   echo '<tr><td colspan="2"><br />Disabling Automatic Log Backups...
				   <br />Page reloading...<br /><br />
				   <script language="JavaScript" type="text/javascript">
                                   setTimeout("location.href = \''.$_SERVER["PHP_SELF"].'\'", 3000);
				   </script>
				   </td></tr>';
				   exec ('sudo mount -o remount,rw /');
				   exec ('sudo systemctl stop mmdvm-log-backup.timer');
				   exec ('sudo systemctl stop mmdvm-log-backup.service');
				   exec ('sudo systemctl stop mmdvm-log-restore.service');
				   exec ('sudo systemctl stop mmdvm-log-shutdown.service');
				   exec ('sudo systemctl disable mmdvm-log-backup.timer');
				   exec ('sudo systemctl disable mmdvm-log-backup.service');
				   exec ('sudo systemctl disable mmdvm-log-restore.service');
				   exec ('sudo systemctl disable mmdvm-log-shutdown.service');
				   exec ('sudo systemctl mask mmdvm-log-backup.timer');
				   exec ('sudo systemctl mask mmdvm-log-backup.service');
				   exec ('sudo systemctl mask mmdvm-log-restore.service');
				   exec ('sudo systemctl mask mmdvm-log-shutdown.service');
				   exec ('sudo systemctl daemon-reload');
			    }
			    else if ( escapeshellcmd($_POST["enable_backups"]) ) {
				   echo '<tr><td colspan="2"><br />Enabling Automatic Log Backups...
				   <br />Page reloading...<br /><br />
				   <script language="JavaScript" type="text/javascript">
                                   setTimeout("location.href = \''.$_SERVER["PHP_SELF"].'\'", 3000);
				   </script>
				   </td></tr>';
				   exec ('sudo mount -o remount,rw /');
				   exec ('sudo systemctl unmask mmdvm-log-backup.timer');
				   exec ('sudo systemctl unmask mmdvm-log-backup.service');
				   exec ('sudo systemctl unmask mmdvm-log-restore.service');
				   exec ('sudo systemctl unmask mmdvm-log-shutdown.service');
				   exec ('sudo systemctl enable mmdvm-log-backup.timer');
				   exec ('sudo systemctl enable mmdvm-log-backup.service');
				   exec ('sudo systemctl enable mmdvm-log-restore.service');
				   exec ('sudo systemctl enable mmdvm-log-shutdown.service');
				   exec ('sudo systemctl daemon-reload');
				   exec ('sudo systemctl restart mmdvm-log-backup.timer');
				   exec ('sudo systemctl restart mmdvm-log-backup.service');
				   exec ('sudo systemctl restart mmdvm-log-restore.service');
				   exec ('sudo systemctl restart mmdvm-log-shutdown.service');
			    }
			    unset($_POST);
			    ?>
			</table>
		    <?php }
		    else { ?>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			    <table width="100%">
				<tr>
				    <th colspan="2">MMDVM Log Manager</th>
				</tr>
				<tr>
				    <th>Enable Auto Log Backups/Restores</th>
				    <th>Disable Auto Log Backups/Restores</th>
				</tr>
				<tr>
					<td>
						<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="backups_func">
							<label for="config">Enable Auto Backup Service:</label>
							<?php if (strpos($status, 'masked') == TRUE) { ?>
							<button type="submit" name="enable_backups" value="Enable">Enable</button>
							<?php } else { ?>
							<button type="submit" name="enable_backups" value="Enabled" disabled="disabled">Enabled</button>
							<?php } ?>
					</td>

					<td>
							<label for="configs">Disable Auto Backup Service:</label>
							<?php if (strpos($status, 'masked') != TRUE) { ?>
							<button type="submit" name="disable_backups" value="Disable">Disable</button>
							<?php } else { ?>
							<button type="submit" name="disable_backups" value="Disabled" disabled="disabled">Disabled</button>
							<?php } ?>
						</form>
					</td>
				</tr>
				<tr>
                                        <td style="white-space:normal;padding: 3px;">This will enable the automatic MMDVM log backup/restore on reboot functionality.</td>
                                        <td style="white-space:normal;padding: 3px;">This will DISABLE the automatic MMDVM log backup/restore on reboot functionality. Note that existing log backups will be retained.</td>
				</tr>
			</table>
		</form>
	<br />
	<br />

	<table>
		<tr>
			<th colspan="2">Purge All MMDVM Logs</th>
		</tr>
		<tr>
			<td colspan="2">
				<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="purgelogs">
					<label for="delete_configs">Purge All Logs:</label>
					<input style="background:crimson;color:white;" type="submit" name="purge_logs" value="Purge!">
				</form>
			</td>
		</tr>
                <tr>
                     <td style="white-space:normal;padding: 3px;">This will purge ALL MMDVM logs and start fresh; including current live logs and backup logs (if service enabled).</td>
                </tr>

	</table>
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
