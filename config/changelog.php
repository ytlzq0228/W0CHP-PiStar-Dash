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

require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/config/changelog.php") {
    // Sanity Check Passed.
    header('Cache-Control: no-cache');
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
	    <title>W0CHP-PiStar Dash ChangeLog</title>
	    <link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/config/browserdetect.php'; ?>
        <script type="text/javascript" src="/js/jquery.min.js?version=<?php echo $versionCmd; ?>"></script>
        <script type="text/javascript" src="/js/functions.js?version=<?php echo $versionCmd; ?>"></script>
        <script type="text/javascript">
          $.ajaxSetup({ cache: false });
        </script>
<style type="text/css">
.cl_wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  min-height: 100vh;
}

.ChangeLogData {
  font-size: 1em;
  padding: 1em;
  font-family: 'Inconsolata', monospace;
  background-color: black;
  color: lightgray;
  text-align: left;
  width: 75%
}

.foreground-1 { color: #ff002f; }
.foreground-2 { color: #30fe00; }
.foreground-3 { color: #e3ff00; }
.foreground-4 { color: #4d4dff; font-weight:bold; }
.foreground-5 { color: #ff32ff; }
.foreground-6 { color: #00ffff; }
.foreground-7 { color: white; }

.bold.foreground-1 { color: #ff002f; font-weight:bold; }
.bold.foreground-2 { color: #30fe00; font-weight:bold; }
.bold.foreground-3 { color: #e3ff00; font-weight:bold; }
.bold.foreground-4 { color: #4d4dff; font-weight:bold; }
.bold.foreground-5 { color: #ff32ff; font-weight:bold; }
.bold.foreground-6 { color: #00ffff; font-weight:bold; }
.bold.foreground-7 { color: white; font-weight:bold; }

</style>
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
		    <h1><code>W0CHP-PiStar-Dash</code><br />ChangeLog</h1>
		    <p>
			<div class="navbar">
			    <a class="menuconfig" href="/admin/configure.php"><?php echo $lang['configuration'];?></a>
			    <a class="menubackup" href="/admin/config_backup.php"><?php echo $lang['backup_restore'];?></a>
			    <a class="menuupdate" href="/admin/update.php"><?php echo $lang['update'];?></a>
			    <a class="menuadmin" href="/admin/"><?php echo $lang['admin'];?></a>
			    <?php if (file_exists("/etc/dstar-radio.mmdvmhost")) { ?>
			    <a class="menulive" href="/live/">Live Caller</a>
			    <?php } ?>
			    <a class="menudashboard" href="/"><?php echo $lang['dashboard'];?></a>
			</div>
		    </p>
		</div>
		<div class="contentwide">

                  <div class="divTable">
                    <div class="divTableBody">
                      <div class="divTableRow">
                        <div class="divTableCellSans">
                          <p><b>The Last 20 Changes/Commits of the Dashboard Code:</b></p>
                            <div class="cl_wrapper">
			      <div class="ChangeLogData"> 
				<?php
				  $uaStr="WPSD-ChangeLog-Viewer";
				  @exec("curl --fail -s -o /dev/null https://repo.w0chp.net/WPSD-Dev/W0CHP-PiStar-Dash/info/refs?service=git-upload-pack --user-agent $uaStr");
				  $out = shell_exec('/usr/local/bin/WPSD-CL-to-html');
				  $out = str_replace("\n", "<br />", $out);
				  echo $out;
				?>
			      </div>
			    </div>
			    <p style="text-align:center;font-weight:bold;">
			      <a href="https://repo.w0chp.net/WPSD-Dev/W0CHP-PiStar-Dash/graph?branch=refs%2Fheads%2Fmaster" target="_new" style="text-decoration:underline;color:inherit;">View the entire change/commit history...</a>
			    </p>
			</div>
		      </div>
		    </div>
		  </div>
		</div>
		
		<div class="footer">
		    Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
			<a href="https://w0chp.net/w0chp-pistar-dash/" style="color: #ffffff; text-decoration:underline;">W0CHP-PiStar-Dash</a> by W0CHP<br />
		</div>
	    </div>
	</body>
    </html>
<?php
}
?>
