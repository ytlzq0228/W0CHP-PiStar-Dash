<?php require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php'; ?>
<div class="header">
    <div class="SmallHeader shLeft">Hostname: <?php echo exec('cat /etc/hostname'); ?></div><div class="SmallHeader shRight">Pi-Star: Ver.#  <?php echo $_SESSION['PiStarRelease']['Pi-Star']['Version'].'<br />';?>
    <?php if (constant("AUTO_UPDATE_CHECK") == "true") { ?> 
    <div id="CheckUpdate"><?php echo $version; system('/usr/local/sbin/pistar-check4updates'); ?></div></div>
    <?php } else { ?>
    <div id="CheckUpdate"><?php echo $version; ?></div></div>
    <?php } ?>    
    <h1>Pi-Star Digital Voice - Expert Area</h1>
    <p>
	<div class="navbar">
	    <a class="menuconfig" href="/admin/configure.php"><?php echo $lang['configuration'];?></a>
	    <a class="menubackup" href="/admin/config_backup.php"><?php echo $lang['backup_restore'];?></a>
	    <a class="menuupgrade" href="/admin/expert/upgrade.php">Upgrade</a>
	    <a class="menuupdate" href="/admin/update.php"><?php echo $lang['update'];?></a>
	    <a class="menuadmin" href="/admin/"><?php echo $lang['admin'];?></a>
	    <?php if (file_exists("/etc/dstar-radio.mmdvmhost")) { ?>
	    <a class="menulive" href="/live/">Live Caller</a>
	    <?php } ?>
	    <a class="menudashboard" href="/"><?php echo $lang['dashboard'];?></a>
	</div>

	<div class="lnavbar">
            <ul class="mainnav">
		<li class="has-subs"><a href="#">Quick Editors</a>
		    <ul class="dropdown">
		    	<li class="subs"><a href="edit_mmdvmhost.php">MMDVMHost</a></li>
			<!-- D-Star Sub -->
			<li class="subs has-subs"><a href="#">D-Star</a>
			    <ul class="dropdown">
				<li class="subs"><a href="edit_dstarrepeater.php">D-StarRepeater</a></li>
				<li class="subs"><a href="edit_ircddbgateway.php">ircDDB Gateway</a></li>
				<li class="subs"><a href="edit_timeserver.php">TimeServer</a></li>
			    </ul>
			</li>
			<!-- DMR Sub -->
			<li class="subs has-subs"><a href="#">DMR</a>
			    <ul class="dropdown">
				<li class="subs"><a href="edit_dmrgateway.php">DMR Gateway</a></li>
				<li class="subs"><a href="edit_dmr2ysf.php">DMR to YSF</a></li>
				<li class="subs"><a href="edit_dmr2nxdn.php">DMR to NXDN</a></li>
			    </ul>
			</li>
			<!-- SystemFusion Sub -->
			<li class="subs has-subs"><a href="#">SystemFusion</a>
			    <ul class="dropdown">
				<li class="subs"><a href="edit_ysfgateway.php">YSF Gateway</a></li>
				<li class="subs"><a href="edit_dgidgateway.php">DGId Gateway</a></li>
				<li class="subs"><a href="edit_ysf2dmr.php">YSF to DMR</a></li>
				<li class="subs"><a href="edit_ysf2p25.php">YSF to P25</a></li>
				<li class="subs"><a href="edit_ysf2nxdn.php">YSF to NXDN</a></li>
			    </ul>
			</li>
            <!-- others -->
			<li class="subs"><a href="edit_p25gateway.php">P25 Gateway</a></li>
			<li class="subs"><a href="edit_nxdngateway.php">NXDN Gateway</a></li>
			<li class="subs"><a href="edit_m17gateway.php">M17 Gateway</a></li>
			<li class="subs"><a href="edit_aprsgateway.php">APRS Gateway</a></li>
			<!-- DAPNet Sub -->
			<li class="subs has-subs"><a href="#">DAPNet</a>
			    <ul class="dropdown">
				<li class="subs"><a href="edit_dapnetgateway.php">DAPNet Gateway</a></li>
				<li class="subs"><a href="edit_dapnetapi.php">DAPNet API</a></li>
			    </ul>
			</li>
		    </ul>
		</li>

		<li class="has-subs"><a href="#">Full Editors</a>
		    <ul class="dropdown">
			<li class="subs"><a href="fulledit_mmdvmhost.php">MMDVMHost</a></li>
			<li class="subs"><a href="fulledit_pistar-remote.php">PiStar-Remote</a></li>
			<!-- D-Star Sub -->
			<li class="subs has-subs"><a href="#">D-Star</a>
			    <ul class="dropdown">
				<li class="subs"><a href="fulledit_dstarrepeater.php">D-StarRepeater</a></li>
				<li class="subs"><a href="fulledit_ircddbgateway.php">ircDDB Gateway</a></li>
				<li class="subs"><a href="fulledit_timeserver.php">TimeServer</a></li>
			    </ul>
			</li>
			<!-- DMR Sub -->
			<li class="subs has-subs"><a href="#">DMR</a>
			    <ul class="dropdown">
				<li class="subs"><a href="fulledit_dmrgateway.php">DMR Gateway</a></li>
				<li class="subs"><a href="fulledit_dmr2ysf.php">DMR to YSF</a></li>
				<li class="subs"><a href="fulledit_dmr2nxdn.php">DMR to NXDN</a></li>
				<li class="subs"><a href="fulledit_bmapikey.php">Brandmeister API</a></li>
			    </ul>
			</li>
			<!-- YSF Sub -->
			<li class="subs has-subs"><a href="#">SystemFusion</a>
			    <ul class="dropdown">
				<li class="subs"><a href="fulledit_ysfgateway.php">YSF Gateway</a></li>
				<li class="subs"><a href="fulledit_dgidgateway.php">DGId Gateway</a></li>
				<li class="subs"><a href="fulledit_ysf2dmr.php">YSF to DMR</a></li>
				<li class="subs"><a href="fulledit_ysf2p25.php">YSF to P25</a></li>
				<li class="subs"><a href="fulledit_ysf2nxdn.php">YSF to NXDN</a></li>
			    </ul>
			</li>
            <!-- others -->
			<li class="subs"><a href="fulledit_p25gateway.php">P25 Gateway</a></li>
			<li class="subs"><a href="fulledit_nxdngateway.php">NXDN Gateway</a></li>
			<li class="subs"><a href="fulledit_m17gateway.php">M17 Gateway</a></li>
			<li class="subs"><a href="fulledit_aprsgateway.php">APRS Gateway</a></li>
			<!-- DAPNet Sub -->
			<li class="subs has-subs"><a href="#">DAPNet</a>
			    <ul class="dropdown">
				<li class="subs"><a href="fulledit_dapnetgateway.php">DAPNet Gateway</a></li>
				<li class="subs"><a href="fulledit_dapnetapi.php">DAPNet API</a></li>
			    </ul>
			</li>
			<li class="subs"><a href="fulledit_wpaconfig.php">WiFi</a></li>
			<li class="subs"><a href="fulledit_cron.php">System Cron</a></li>
			<li class="subs"><a href="fulledit_rssidat.php">RSSI Data</a></li>
		    </ul>
		</li>
		
		<li class="has-subs"><a href="#">Hosts File Editors</a>
		    <ul class="dropdown">
			<li class="subs"><a href="fulledit_hosts_DCS_Hosts.php">DCS Hosts</a></li>
			<li class="subs"><a href="fulledit_hosts_DExtra_Hosts.php">DExtra Hosts</a></li>
			<li class="subs"><a href="fulledit_hosts_DPlus_Hosts.php">DPlus Hosts</a></li>
			<li class="subs"><a href="fulledit_hosts_DMR_Hosts.php">DMR Hosts</a></li>
			<li class="subs"><a href="fulledit_hosts_P25_Hosts.php">P25 Hosts</a></li>
			<li class="subs"><a href="fulledit_hosts_NXDN_Hosts.php">NXDN Hosts</a></li>
			<li class="subs"><a href="fulledit_hosts_M17_Hosts.php">M17 Hosts</a></li>
			<li class="subs"><a href="fulledit_hosts_XLX_Hosts.php">XLX Hosts</a></li>
		    </ul>
		</li>
			
		<li class="has-subs"><a href="#">Tools</a>
		    <ul class="dropdown">
			<!-- Services Sub -->
			<li class="subs has-subs"><a href="#">Pi-Star Services</a>
			    <ul class="dropdown">
				<li class="subs"><a href="services.php?action=stop">Stop Services</a></li>
				<li class="subs"><a href="services.php?action=fullstop">Full Stop Services</a></li>
				<li class="subs"><a href="services.php?action=restart">Restart Services</a></li>
				<li class="subs"><a href="services.php?action=status">Services Status</a></li>
				<li class="subs"><a href="services.php?action=killmmdvmhost">Kill MMDVMHost</a></li>
			    </ul>
			</li>
			<li class="subs"><a href="services.php?action=updatehostsfiles">Update Hosts Files</a></li>
			<li class="subs"><a href="security_upgrades.php">Security Upgrades</a></li>
			<li class="subs"><a href="edit_dashboard.php">CSS Tool</a></li>
			<li class="subs"><a href="ssh_access.php">SSH Access</a></li>
			<li class="subs"><a href="jitter_test.php">DMR Jitter Test</a></li>
			<li class="subs"><a href="calibration.php">MMDVM Calibration</a></li>
			<li class="subs"><a href="config_manager.php">Config Manager</a></li>
			<li class="subs"><a href="log_manager.php">Log Manager</a></li>
			<li class="subs"><a href="filemanager.php">File Manager</a></li>
			<li class="subs"><a href="/config/changelog.php">ChangeLog</a></li>
		    </ul>
		</li>
	    </ul>
	</div>
    </p>
</div>
