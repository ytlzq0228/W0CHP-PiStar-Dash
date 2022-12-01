<div class="header">
    <div class="SmallHeader shLeft">Hostname: <?php echo exec('cat /etc/hostname'); ?></div><div class="SmallHeader shRight">Pi-Star: <?php echo $_SESSION['PiStarRelease']['Pi-Star']['Version'].'<br />';?>
    <?php if (constant("AUTO_UPDATE_CHECK") == "true") { ?> 
    <div id="CheckUpdate"><?php echo $version; system('/usr/local/sbin/pistar-check4updates'); ?></div></div>
    <?php } else { ?>
    <div id="CheckUpdate"><?php echo $version; ?></div></div>
    <?php } ?>    
    <h1>Pi-Star Digital Voice - Expert</h1>
    <p>
	<div class="navbar">
	    <a class="menuconfig" href="/admin/configure.php"><?php echo $lang['configuration'];?></a>
	    <a class="menubackup" href="/admin/config_backup.php"><?php echo $lang['backup_restore'];?></a>
	    <a class="menuupgrade" href="/admin/expert/upgrade.php">Upgrade</a>
	    <a class="menuupdate" href="/admin/update.php"><?php echo $lang['update'];?></a>
	    <a class="menuadmin" href="/admin/"><?php echo $lang['admin'];?></a>
	    <a class="menulive" href="/live/">Live Caller</a>
	    <a class="menudashboard" href="/"><?php echo $lang['dashboard'];?></a>
	</div>

    </p>
</div>
