<?php
require '..\config.php';
require 'pdo_init.php';
require 'client_info.php';

//Update informasi (kecuali status) dari SEMUA Client.
$stmt = $pdo->prepare("SELECT `client_id`,`name` FROM `clients`");
$stmt->execute();
foreach ($stmt as $row) {
    $id = $row['client_id'];
    $target = $row['name'];

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
    }
}
