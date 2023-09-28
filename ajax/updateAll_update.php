<?php
require '..\config.php';
require 'pdo_init.php';
require 'client_info.php';

$id = $_POST['id'];
$target = $_POST['name'];

//Cek koneksi
$result = getConnection($target, $id);

if ($result == 0) {
    getOs($target, $result, $id);
    getCpu($target, $result, $id);
    getGpu($target, $result, $id);
    getram($target, $result, $id, 0);
    getHdd($target, $result, $id, 0);
    getIpMac($target, $result, $id, 0);
    getApps($target, $result, $id);
    echo "<span style=\"color:green;\">" . $target . " has been updated.</span>";
} else {
    echo "<span style=\"color:red;\">" . $target . " is disconnected.</span>";
}
echo "<br>";