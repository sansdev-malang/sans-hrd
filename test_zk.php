<?php
require 'vendor/autoload.php';
$zk = new \Rats\Zkteco\Lib\ZKTeco('10.10.2.141', 4370);
if ($zk->connect()) {
    $logs = $zk->getAttendance();
    var_dump(is_array($logs) ? count($logs) : $logs);
    $zk->disconnect();
} else {
    echo "Connection failed\n";
}
