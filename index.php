<?php
session_set_cookie_params(0, "/");
session_name("PiStar Dashboard Session");
session_id('pistardashsess');
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';

$MYCALL = strtoupper($callsign);
$_SESSION['MYCALL'] = $MYCALL;

// Clear session data (page {re}load);
unset($_SESSION['BMAPIKey']);
unset($_SESSION['DAPNETAPIKeyConfigs']);
unset($_SESSION['PiStarRelease']);
unset($_SESSION['MMDVMHostConfigs']);
unset($_SESSION['ircDDBConfigs']);
unset($_SESSION['DStarRepeaterConfigs']);
unset($_SESSION['DMRGatewayConfigs']);
unset($_SESSION['YSFGatewayConfigs']);
unset($_SESSION['DGIdGatewayConfigs']);
unset($_SESSION['DAPNETGatewayConfigs']);
unset($_SESSION['YSF2DMRConfigs']);
unset($_SESSION['YSF2NXDNConfigs']);
unset($_SESSION['YSF2P25Configs']);
unset($_SESSION['DMR2YSFConfigs']);
unset($_SESSION['DMR2NXDNConfigs']);
unset($_SESSION['APRSGatewayConfigs']);
unset($_SESSION['NXDNGatewayConfigs']);
unset($_SESSION['P25GatewayConfigs']);
unset($_SESSION['CSSConfigs']);
unset($_SESSION['DvModemFWVersion']);
unset($_SESSION['DvModemTCXOFreq']);
checkSessionValidity();

if (isset($_SESSION['CSSConfigs']['Text'])) {
    $textSections = $_SESSION['CSSConfigs']['Text']['TextSectionColor'];
}
if(empty($_GET['func'])) {
    $_GET['func'] = "main";
}
if(empty($_POST['func'])) {
    $_POST['func'] = "main";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
    <head>
	<meta name="robots" content="index" />
	<meta name="robots" content="follow" />
	<meta name="language" content="English" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php echo "<meta name=\"generator\" content=\"$version\" />\n"; ?>
	<meta name="Description" content="Pi-Star Dashboard" />
	<meta name="KeyWords" content="MMDVMHost,ircDDBGateway,D-Star,ircDDB,DMRGateway,DMR,YSFGateway,YSF,C4FM,NXDNGateway,NXDN,P25Gateway,P25,Pi-Star,DL5DI,DG9VH,MW0MWZ,W0CHP" />
	<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="pragma" content="no-cache" />
	<link rel="shortcut icon" href="/images/favicon.ico?version=<?php echo $versionCmd; ?>" type="image/x-icon" />
	<title><?php echo "$MYCALL"." - ".$lang['digital_voice']." ".$lang['dashboard'];?></title>
	<link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css?version=<?php echo $versionCmd; ?>" />
	<?php include_once "config/browserdetect.php"; ?>
	<script type="text/javascript" src="/js/jquery.min.js?version=<?php echo $versionCmd; ?>"></script>
	<script type="text/javascript" src="/js/functions.js?version=<?php echo $versionCmd; ?>"></script>
	<script type="text/javascript">
	 $.ajaxSetup({ cache: false });
	</script>
        <link href="/js/select2/css/select2.min.css?version=<?php echo $versionCmd; ?>" rel="stylesheet" />
        <script src="/js/select2/js/select2.full.min.js?version=<?php echo $versionCmd; ?>"></script>
        <script src="/js/select2/js/select2-searchInputPlaceholder.js?version=<?php echo $versionCmd; ?>"></script>
        <script type="text/javascript">
          $(document).ready(function() {
            $('.ysfLinkHost').select2({searchInputPlaceholder: 'Search...'});
            $('.p25LinkHost').select2({searchInputPlaceholder: 'Search...'});
            $('.nxdnLinkHost').select2({searchInputPlaceholder: 'Search...'});
            $(".RefName").select2({
              tags: true,
              dropdownAutoWidth : true,
              createTag: function (params) {
                return {
                  id: params.term,
                  text: params.term,
                  newOption: true
                }
              },
              templateResult: function (data) {
                var $result = $("<span></span>");

                $result.text(data.text);

                if (data.newOption) {
                  $result.append(" <em>(Search existing, or enter and save custom reflector value.)</em>");
                }

                return $result;
              }
            });
            $('.dmrMasterHost3').select2();
            $('.dmrMasterHost3Startup').select2({searchInputPlaceholder: 'Search...'});
            $('.ModSel').select2();
            $('.M17Ref').select2({searchInputPlaceholder: 'Search...'});
          });
          $(document).ready(function(){
            setInterval(function(){
                $("#CheckUpdate").load(window.location.href + " #CheckUpdate" );
                },10000);
            });
          $(document).ready(function(){
            setInterval(function(){
                $("#CheckMessage").load(window.location.href + " #CheckMessage" );
                },3600000);
            });
          $(document).ready(function() {
            $('.menuhwinfo').click(function() {
              $(".hw_toggle").slideToggle(function() {
                localStorage.setItem('hwinfo_visible', $(this).is(":visible"));
              })
            });
            $('.hw_toggle').toggle(localStorage.getItem('hwinfo_visible') === 'true');
          });
	  jQuery(document).ready(function() {
            jQuery('#lh_details').click(function(){
              jQuery('#lh_info').slideToggle('slow');
              if(jQuery(this).text() == 'Hide Last Heard...'){
                  jQuery(this).text('Display Last Heard...');
              } else {
                  jQuery(this).text('Hide Last Heard...');
             }
            });
          });
	</script>
    </head>
    <body>
	<div class="container">
	    <div class="header">
               <div class="SmallHeader shLeft"><a style="border-bottom: 1px dotted;" class="tooltip" href="#"><?php echo $lang['hostname'].": ";?> <span><strong>System IP Address<br /></strong><?php echo str_replace(',', ',<br />', exec('hostname -I'));?> </span>  <?php echo exec('cat /etc/hostname'); ?></a></div>
		<div class="SmallHeader shRight">Pi-Star: Ver.#  <?php echo $_SESSION['PiStarRelease']['Pi-Star']['Version'].'<br />';?>
		<?php if (constant("AUTO_UPDATE_CHECK") == "true") { ?>
		<div id="CheckUpdate"><?php echo $version; system('/usr/local/sbin/pistar-check4updates'); ?></div></div>
		<?php } else { ?>
		<div id="CheckUpdate"><?php echo $version; ?></div></div>
		<?php } ?>
		<h1>Pi-Star <?php echo $lang['digital_voice']." ".$lang['dashboard_for']." <code style='font-weight:550;'>".$_SESSION['MYCALL']."</code>"; ?></h1>
		<div id="CheckMessage">
		<?php
		    include('config/messages.php');
		?>
		</div>

		<p>
 		<div class="navbar">
                <script type= "text/javascript">
                 $(document).ready(function() {
                   setInterval(function() {
                     $("#timer").load("/dstarrepeater/datetime.php");
                     }, 1000);

                   function update() {
                     $.ajax({
                       type: 'GET',
                       cache: false,
                       url: '/dstarrepeater/datetime.php',
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
			<?php if ($_SERVER["PHP_SELF"] == "/admin/index.php") {
			    echo ' <a class="menuupdate" href="/admin/update.php">'.$lang['update'].'</a>'."\n";
			    echo ' <a class="menuexpert" href="/admin/expert/">Expert</a>'."\n";
			    echo ' <a class="menupower" href="/admin/power.php">'.$lang['power'].'</a>'."\n";
			    echo ' <a class="menusysinfo" href="/admin/sysinfo.php">System  Details</a>'."\n";
			    echo ' <a class="menulogs" href="/admin/live_modem_log.php">'.$lang['live_logs'].'</a>'."\n";
			} ?>
			<a class="menuadmin" href="/admin/"><?php echo $lang['admin'];?></a>
			<?php if (file_exists("/etc/dstar-radio.mmdvmhost")) { ?>
			<a class="menulive" href="/live/">Live Caller</a>
			<?php } ?>
			<a class="menuhwinfo" href='#'>Toggle SysInfo</a>
			<a class="menudashboard" href="/"><?php echo $lang['dashboard'];?></a>
		    </div> 
		</p>
	    </div>

	    <?php
	    // Check if config files need updating but supress if new installation
	    if (($_SERVER["PHP_SELF"] == "/admin/index.php") || ($_SERVER["PHP_SELF"] == "/index.php")) {
		$configUpNeeded = $_SESSION['PiStarRelease']['Pi-Star']['ConfUpdReqd'];
                if (!isset($configUpNeeded) || ($configUpNeeded < $configUpdateRequired)) {	
		    $fileList = array_filter(array("/etc/dstar-radio.mmdvmhost", "/etc/dstar-radio.dstarrepeater"), 'file_exists');
		    if ($file = array_shift($fileList)) {
	    ?>
		<div>
		    <div style="background-color: #FFCC00; color: #000;text-align:center; padding:10px 0; margin: 0px 0px 10px 0px; width: 100%;">
				<p>
				<b>IMPORTANT</b><br />
				<br />
				Your configuration needs to be updated.<br />
				Go to the <a href="/admin/configure.php" style="text-decoration:underline;font-weight:bold;">Configuration Page</a> and click on any "Apply Changes" button.<br />
				<br />This message will disappear once this has been completed.<br />
				</p>
		    </div>
		</div>
	    <?php }
	        }
	    }
	    ?>
	    <?php
            // Output some default features
            if ($_SERVER["PHP_SELF"] == "/index.php" || $_SERVER["PHP_SELF"] == "/admin/index.php")
            {
                    echo '<div class="contentwide">'."\n";
                    echo '<script type="text/javascript">'."\n";
                    echo 'function reloadHwInfo(){'."\n";
                    echo '  $("#hwInfo").load("/dstarrepeater/hw_info.php",function(){ setTimeout(reloadHwInfo, 15000) });'."\n";
                    echo '}'."\n";
                    echo 'setTimeout(reloadHwInfo, 15000);'."\n";
                    echo '$(window).trigger(\'resize\');'."\n";
                    echo '</script>'."\n";
                    echo '<script type="text/javascript">'."\n";
                    echo 'function reloadRadioInfo(){'."\n";
                    echo '  $("#radioInfo").load("/mmdvmhost/radioinfo.php",function(){ setTimeout(reloadRadioInfo, 1000) });'."\n";
                    echo '}'."\n";
                    echo 'setTimeout(reloadRadioInfo, 1000);'."\n";
                    echo '$(window).trigger(\'resize\');'."\n";
                    echo '</script>'."\n";
                    echo "<div id='hw_info' class='hw_toggle'>\n";
                    echo '<div id="hwInfo">'."\n";
                    include 'dstarrepeater/hw_info.php';
                    echo '</div>'."\n";
                    echo '</div>'."\n";
                    echo '<div id="radioInfo">'."\n";
                    include 'mmdvmhost/radioinfo.php';
                    echo '</div>'."\n";
                    echo '<br class="noMob" />'."\n";
                    echo '</div>'."\n";
            }

        // First lets figure out if we are in MMDVMHost mode, or dstarrepeater mode;
	    if (file_exists('/etc/dstar-radio.mmdvmhost')) {
		echo '<div class="nav">'."\n";					// Start the Side Menu
		echo '<script type="text/javascript">'."\n";
		echo 'function reloadRepeaterInfo(){'."\n";
		echo '  $("#repeaterInfo").load("/mmdvmhost/repeaterinfo.php",function(){ setTimeout(reloadRepeaterInfo,1000) });'."\n";
		echo '}'."\n";
		echo 'setTimeout(reloadRepeaterInfo,1000);'."\n";
		echo '$(window).trigger(\'resize\');'."\n";
		echo '</script>'."\n";
		echo '<div id="repeaterInfo">'."\n";
		include 'mmdvmhost/repeaterinfo.php';				// MMDVMDash Repeater Info
		echo '</div>'."\n";
		echo '</div>'."\n";
		
		echo '<div class="content">'."\n";

		// menu/selection set:
    		// BM  / DMRGwcheck: Get the current DMR Master from the config
    		$dmrMasterHost = getConfigItem("DMR Network", "Address", $_SESSION['MMDVMHostConfigs']);
    		if ( $dmrMasterHost == '127.0.0.1' ) {
        		$dmrMasterHost = $_SESSION['DMRGatewayConfigs']['DMR Network 1']['Address'];
        		$bmEnabled = ($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Enabled'] != "0" ? true : false);
			$xlxEnabled = ($_SESSION['DMRGatewayConfigs']['XLX Network']['Enabled'] != "0" ? true : false);
    		}
		elseif (preg_match("/brandmeister.network/",$dmrMasterHost))
		{
			$bmEnabled = true;
		}
    		// Make sure the master is a BrandMeister Master
    		if (($dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r")) != FALSE) {
        		while (!feof($dmrMasterFile)) {
            		$dmrMasterLine = fgets($dmrMasterFile);
            		$dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
            		if ((strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
                		if ($dmrMasterHost == $dmrMasterHostF[2]) { $dmrMasterHost = str_replace('_', ' ', $dmrMasterHostF[0]); }
            		}
        	    }
        	    fclose($dmrMasterFile);
    		} // end BM check
			// tgif check:
			if ( $testMMDVModeDMR == 1 ) {
		    	// Get the current DMR Master from the config
		    	$dmrMasterHost = getConfigItem("DMR Network", "Address", $_SESSION['MMDVMHostConfigs']);
		    	if ( $dmrMasterHost == '127.0.0.1' ) {
				// DMRGateway, need to check each config
				if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Address'])) {
			    	if (($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Address'] == "tgif.network") && ($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Enabled'])) {
						$tgifEnabled = true;
			    	}
				}
				if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Address'])) {
			    	if (($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Address'] == "tgif.network") && ($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Enabled'])) {
						$tgifEnabled = true;
			    	}
				}
				if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Address'])) {
			    	if (($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Address'] == "tgif.network") && ($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Enabled'])) {
						$tgifEnabled = true;
			    	}
				}
				if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 4']['Address'])) {
			    	if (($_SESSION['DMRGatewayConfigs']['DMR Network 4']['Address'] == "tgif.network") && ($_SESSION['DMRGatewayConfigs']['DMR Network 4']['Enabled'])) {
						$tgifEnabled = true;
			    	}
				}
				if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Address'])) {
			    	if (($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Address'] == "tgif.network") && ($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Enabled'])) {
						$tgifEnabled = true;
			    	}
				}
    		}
			// DMRmaster Connected directly to TGIF
    		else if ( $dmrMasterHost == 'tgif.network' ) {
				$tgifEnabled = true;
			}
        } // end tgif check

		$testMMDVModeDSTARnet = getConfigItem("D-Star", "Enable", $_SESSION['MMDVMHostConfigs']);
		if ( $testMMDVModeDSTARnet == 1 ) {	// If D-Star network is enabled, add these extra features.
		    
		    if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "ds_man" || $_GET["func"] == "ds_man") {	// Admin Only Option (D-Star Mgr)
			echo '<script type="text/javascript">'."\n";
			echo 'function reloadrefLinks(){'."\n";
			echo '  $("#refLinks").load("/dstarrepeater/active_reflector_links.php",function(){ setTimeout(reloadrefLinks,2500) });'."\n";
			echo '}'."\n";
			echo 'setTimeout(reloadrefLinks,2500);'."\n";
			echo '$(window).trigger(\'resize\');'."\n";
			echo '</script>'."\n";
			echo '<div id="refLinks">'."\n";
			include 'dstarrepeater/active_reflector_links.php';	// dstarrepeater gateway config
			echo '</div>'."\n";
			include 'dstarrepeater/link_manager.php';		// D-Star Link Manager
		    }
		    
		    echo '<script type="text/javascript">'."\n";
		    echo 'function reloadccsConnections(){'."\n";
		    echo '  $("#ccsConnects").load("/dstarrepeater/ccs_connections.php",function(){ setTimeout(reloadccsConnections,15000) });'."\n";
		    echo '}'."\n";
		    echo 'setTimeout(reloadccsConnections,15000);'."\n";
		    echo '$(window).trigger(\'resize\');'."\n";
		    echo '</script>'."\n";
		    echo '<div id="ccsConnects">'."\n";
		    include 'dstarrepeater/ccs_connections.php';			// dstarrepeater gateway config
		    echo '</div>'."\n";
		}
		if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "mode_man" || $_GET["func"] == "mode_man") {	// Admin Only Option (instant mode mgr)	
                    include "admin/instant-mode-manager.php";
		}

		if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "sys_man" || $_GET["func"] == "sys_man") {	// Admin Only Option (system mgr)	
                    include "admin/system-manager.php";
		}

		if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "bm_man" || $_GET["func"] == "bm_man") { 		// Admin Only Option (BM links )
		    echo '<script type="text/javascript">'."\n";
        	    echo 'function reloadbmConnections(){'."\n";
        	    echo '  $("#bmConnects").load("/mmdvmhost/bm_links.php",function(){ setTimeout(reloadbmConnections,20000) });'."\n";
        	    echo '}'."\n";
        	    echo 'setTimeout(reloadbmConnections,20000);'."\n";
		    echo '$(window).trigger(\'resize\');'."\n";
        	    echo '</script>'."\n";
        	    echo '<div id="bmConnects">'."\n";
		    include 'mmdvmhost/bm_links.php';                       // BM Links
		    echo '</div>'."\n";
		}
		if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "bm_man" || $_GET["func"] == "bm_man") {		// Admin Only Options (BM mgr)
			include 'mmdvmhost/bm_manager.php';                     // BM DMR Link Manager
		}

		// will re-enable if/when TGIF provides a public API for their new (2022) platform
		/*
		if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "tgif_man" || $_GET["func"] == "tgif_man") {	// Admin Only Option (tgif links)
		    echo '<script type="text/javascript">'."\n";
        	    echo 'function reloadtgifConnections(){'."\n";
        	    echo '  $("#tgifConnects").load("/mmdvmhost/tgif_links.php",function(){ setTimeout(reloadtgifConnections,15000) });'."\n";
        	    echo '}'."\n";
        	    echo 'setTimeout(reloadtgifConnections,15000);'."\n";
				echo '$(window).trigger(\'resize\');'."\n";
        	    echo '</script>'."\n";
        	    echo '<div id="tgifConnects">'."\n";
		    include 'mmdvmhost/tgif_links.php';			// TGIF Links
		    echo '</div>'."\n";
		}
		*/

		if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "tgif_man" || $_GET["func"] == "tgif_man") {	// Admin Only Options (tgi mgr)
        	include 'mmdvmhost/tgif_manager.php';			// TGIF DMR Link Manager
		}
		
		$testMMDVModeYSF = getConfigItem("System Fusion", "Enable", $_SESSION['MMDVMHostConfigs']);
		$testDMR2YSF = $_SESSION['DMR2YSFConfigs']['Enabled']['Enabled'];
		if ($testMMDVModeYSF == 1 || $testDMR2YSF == 1) {
		    if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "ysf_man" || $_GET["func"] == "ysf_man") { 	// Admin Only Option
				include 'mmdvmhost/ysf_manager.php';		// YSF Links
		    }
		}
		$testMMDVModeP25net = getConfigItem("P25 Network", "Enable", $_SESSION['MMDVMHostConfigs']);
                $testYSF2P25 = $_SESSION['YSF2P25Configs']['Enabled']['Enabled'];
		if ( $testMMDVModeP25net == 1 || $testYSF2P25 == 1) {				// If P25 network is enabled, add these extra features.
		    if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "p25_man" || $_GET["func"] == "p25_man") { 	// Admin Only Option *p25 mgr)
				include 'mmdvmhost/p25_manager.php';		// P25 Links
		    }
		}
		$testMMDVModeNXDN = getConfigItem("NXDN Network", "Enable", $_SESSION['MMDVMHostConfigs']);
		$testDMR2NXDN = $_SESSION['DMR2NXDNConfigs']['Enabled']['Enabled'];
		$testYSF2NXDN = $_SESSION['YSF2NXDNConfigs']['Enabled']['Enabled'];
		if ( $testMMDVModeNXDN == 1 || $testDMR2NXDN == 1 || $testYSF2NXDN == 1 ) {
		    if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "nxdn_man" || $_GET["func"] == "nxdn_man") { 	// Admin Only Option (nxdn mgr)
				include 'mmdvmhost/nxdn_manager.php';		// NXDN Links
		    }
		}
		$testMMDVModeM17net = getConfigItem("M17 Network", "Enable", $_SESSION['MMDVMHostConfigs']);
		if ( $testMMDVModeM17net == 1 ) {				// If M17 network is enabled, add these extra features.
		    if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "m17_man" || $_GET["func"] == "m17_man") { 	// Admin Only Option
			include 'mmdvmhost/m17_manager.php';		// M17 Links
		    }
		}
                $dmrMasterHost = getConfigItem("DMR Network", "Address", $_SESSION['MMDVMHostConfigs']);
                if ( $dmrMasterHost == '127.0.0.1') {
		    if ($xlxEnabled == 1) {
			if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "xlx_man" || $_GET["func"] == "xlx_man") { 	// Admin Only Option
			    include 'mmdvmhost/xlx_dmr_manager.php';		// XLX-DMR Manager
			}
		    }
		}
		$testMMDVModePOCSAG = getConfigItem("POCSAG", "Enable", $_SESSION['MMDVMHostConfigs']);
		if ( $testMMDVModePOCSAG == 1 ) {
		    if ($_SERVER["PHP_SELF"] == "/admin/index.php" && $_POST["func"] == "pocsag_man" || $_GET["func"] == "pocsag_man") {  // Admin Only Options (pocsag mgr)
			    echo '<div id="dapnetMsgr">'."\n";
			    include 'mmdvmhost/dapnet_messenger.php';
			    echo '</div>'."\n";
		    }
        	}

	    if ($_SERVER["PHP_SELF"] == "/admin/index.php") {
		echo '<div class="contentwide">'."\n";
	    	echo '<script type="text/javascript">'."\n";
	    	echo 'function reloadSysInfo(){'."\n";
	    	echo '  $("#sysInfo").load("/dstarrepeater/system.php",function(){ setTimeout(reloadSysInfo,5000) });'."\n";
	    	echo '}'."\n";
	    	echo 'setTimeout(reloadSysInfo,5000);'."\n";
	    	echo '$(window).trigger(\'resize\');'."\n";
	    	echo '</script>'."\n";
	    	if ($_GET['func'] == "main") {				// only show services on main admin page
		    echo '<div id="sysInfo">'."\n";
		    include 'dstarrepeater/system.php';				// Basic System Info
		    echo '</div></div>'."\n";
                }
            }
    
		// begin admin selection form
		if ($_SERVER["PHP_SELF"] == "/admin/index.php") {
		    if ($_GET['func'] != 'main') { echo '<br />'; }
                    echo '<div style="text-align:left;font-weight:bold;">Admin Sections</div>'."\n";
		    echo '<form method="get" id="admin_sel" name="admin_sel" action="/admin/" style="padding-bottom:10px;">'."\n";
		    echo '      <div class="mode_flex">'."\n";
		    echo '        <div class="mode_flex row">'."\n";
		    echo '          <div class="mode_flex column">'."\n";
 		    echo '            <button form="admin_sel" type="submit" value="main" name="func"><span>Admin Main Page</span></button>'."\n";
		    echo '          </div><div class="mode_flex column">'."\n";
                    $testMMDVModeDSTARnet = getConfigItem("D-Star", "Enable", $_SESSION['MMDVMHostConfigs']);
                    if ( $testMMDVModeDSTARnet == 1 && !isPaused("D-Star") ) {
                        echo '		<button form="admin_sel" type="submit" value="ds_man" name="func"><span>D-Star Manager</span></button>'."\n";
                    }
                    else {
                        echo '		<button form="admin_sel" disabled="disabled" type="submit" value="ds_man" name="func"><span>D-Star Manager</span></button>'."\n";
                    } 
		    echo '          </div><div class="mode_flex column">'."\n";
                    $testMMDVModeDMR = getConfigItem("DMR", "Enable", $_SESSION['MMDVMHostConfigs']);
		    if ($bmEnabled == true && $testMMDVModeDMR ==1) {
		        echo '		<button form="admin_sel" type="submit" value="bm_man" name="func"><span>BrandMeister Manager</span></button>'."\n";
		    }
		    else {
			echo '		<button form="admin_sel" disabled="disabled" type="submit" value="bm_man" name="func"><span>BrandMeister Manager</span></button>'."\n";
		    }
		    echo '          </div><div class="mode_flex column">'."\n";
                    if ($tgifEnabled ==1 && $testMMDVModeDMR ==1) {
		        echo '		<button form="admin_sel" type="submit" value="tgif_man" name="func"><span>TGIF Manager</span></button>'."\n";
		    }
		    else {
			echo '		<button form="admin_sel" disabled="disabled" type="submit" value="tgif_man" name="func"><span>TGIF Manager</span></button>'."\n";
		    }
		    echo '          </div><div class="mode_flex column">'."\n";
        	    $testMMDVModeYSF = getConfigItem("System Fusion", "Enable", $_SESSION['MMDVMHostConfigs']);
        	    $testDMR2YSF = $_SESSION['DMR2YSFConfigs']['Enabled']['Enabled'];
        	    if ($testMMDVModeYSF == 1 || $testDMR2YSF == 1) {
		        echo '		<button form="admin_sel" type="submit" value="ysf_man" name="func"><span>YSF Manager</span></button>'."\n";
		    }
		    else {
		        echo '		<button form="admin_sel" disabled="disabled" type="submit" value="ysf_man" name="func"><span>YSF Manager</span></button>'."\n";
		    }
		    echo '          </div><div class="mode_flex column">'."\n";
                    $testMMDVModeDMR = getConfigItem("DMR", "Enable", $_SESSION['MMDVMHostConfigs']);
                    if ($xlxEnabled == true && $testMMDVModeDMR ==1) {
                        echo '          <button form="admin_sel" type="submit" value="xlx_man" name="func"><span>XLX DMR Link Manager</span></button>'."\n";
                    }
                    else {
                        echo '          <button form="admin_sel" disabled="disabled" type="submit" value="xlx_man" name="func"><span>XLX DMR Link Manager</span></button>'."\n";
                    } 
		    echo '      </div></div>'."\n";
                    echo '        <div class="mode_flex row">'."\n";
		    echo '          <div class="mode_flex column">'."\n";
                    $testMMDVModeP25 = getConfigItem("P25", "Enable", $_SESSION['MMDVMHostConfigs']);
                    $testYSF2P25 = $_SESSION['YSF2P25Configs']['Enabled']['Enabled'];
                    if ( $testMMDVModeP25 == 1 || $testYSF2P25 == 1) {
		    	echo '		<button form="admin_sel" type="submit" value="p25_man" name="func"><span>P25 Manager</span></button>'."\n";
		    }
		    else {
		    	echo '		<button form="admin_sel" disabled="disabled" type="submit" value="p25_man" name="func"><span>P25 Manager</span></button>'."\n";
		    }
		    echo '          </div><div class="mode_flex column">'."\n";
		    $testMMDVModeNXDN = getConfigItem("NXDN Network", "Enable", $_SESSION['MMDVMHostConfigs']);
                    $testDMR2NXDN = $_SESSION['DMR2NXDNConfigs']['Enabled']['Enabled'];
                    $testYSF2NXDN = $_SESSION['YSF2NXDNConfigs']['Enabled']['Enabled'];
                    if (($testMMDVModeNXDN == 1 || $testDMR2NXDN == 1 || $testYSF2NXDN == 1) && !isPaused("NXDN")) {
		    	echo '		<button form="admin_sel" type="submit" value="nxdn_man" name="func"><span>NXDN Manager</span></button>'."\n";
		    }
		    else {
		    	echo '		<button form="admin_sel" disabled="disabled" type="submit" value="nxdn_man" name="func"><span>NXDN Manager</span></button>'."\n";
		    }
		    echo '          </div><div class="mode_flex column">'."\n";
                    $testMMDVModeM17 = getConfigItem("M17 Network", "Enable", $_SESSION['MMDVMHostConfigs']);
                    if ($testMMDVModeM17 == 1 && !isPaused("M17")) {
		    	echo '		<button form="admin_sel" type="submit" value="m17_man" name="func"><span>M17 Manager</span></button>'."\n";
		    }
		    else {
		    	echo '		<button form="admin_sel" disabled="disabled" type="submit" value="m17_man" name="func"><span>M17 Manager</span></button>'."\n";
                    }
		    echo '          </div><div class="mode_flex column">'."\n";
                    $testMMDVModePOCSAG = getConfigItem("POCSAG", "Enable", $_SESSION['MMDVMHostConfigs']);
		    if ($testMMDVModePOCSAG == 1) {
		        echo '		<button form="admin_sel" type="submit" value="pocsag_man" name="func"><span>POCSAG Manager</span></button>'."\n";
		    }
		    else {
		        echo '		<button form="admin_sel" disabled="disabled" type="submit" value="pocsag_man" name="func"><span>POCSAG Manager</span></button>'."\n";
		    }
		    echo '          </div><div class="mode_flex column">'."\n";
 		    echo '            <button form="admin_sel" type="submit" value="mode_man" name="func"><span>Instant Mode Manager</span></button>'."\n";
		    echo '          </div><div class="mode_flex column">'."\n";
		    echo '		<button form="admin_sel" type="submit" value="sys_man" name="func"><span>System Manager</span></button>'."\n";
		    echo '      </div></div>'."\n".'</div>'."\n";
		    echo '      <div><b>Note:</b> Modes/networks/services not <a href="/admin/configure.php" style="text-decoration:underline;color:inherit;">globally configured/enabled</a>, or that are paused, are not selectable here until they are enabled or <a href="./?func=mode_man" style="text-decoration:underline;color:inherit;">resumed from pause</a>.</div>'."\n";
		    echo ' </form>'."\n";
		    if ($_GET['func'] != "main") {
			echo "</div>\n";
		    }
		}

	if ($_SERVER["PHP_SELF"] == "/index.php" || $_SERVER["PHP_SELF"] == "/admin/index.php") {
		echo '<script type="text/javascript">'."\n";
        	echo 'function setLastCaller(obj) {'."\n";
        	echo '    if (obj.checked) {'."\n";
        	echo "        $.ajax({
                	        type: \"POST\",
  	          	        url: '/mmdvmhost/callerdetails_ajax.php',
                	        data:{action:'enable'},
				success: function(data) { 
     				    $('#lcmsg').html(data).fadeIn('slow');
				    $('#lcmsg').html(\"<div style='padding:8px;font-style:italic;font-weight:bold;'>For optimal performance, the number of Last Heard rows will be decreased while Caller Details function is enabled.</div>\").fadeIn('slow')
     				    $('#lcmsg').delay(4000).fadeOut('slow');
				}
         	             });";
	        echo '    }'."\n";
	        echo '    else {'."\n";
	        echo "        $.ajax({
	                        type: \"POST\",
	                        url: '/mmdvmhost/callerdetails_ajax.php',
	                        data:{action:'disable'},
				success: function(data) { 
     				    $('#lcmsg').html(data).fadeIn('slow');
				    $('#lcmsg').html(\"<div style='padding:8px;font-style:italic;font-weight:bold;'>Caller Details function disabled. Increasing Last Heard table rows to user preference (if set) or default (40).</div>\").fadeIn('slow')
     				    $('#lcmsg').delay(4000).fadeOut('slow');
				}
	                      });";
	        echo '    }'."\n";
	        echo '}'."\n";
    		echo '</script>'."\n";
		echo '<div id="lcmsg" style="background:#d6d6d6;color:black; margin:0 0 10px 0;"></div>'."\n";
    		echo '<script type="text/javascript">'."\n";
    		echo 'function LiveCallerDetails(){'."\n";
    		echo '  $("#liveCallerDeets").load("/mmdvmhost/live_caller_table.php");'."\n";
    		echo '}'."\n";
    		echo 'setInterval(function(){LiveCallerDetails()}, 1500);'."\n";
    		echo '$(window).trigger(\'resize\');'."\n";
    		echo '</script>'."\n";
 
		echo '<script type="text/javascript">'."\n";
		echo 'var lhto;'."\n";
		echo 'var ltxto'."\n";

		echo 'function reloadLiveCaller(){'."\n";
		echo '  $("#liveCallerDeets").load("/mmdvmhost/live_caller_table.php",function(){ livecaller = setTimeout(reloadLiveCaller,1500) });'."\n";
		echo '}'."\n";
		echo 'function reloadLocalTX(){'."\n";
		echo '  $("#localTxs").load("/mmdvmhost/localtx.php",function(){ ltxto = setTimeout(reloadLocalTX,1500) });'."\n";
		echo '}'."\n";
	
		echo 'function reloadLastHeard(){'."\n";
		echo '  $("#lastHeard").load("/mmdvmhost/lh.php",function(){ lhto = setTimeout(reloadLastHeard,1500) });'."\n";
		echo '}'."\n";
		
     		echo 'function setLCautorefresh(obj) {'."\n";
    		echo '        livecaller = setTimeout(reloadLiveCaller,1500,1500);'."\n";
    		echo '}'."\n";

		echo 'function setLHAutorefresh(obj) {'."\n";
    		echo '        lhto = setTimeout(reloadLastHeard,1500);'."\n";
    		echo '}'."\n";
		
		echo 'function setLocalTXAutorefresh(obj) {'."\n";
    		echo '        ltxto = setTimeout(reloadLocalTX,1500);'."\n";
    		echo '}'."\n";
		
		echo 'lhto = setTimeout(reloadLastHeard,1500);'."\n";
		echo 'ltxto = setTimeout(reloadLocalTX,1500);'."\n";
		echo 'livecaller = setTimeout(reloadLiveCaller,1500);'."\n";
		echo '$(window).trigger(\'resize\');'."\n";
        	echo 'function setLHTGnames(obj) {'."\n";
        	echo '    if (obj.checked) {'."\n";
        	echo "        $.ajax({
                	        type: \"POST\",
  	          	        url: '/mmdvmhost/tgnames_ajax.php',
                	        data:{action:'enable'},
                                success: function(data) { 
                                    $('#lcmsg').html(data).fadeIn('slow');
                                    $('#lcmsg').html(\"<div style='padding:8px;font-style:italic;font-weight:bold;'>Talkgroup Names display enabled: Please wait until data populated.</div>\").fadeIn('slow')
                                    $('#lcmsg').delay(4000).fadeOut('slow');
                                }
         	             });";
	        echo '    }'."\n";
	        echo '    else {'."\n";
	        echo "        $.ajax({
	                        type: \"POST\",
	                        url: '/mmdvmhost/tgnames_ajax.php',
	                        data:{action:'disable'},
                                success: function(data) { 
                                    $('#lcmsg').html(data).fadeIn('slow');
                                    $('#lcmsg').html(\"<div style='padding:8px;font-style:italic;font-weight:bold;'>Talkgroup Names display disabled: Please wait until data is cleared.</div>\").fadeIn('slow')
                                    $('#lcmsg').delay(4000).fadeOut('slow');
                                }
	                      });";
	        echo '    }'."\n";
	        echo '}'."\n";
    		echo '</script>'."\n";

    }

		if ($_SERVER["PHP_SELF"] == "/admin/index.php") {
		    echo '<div style="text-align:left;font-weight:bold;"><a style="color:'.$textSections.';text-decoration:underline;" href="#lh_info" id="lh_details">Display Last Heard...</a></div><br />';
		    echo '<div id="lh_info" style="display:none;">';
                    echo '<div id="liveCallerDeets">'."\n";
                    include 'mmdvmhost/live_caller_table.php';
                    echo '</div>'."\n";

                    echo '<div id="localTxs">'."\n";
                    include 'mmdvmhost/localtx.php';                            // MMDVMDash Local Trasmissions
                    echo '</div>'."\n";

                    echo '<div id="lastHeard">'."\n";
                    include 'mmdvmhost/lh.php';                                 // MMDVMDash Last Heard
                    echo '</div>'."\n";
                    echo '</div>'."\n";
                } else {
                    echo '<div id="liveCallerDeets">'."\n";
                    include 'mmdvmhost/live_caller_table.php';
                    echo '</div>'."\n";

                    echo '<div id="localTxs">'."\n";
                    include 'mmdvmhost/localtx.php';                            // MMDVMDash Local Trasmissions
                    echo '</div>'."\n";

                    echo '<div id="lastHeard">'."\n";
                    include 'mmdvmhost/lh.php';                                 // MMDVMDash Last Heard
                    echo '</div>'."\n";
		}

		// If POCSAG is enabled, show the information panel
        if ( $testMMDVModePOCSAG == 1 ) {
            if (($_SERVER["PHP_SELF"] == "/index.php" || $_POST["func"] == "pocsag_man" || $_GET["func"] == "pocsag_man")) { // display pages in pocsag mgr or main dash page only with no other func requested
	            $myOrigin = ($_SERVER["PHP_SELF"] == "/admin/index.php" ? "admin" : "other");
		    
		    echo '<script type="text/javascript">'."\n";
		    echo 'var pagesto;'."\n";
		    echo 'function setPagesAutorefresh(obj) {'."\n";
	            echo '        pagesto = setTimeout(reloadPages, 10000, "?origin='.$myOrigin.'");'."\n";
		    echo '}'."\n";
		    echo 'function reloadPages(OptStr){'."\n";
		    echo '    $("#Pages").load("/mmdvmhost/pages.php"+OptStr, function(){ pagesto = setTimeout(reloadPages, 10000, "?origin='.$myOrigin.'") });'."\n";
		    echo '}'."\n";
		    echo 'pagesto = setTimeout(reloadPages, 10000, "?origin='.$myOrigin.'");'."\n";
		    echo '$(window).trigger(\'resize\');'."\n";
		    echo '</script>'."\n";
		    echo "\n".'<div id="Pages">'."\n";
		    include 'mmdvmhost/pages.php';				// POCSAG Messages
		    echo '</div>'."\n";
		}
	    }
    }
	    else if (file_exists('/etc/dstar-radio.dstarrepeater')) {
		echo '<div class="contentwide">'."\n";
		include 'dstarrepeater/gateway_software_config.php';		// dstarrepeater gateway config
		echo '<script type="text/javascript">'."\n";
		echo 'function reloadrefLinks(){'."\n";
		echo '  $("#refLinks").load("/dstarrepeater/active_reflector_links.php",function(){ setTimeout(reloadrefLinks,2500) });'."\n";
		echo '}'."\n";
		echo 'setTimeout(reloadrefLinks,2500);'."\n";
		echo '$(window).trigger(\'resize\');'."\n";
		echo '</script>'."\n";
		echo '<br />'."\n";
		echo '<div id="refLinks">'."\n";
		include 'dstarrepeater/active_reflector_links.php';		// dstarrepeater gateway config
		echo '</div>'."\n";
		if ($_SERVER["PHP_SELF"] == "/admin/index.php") {        // Admin Only Option (D-Star Mgr)
		    include 'dstarrepeater/link_manager.php';		// D-Star Link Manager
		    echo "<br />\n";
		}
		
		echo '<script type="text/javascript">'."\n";
		echo 'function reloadccsConnections(){'."\n";
		echo '  $("#ccsConnects").load("/dstarrepeater/ccs_connections.php",function(){ setTimeout(reloadccsConnections,15000) });'."\n";
		echo '}'."\n";
		echo 'setTimeout(reloadccsConnections,15000);'."\n";
		echo '$(window).trigger(\'resize\');'."\n";
		echo '</script>'."\n";
		echo '<div id="ccsConnects">'."\n";
		include 'dstarrepeater/ccs_connections.php';			// dstarrepeater gateway config
		echo '</div>'."\n";
		
		echo '<script type="text/javascript">'."\n";
		echo 'function reloadLocalTx(){'."\n";
		echo '  $("#localTx").load("/dstarrepeater/local_tx.php",function(){ setTimeout(reloadLocalTx,3000) });'."\n";
		echo '}'."\n";
		echo 'setTimeout(reloadLocalTx,3000);'."\n";
		echo 'function reloadLastHeard(){'."\n";
		echo '  $("#lhdstar").load("/dstarrepeater/last_heard.php",function(){ setTimeout(reloadLastHeard,3000) });'."\n";
		echo '}'."\n";
		echo 'setTimeout(reloadLastHeard,3000);'."\n";
		echo '$(window).trigger(\'resize\');'."\n";
		echo '</script>'."\n";
		echo '<div id="lhdstar">'."\n";
		include 'dstarrepeater/last_heard.php';				//dstarrepeater Last Heard
		echo '</div>'."\n";
		echo "<br />\n";
		echo '<div id="localTx">'."\n";
		include 'dstarrepeater/local_tx.php';				//dstarrepeater Local Transmissions
		echo '</div>'."\n";
		echo '<br />'."\n";
		
	    }
	    else {
		echo '<div class="contentwide">'."\n";
		//We dont know what mode we are in - fail...
		echo "<H1>No Mode Defined...</H1>\n";
		echo "<p>I don't know what mode I am in, you probably just need to configure me.</p>\n";
		echo "<p>You will be re-directed to the configuration portal in 10 secs</p>\n";
		echo '<script type="text/javascript">setTimeout(function() { window.location="/admin/configure.php";},10000);</script>'."\n";
	    }
	    ?>
	</div>
	
	<div class="footer">
	   <?php 
		echo 'Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-'.date("Y").'<br />'."\n";
		echo '<a href="https://w0chp.net/w0chp-pistar-dash/" style="color: #ffffff; text-decoration:underline;">W0CHP-PiStar-Dash</a> enhancements by W0CHP';
	   ?>
	  <br />USA Callsign/Name Lookups Provided by <a href="https://callook.info/" style="color: #ffffff; text-decoration:underline;">Callook.Info</a>
	</div>
	
	</div>
    </body>
</html>

