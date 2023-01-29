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

include_once $_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';

?>

<div style="text-align:left;font-weight:bold;"><?php echo $lang['service_status'];?></div>
<div class="status-grid">
  <?php if (getFWstate()=='0' ) { ?>
  <div class='grid-item paused-mode-cell' title="Disabled">Firewall</div>
  <?php } else { ?>
  <div class="grid-item <?php getServiceStatusClass(getFWstate()); ?>">Firewall</div>
  <?php } ?>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('MMDVMHost')); ?>">MMDVMHost</div>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('DMRGateway')); ?>">DMRGateway</div>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('ircddbgatewayd')); ?>">ircDDBGateway</div>  
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('YSFGateway')); ?>">YSFGateway</div>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('YSF2DMR')); ?>">YSF2DMR</div>
  <?php if (getPSRState()=='0' ) { ?>
  <div class='grid-item paused-mode-cell' title="Disabled">Pi-Star Remote</div>
  <?php } else { ?>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('/usr/local/sbin/pistar-remote',true)); ?>">Pi-Star Remote</div> 
  <?php } ?>

  <?php if (getCronState()=='0' ) { ?>
  <div class='grid-item paused-mode-cell' title="Disabled">Cron</div>
  <?php } else { ?>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('cron')); ?>">Cron</div>
  <?php } ?>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('NXDNGateway')); ?>">NXDNGateway</div> 
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('M17Gateway')); ?>">M17Gateway</div> 
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('NXDNParrot')); ?>">NXDNParrot</div> 
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('NXDN2DMR')); ?>">NXDN2DMR</div> 
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('APRSGateway')); ?>">APRSGateway</div>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('dstarrepeaterd')); ?>">D-Star Repeater</div> 
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('YSFParrot')); ?>">YSFParrot</div>

  <div class="grid-item <?php getServiceStatusClass(autoAPenabled()); ?>">Auto AP</div>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('P25Gateway')); ?>">P25Gateway</div> 
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('P25Parrot')); ?>">P25Parrot</div> 
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('DAPNETGateway')); ?>">DAPNETGateway</div> 
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('timeserverd')); ?>">TimeServer</div>
  <?php if (getPSWstate()=='0' ) { ?>
  <div class='grid-item paused-mode-cell' title="Disabled">Pi-Star Watchdog</div>
  <?php } else { ?>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('/usr/local/sbin/pistar-watchdog',true)); ?>">Pi-Star Watchdog</div> 
  <?php } ?>

  <div class="grid-item <?php getServiceStatusClass(UPnPenabled()); ?>">UPnP</div>  
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('gpsd'));  ?>">GPSd</div>  
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('NextionDriver'));  ?>">NextionDriver</div>  
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('/usr/local/sbin/pistar-keeper',true)); ?>">PiStar-Keeper</div>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('DGIdGateway')); ?>">DGIdGateway</div> 
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('DMR2YSF')); ?>">DMR2YSF</div>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('YSF2P25')); ?>">YSF2P25</div>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('YSF2NXDN')); ?>">YSF2NXDN</div>
  <div class="grid-item <?php getServiceStatusClass(isProcessRunning('DMR2NXDN')); ?>">DMR2NXDN</div>
</div>

<br />
