<?php

if (!isset($_SESSION) || !is_array($_SESSION)) {
    session_id('pistardashsess');
    session_start();
}

$cmdoutput = array();
# Avoid that FS is remounted RO while upgrading, the process could take some time to finish
exec('systemctl stop pistar-watchdog.timer > /dev/null 2>&1');
exec('systemctl stop pistar-watchdog.service > /dev/null 2>&1');
exec('sudo mount -o remount,rw /');
$cmdresult = exec('sudo /usr/bin/unattended-upgrade -v', $cmdoutput, $retvalue);
exec('systemctl start pistar-watchdog.service > /dev/null 2>&1');
exec('systemctl start pistar-watchdog.timer > /dev/null 2>&1');

if ($retvalue == 0) {
    echo "<br /><h2 class='left'>** Success **</h2>";
}
else {
    echo "<br /><h2 class='left'>!! Failure !!</h2>";
}

echo "<pre class='left'>";
foreach ($cmdoutput as $l) {
    $l = wordwrap($l, 100, "\n");
    echo "$l<br />";
}
echo "</pre><br />";
echo "<br />";
?>
