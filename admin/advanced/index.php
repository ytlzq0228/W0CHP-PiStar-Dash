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
	<title>Pi-Star - Digital Voice Dashboard - Advanced Area</title>
	<link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/config/browserdetect.php'; ?>
    </head>
    <body>
	<div class="container">
<?php
$config_dir = "/etc/WPSD_config_mgr";
if (!is_dir($config_dir) || count(glob("$config_dir/*")) < 1) {
?>
<div>
  <table align="center"style="margin: 0px 0px 10px 0px; width: 100%;border-collapse:collapse; table-layout:fixed;white-space: normal!important;">
    <tr>
    <td align="center" valign="top" style="background-color: #ffff90; color: #906000; word-wrap: break-all;padding:20px;">Notice! You do not have any saved configurations / profiles.<br /><br />
    It is recommended that you <b><a href="/admin/advanced/config_manager.php">save your configuration / profile before making any changes</a>.</b></td>
    </tr>
  </table>
</div>
<?php } ?>
<?php
                // check that no modes are paused. If so, bail and direct user to unpause...
                $is_paused = glob('/etc/*_paused');
                $repl_str = array('/\/etc\//', '/_paused/');
                $paused_modes = preg_replace($repl_str, '', $is_paused);
                if (!empty($is_paused)) {
                    //HTML output starts here
                    include './header-menu-disabled.inc';
		    echo '            <div class="contentwide">

              <div class="divTable">
                <div class="divTableBody">
                  <div class="divTableRow">
                    <div class="divTableCellSans">
                    <h2 style="color:inherit;">Advanced Editors &amp; Tools</h2>';
                    echo '<h1>IMPORTANT:</h1>';
                    echo '<p><b>One or more modes have been detected to have been "paused" by you</b>:</p>';
                    foreach($paused_modes as $mode) {
                        echo "<h2>$mode</h2>";
                    }
                    echo '<p>You must "resume" all of the modes you have paused in order to make any configuration changes...</p>';
                    echo '<p>Go the <a href="/admin/?func=mode_man" style="text-decoration:underline;color:inherit;">Instant Mode Manager page to Resume the paused mode(s)</a>. Once that\'s completed, this configuration page will be enabled.</p>';
                    echo '<br />'."\n";
		    echo '                </div>
              </div>
            </div>
          </div>

        </div>
            <div class="footer">
                Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-'.date("Y").'.<br />
                <a href="https://w0chp.net/w0chp-pistar-dash/" style="color: #ffffff; text-decoration:underline;">W0CHP-PiStar-Dash</a> by W0CHP<br />
            </div>

        </div>
    </body>
</html>';
                    die();
} else {
	    include './header-menu.inc';
?>
            <div class="contentwide">

	      <div class="divTable">
		<div class="divTableBody">
		  <div class="divTableRow">
		    <div class="divTableCellSans">
		    <h2 style="color:inherit;">Advanced Editors &amp; Tools</h2>
		    <h3>**WARNING**</h3>
            		<p>
			Advanced  editors &amp; tools have been created to make editing some of the extra settings in the<br />
			config files more simple, allowing you to update some areas of the config files without the<br />
			need to login to your Pi over SSH.<br />
			<br />
			Please keep in mind when making your edits here, that these config files can be updated by<br />
			the dashboard, and that your edits can be over-written. It is assumed that you already know<br />
			what you are doing editing the files by hand, and that you understand what parts of the files<br />
			are maintained by the dashboard.<br />
			<br />
			With that warning in mind, you are free to make any changes you like by accessing the advanced areas
			in the upper-left-hand menus. <br />
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

} // end paused mode check

?>
