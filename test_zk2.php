<?php
require 'vendor/autoload.php';
$zk = new \Rats\Zkteco\Lib\ZKTeco('10.10.2.141', 4370);
if ($zk->connect()) {
    $users = $zk->getUser();
    var_dump(is_array($users) ? count($users) : $users);
    $zk->disconnect();
} else {
    echo "Connection failed\n";
}
