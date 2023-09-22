<?php
require '..\config.php';
require 'pdo_init.php';
$id = $_POST['id'];

$stmt = $pdo->prepare("DELETE FROM `clients_network` WHERE `clients_network`.`client_id` = " . $id);
$stmt->execute();
$stmt = $pdo->prepare("DELETE FROM clients_status WHERE `clients_status`.`client_id` = " . $id);
$stmt->execute();
$stmt = $pdo->prepare("DELETE FROM clients_app WHERE `clients_app`.`client_id` = " . $id);
$stmt->execute();
$stmt = $pdo->prepare("DELETE FROM clients WHERE `clients`.`client_id` = " . $id);
$stmt->execute();