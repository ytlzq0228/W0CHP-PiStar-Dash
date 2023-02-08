<?php

if (!isset($_SESSION) || !is_array($_SESSION)) {
    session_id('pistardashsess');
    session_start();
}
// Load the language support
require_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';
// load config
require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';

if (file_exists('/etc/default/shellinabox')) {
  $getPortCommand = "grep -m 1 'SHELLINABOX_PORT=' /etc/default/shellinabox | awk -F '=' '/SHELLINABOX_PORT=/ {print $2}'";
  $shellPort = exec($getPortCommand);
}

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/advanced/ssh_access.php") {
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
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - SSH";?></title>
    <link rel="stylesheet" type="text/css" href="../css/pistar-css.php?version=<?php echo $versionCmd; ?>" />
    <link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
    <script type="text/javascript" src="/js/jquery.min.js?version=<?php echo $versionCmd; ?>"></script>
    <script type="text/javascript" src="/js/jquery-timing.min.js?version=<?php echo $versionCmd; ?>"></script>
  </head>
  <body>
    <div class="container">
    <div class="header">
    <div class="SmallHeader shLeft">Hostname: <?php echo exec('cat /etc/hostname'); ?></div>
    <div class="SmallHeader shRight">Pi-Star: Ver.#  <?php echo $_SESSION['PiStarRelease']['Pi-Star']['Version'].'<br />';?>
    <?php if (constant("AUTO_UPDATE_CHECK") == "true") { ?> 
    <div id="CheckUpdate"><?php echo $version; system('/usr/local/sbin/pistar-check4updates'); ?></div></div>
    <?php } else { ?>
    <div id="CheckUpdate"><?php echo $version; ?></div></div>
    <?php } ?>    
    <h1>Pi-Star <?php echo $lang['digital_voice']." ".$lang['dashboard_for']." ".$_SESSION['MYCALL']; ?> - SSH Access</h1>
        <p>
        <div class="navbar">
          <a class="menuconfig" href="/admin/configure.php">Configuration
          </a>
          <a class="menubackup" href="/admin/config_backup.php">Backup/Restore
          </a>
          <a class="menuupgrade" href="/admin/advanced/upgrade.php">Upgrade
          </a>
          <a class="menuupdate" href="/admin/update.php">Update
          </a>
          <a class="menuadmin" href="/admin/">Admin
          </a>
	  <?php if (file_exists("/etc/dstar-radio.mmdvmhost")) { ?>
	  <a class="menulive" href="/live/">Live Caller</a>
	  <?php } ?>
          <a class="menudashboard" href="/">Dashboard
          </a>
        </div>
	</p>
  </div>
  <div class="contentwide">
    <?php if (isset($shellPort)) {
      echo "<iframe src=\"http://".$_SERVER['HTTP_HOST'].":".$shellPort."\" style=\"border:1px solid #ffffff; background:#000; color:#00ff00; padding:5px;margin:5px;\" name=\"Pi-Star_SSH\" scrolling=\"no\" frameborder=\"0\" marginheight=\"0px\" marginwidth=\"0px\" height=\"600px\" width=\"860px\"></iframe>";
    }
    else {
      echo "SSH Feature not yet installed";
    } ?>
  <?php if (isset($shellPort)) { echo "<p><a href=\"//".$_SERVER['HTTP_HOST'].":".$shellPort."\" style=\"text-decoration:underline;color:inherit;\">Click here for full-screen SSH client</a></p>\n"; } ?>
  <div class="footer">
  Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  <a href="https://w0chp.net/w0chp-pistar-dash/" style="color: #ffffff; text-decoration:underline;">W0CHP-PiStar-Dash</a> enhancements by W0CHP
  <br />
  </div>
  </body>
  </html>

<?php
}
?>
