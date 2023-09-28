<?php
require '..\config.php';
require 'pdo_init.php';
require 'client_info.php';

$clients = [];
$stmt = $pdo->prepare("SELECT `client_id`,`name` FROM `clients`");
$stmt->execute();
foreach ($stmt as $row) {
    array_push($clients, $row['client_id']);
    array_push($clients, $row['name']);
}

for ($i = 0; $i < count($clients); $i++) {
    echo $clients[$i];
    if($i < (count($clients)-1)){
        echo ',';
    }
}