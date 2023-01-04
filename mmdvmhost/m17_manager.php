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

    // Check if M17 is Enabled
    $testMMDVModeM17 = getConfigItem("M17 Network", "Enable", $_SESSION['MMDVMHostConfigs']);
    if ( $testMMDVModeM17 == 1 ) {
	// Check that the remote is enabled
	if (isset($_SESSION['M17GatewayConfigs']['Remote Commands']['Enable']) && (isset($_SESSION['M17GatewayConfigs']['Remote Commands']['Port'])) && ($_SESSION['M17GatewayConfigs']['Remote Commands']['Enable'] == 1)) {
	    if (!empty($_POST) && isset($_POST["m17MgrSubmit"])) {
		$remoteCommand = "";
		$remotePort = $_SESSION['M17GatewayConfigs']['Remote Commands']['Port'];
		
		// Handle Posted Data
		if ($_POST["Link"] == "LINK") {
		    $m17LinkHost = $_POST['m17LinkHost'];
		    $m17LinkToHost = "";
		    if ($m17LinkHost != "none") { // Unlinking
			$m17LinkToHost = "".$m17LinkHost."_".$_POST['m17LinkModule']."";
		    }
		    $remoteCommand = "cd /var/log/pi-star && sudo /usr/local/bin/RemoteCommand ".$remotePort." Reflector ".$m17LinkToHost."";
		}
		else if ($_POST["Link"] == "UNLINK") {
		    $remoteCommand = "cd /var/log/pi-star && sudo /usr/local/bin/RemoteCommand ".$remotePort." Reflector";
		}
		else {
		    echo "<div style='text-align:left;font-weight:bold;'>M17 Link Manager</div>\n";
		    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td><p>";
		    echo "Something wrong with your input, (Neither Link nor Unlink Sent) - please try again";
		    echo "<br />Page reloading...</p></td></tr>\n</table>\n";
		    unset($_POST);
		    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
		}
		if (empty($_POST['m17LinkHost'])) {
		    echo "<div style='text-align:left;font-weight:bold;'>M17 Link Manager</div>\n";
		    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td><p>";
		    echo "Something wrong with your input, (No target specified) -  please try again";
		    echo "<br />Page reloading...</p></td></tr>\n</table>\n";
		    unset($_POST);
		    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
		}
		
		if (isset($remoteCommand)) {
		    echo "<div style='text-align:left;font-weight:bold;'>M17 Link Manager</div>\n";
		    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td><p>";
		    echo exec($remoteCommand);
		    echo "<br />Page reloading...</p></td></tr>\n</table>\n";
		    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
		}
	    }
	    else {
		// Output HTML
		?>
    		<div style="text-align:left;font-weight:bold;">M17 Link Manager</div>
		<form action="//<?php echo htmlentities($_SERVER['HTTP_HOST']).htmlentities($_SERVER['PHP_SELF']); ?>?func=m17_man" method="post">
		    <table>
			<tr>
			    <th width="150"><a class="tooltip" href="#">Select Reflector / Module <span><b>Select Reflector Module</b></span></a></th>
			    <th width="150"><a class="tooltip" href="#">Current Link<span><b>Current Link</b></span></a></th>
			    <th width="150"><a class="tooltip" href="#">Link / Un-Link<span><b>Link / Un-Link</b></span></a></th>
			    <th width="150"><a class="tooltip" href="#">Action<span><b>Action</b></span></a></th>
			</tr>
			<tr>
			    <?php
			    $m17CurrentHost = "";
			    $m17CurrentModule = "";
			    $m17Linked = getActualLink($reverseLogLinesM17Gateway, "M17");
			    if (strpos($m17Linked, "Not") === false) {
				$m17CurrentHost = substr($m17Linked, 0, -2);
				$m17CurrentModule = "Module " .substr($m17Linked, -1)."";
			    } else {
				$m17CurrentHost = "Not Linked";
				$m17CurrentModule = "";
			    }
			    ?>
			    <td>
				<select name="m17LinkHost" class="M17Ref">
				    <?php
				    if ($m17CurrentHost == "") {
					echo "      <option value=\"none\" selected=\"selected\">None</option>\n";
				    }
				    else {
					echo "      <option value=\"none\">None</option>\n";
				    }

				    if ($m17MasterHandle = @fopen("/usr/local/etc/M17Hosts.txt", 'r')) {
					$m17gatewayConfigFile = '/etc/m17gateway';
					$configm17gateway = $configm17gateway = parse_ini_file($m17gatewayConfigFile, true);
					$m17StartupHostWithModule = (isset($configm17gateway['Network']['Startup']) ? $configm17gateway['Network']['Startup'] : "");
					$m17StartupHost = "";
					$m17StartupModule = "A";
					if ($m17StartupHostWithModule != "") {
					    $m17StartupHost = substr($m17StartupHostWithModule, 0, -2);
					    $m17StartupModule = substr($m17StartupHostWithModule, -1);
					}
					if ($m17StartupHost == "") {
					    echo "      <option value=\"NONE\" selected=\"selected\">None</option>\n";
					} else {
					    echo "      <option value=\"NONE\">None</option>\n";
					}
					while ($m17MasterLine = fgets($m17MasterHandle)) {
					    $m17MasterHost = preg_split('/\s+/', $m17MasterLine);
					    if ((strpos($m17MasterHost[0], '#') === FALSE) && ($m17MasterHost[0] != '')) {
						if ($m17MasterHost[0] == $m17StartupHost) {
						    echo "      <option value=\"$m17MasterHost[0]\" selected=\"selected\">$m17MasterHost[0]</option>\n";
						} else {
						    echo "      <option value=\"$m17MasterHost[0]\">$m17MasterHost[0]</option>\n";
						}
					    }
					}
					fclose($m17MasterHandle);
				    }
				    ?>
				</select>
				<select name="m17LinkModule" class="ModSel">
				    <?php
				    $m17ModuleList = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
				    foreach ($m17ModuleList as $module) {
					if ($m17StartupModule == $module) {
					    echo "  <option value=\"".$module."\" selected=\"selected\">".$module."</option>\n";
					}
					else {
					    echo "  <option value=\"".$module."\">".$module."</option>\n";
					}
				    }
				    ?>
				</select>
			    </td>
			    <script>
			          $(document).ready(function(){
			            setInterval(function(){
			                $(".CheckLink").load(window.location.href + " .CheckLink" );
			                },3000);
			            });
			    </script>
			    <td><strong class="CheckLink"><?php echo "$m17CurrentHost $m17CurrentModule"; ?></strong></td>
			    <td>
                              <input type="radio" id="link" name="Link" value="LINK" /> <label for="link"/>Link</label>
                              <input type="radio" id="unlink" name="Link" value="UNLINK" checked="checked"  /> <label for="unlink"/>Un-Link</label>
			    </td>
			    <td>
				<input type="submit" name="m17MgrSubmit" value="Request Change" />
			    </td>
			</tr>
                        <tr>
                          <td colspan="4" style="white-space:normal;padding: 3px;">
                            <a href="https://w0chp.net/m17-reflectors/" target="_blank">List of M17 Reflectors (searchable/downloadable)</a>
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
