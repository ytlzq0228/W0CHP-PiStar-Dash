<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
require_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/live_log.php") {
    
    // Sanity Check Passed.
    header('Cache-Control: no-cache');
    
    if (!isset($_GET['ajax'])) {
	unset($_SESSION['offset']);
	//$_SESSION['offset'] = 0;
    }

   $log = $_GET['log'];

    switch ($log) {
	case "MMDVMHost":
	    $logfile = "/var/log/pi-star/MMDVM-".gmdate('Y-m-d').".log";
	    break;
	case "DStarRepeater":
	    $logfile = "/var/log/pi-star/DStarRepeater-".gmdate('Y-m-d').".log";
	    break;
	case "DMRGateway":
	    $logfile = "/var/log/pi-star/DMRGateway-".gmdate('Y-m-d').".log";
	    break;
	case "YSFGateway":
	    $logfile = "/var/log/pi-star/YSFGateway-".gmdate('Y-m-d').".log";
	    break;
	case "ircDDBGateway":
	    $logfile = "/var/log/pi-star/ircDDBGateway-".gmdate('Y-m-d').".log";
	    break;
	case "P25Gateway":
	    $logfile = "/var/log/pi-star/P25Gateway-".gmdate('Y-m-d').".log";
	    break;
	case "NXDNGateway":
	    $logfile = "/var/log/pi-star/NXDNGateway-".gmdate('Y-m-d').".log";
	    break;
	case "M17Gateway":
	    $logfile = "/var/log/pi-star/M17Gateway-".gmdate('Y-m-d').".log";
	    break;
	case "DAPNETGateway":
	    $logfile = "/var/log/pi-star/DAPNETGateway-".gmdate('Y-m-d').".log";
	    break;
    }
    
    if (isset($_GET['ajax'])) {
	if (empty($logfile) || !file_exists($logfile)) {
	    exit();
	}
	
	$handle = fopen($logfile, 'rb');
	if (isset($_SESSION['offset'])) {
	    fseek($handle, 0, SEEK_END);
	    if ($_SESSION['offset'] > ftell($handle)) { //log rotated/truncated
		$_SESSION['offset'] = 0; //continue at beginning of the new log
	    }
	    $data = stream_get_contents($handle, -1, $_SESSION['offset']);
	    $data = wordwrap($data, "200", "\n");
	    $_SESSION['offset'] += strlen($data);
	    echo nl2br($data);
	}
	else {
	    fseek($handle, 0, SEEK_END);
	    $_SESSION['offset'] = ftell($handle);
	} 
	exit();
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
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - ".$lang['live_logs'];?></title>
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/config/browserdetect.php'; ?>
    <link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
    <script type="text/javascript" src="/js/jquery.min.js?version=<?php echo $versionCmd; ?>"></script>
    <script type="text/javascript" src="/js/jquery-timing.min.js?version=<?php echo $versionCmd; ?>"></script>
    <script type="text/javascript">
    $(function() {
      $.repeat(1000, function() {
        $.get('/admin/live_log.php?log=<?php echo "$log";?>&ajax', function(data) {
          if (data.length < 1) return;
          var objDiv = document.getElementById("tail");
          var isScrolledToBottom = objDiv.scrollHeight - objDiv.clientHeight <= objDiv.scrollTop + 1;
          $('#tail').append(data);
          if (isScrolledToBottom)
            objDiv.scrollTop = objDiv.scrollHeight;
        });
      });
    });
    </script>
  </head>
  <body>
      <div class="container">
	  <div class="header">
	      <div class="SmallHeader shLeft">Hostname: <?php echo exec('cat /etc/hostname'); ?></div><div class="SmallHeader shRight">Pi-Star: Ver.#  <?php echo $_SESSION['PiStarRelease']['Pi-Star']['Version'].'<br />';?>
	      <?php if (constant("AUTO_UPDATE_CHECK") == "true") { ?> 
	      <div id="CheckUpdate"><?php echo $version; system('/usr/local/sbin/pistar-check4updates'); ?></div></div>
	      <?php } else { ?>
	      <div id="CheckUpdate"><?php echo $version; ?></div></div>
	      <?php } ?>    
	      <h1>Pi-Star <?php echo $lang['digital_voice']." - ".$lang['live_logs'];?></h1>
	      <p>
		  <div class="navbar">
              <script type= "text/javascript">
               $(document).ready(function() {
                 setInterval(function() {
                   $("#timer").load("/includes/datetime.php");
                   }, 1000);

                 function update() {
                   $.ajax({
                     type: 'GET',
                     cache: false,
                     url: '/includes/datetime.php',
                     timeout: 1000,
                     success: function(data) {
                       $("#timer").html(data); 
                       window.setTimeout(update, 1000);
                     }
                   });
                 }
                 update();
               });
              </script>
              <div style="text-align: left; padding-left: 8px; padding-top: 5px; float: left;"> 
                <span id="timer"></span>
            </div>
		      <a class="menuconfig" href="/admin/configure.php"><?php echo $lang['configuration'];?></a>
		      <a class="menubackup" href="/admin/config_backup.php"><?php echo $lang['backup_restore'];?></a>
		      <a class="menupower" href="/admin/power.php"><?php echo $lang['power'];?></a>
		      <a class="menuadmin" href="/admin/"><?php echo $lang['admin'];?></a>
		      <?php if (file_exists("/etc/dstar-radio.mmdvmhost")) { ?>
		      <a class="menulive" href="/live/">Live Caller</a>
		      <?php } ?>
		      <a class="menudashboard" href="/"><?php echo $lang['dashboard'];?></a>
		  </div>
	      </p>
	  </div>
	  <div class="contentwide">
	      <table width="100%">

  <?php if (!isset($_GET['log'])) { ?>
  <tr><th colspan="2"><?php echo $lang['live_logs'];?></th></tr>
  <tr><td>
  <form method="get">
   <b>Select a log to view:</b>
    <select name="log" value="log">
	<option name="MMDVMHost">MMDVMHost</option>
	<option name="DStarRepeater">DStarRepeater</option>
	<option name="ircDDBGateway">ircDDBGateway</option>
	<option name="DMRGateway">DMRGateway</option>
	<option name="YSFGateway">YSFGateway</option>
	<option name="P25Gateway">P25Gateway</option>
	<option name="NXDNGateway">NXDNGateway</option>
	<option name="M17Gateway">M17Gateway</option>
	<option name="DAPNETGateway">DAPNETGateway</option>
    </select>
    <input type="submit" name="sumbit" value="Select" />
  </form>
  </td>
   <td>
    <button class="button" onclick="location.href='/admin/download_all_logs.php'" style="margin:2px 5px;">Download All Logs</button>
  </td>
  </tr>
  </table>
  </div>
  <div class="footer">
  Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  <a href="https://w0chp.net/w0chp-pistar-dash/" style="color: #ffffff; text-decoration:underline;">W0CHP-PiStar-Dash</a> enhancements by W0CHP
  <br />
  </div>
  </div>
  </body>
  <?php } else { ?>
  <tr><th colspan=2"><?php echo $lang['live_logs']; echo " - $log";?></th></tr>
  <tr><td>
  <form method="get">
  <b>Select a log to view:</b>
    <select name="log" value="log">
	<option name="MMDVMHost" <?php if ($log == "MMDVMHost") { echo "selected='selected'"; } ?>>MMDVMHost</option>
	<option name="DStarRepeater" <?php if ($log == "DStarRepeater") { echo "selected='selected'"; } ?>>DStarRepeater</option>
        <option name="ircDDBGateway" <?php if ($log == "ircDDBGateway") { echo "selected='selected'"; } ?>>ircDDBGateway</option>
        <option name="DMRGateway" <?php if ($log == "DMRGateway") { echo "selected='selected'"; } ?>>DMRGateway</option>
        <option name="YSFGateway" <?php if ($log == "YSFGateway") { echo "selected='selected'"; } ?>>YSFGateway</option>
        <option name="P25Gateway" <?php if ($log == "P25Gateway") { echo "selected='selected'"; } ?>>P25Gateway</option>
        <option name="NXDNGateway" <?php if ($log == "NXDNGateway") { echo "selected='selected'"; } ?>>NXDNGateway</option>
        <option name="M17Gateway" <?php if ($log == "M17Gateway") { echo "selected='selected'"; } ?>>M17Gateway</option>
        <option name="DAPNETGateway" <?php if ($log == "DAPNETGateway") { echo "selected='selected'"; } ?>>DAPNETGateway</option>
    </select>
    <input type="submit" name="sumbit" value="Select" />
  </form>
  </td>
  <td>
   <?php if (file_exists($logfile)) { ?>
   <button class="button" onclick="location.href='/admin/download_log.php?log=<?php echo $log;?>'" style="margin:2px 5px;">Download This Log</button>
   <?php } ?>
   <button class="button" onclick="location.href='/admin/download_all_logs.php'" style="margin:2px 5px;">Download All Logs</button>
  </td>
  </tr>

		    <?php
		    if (!file_exists($logfile)) {
			print '<tr><td colspan="2"><div id="tail">';
			print "<p><b>File `$logfile` not found!</b></p>";
		    } else {
		    ?>
		  <tr><td colspan="2" align="left"><div id="tail">Log viewer will populate once there is modem/network/RF activity. Please wait...<br />
		  </div></td></tr>
		    <?php } ?>
	      </table>
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
}
?>
