<?php
//[ INIT ]
$dsn = "mysql:host=192.168.56.103;dbname='monitoring_db';charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, 'monitor_user', '', $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
//--------
//[ SELECT ]

$stmt = $pdo->prepare("SELECT * FROM `clients`");
$stmt->execute();
foreach ($stmt as $row) {
    echo 'Row ' . $row['client_id'] . ': ' . $row['name'] . ', ' . $row['os'] . ', ' . $row['cpu'] . ', ' . $row['gpu']. ', ' . $row['ram']. ', ' . $row['mem'];
}
//--------
//[ UPDATE / INSERT ]

$stmt = $pdo->prepare("UPDATE `clients` SET `name` = ? WHERE `client_id` = ?");
$stmt->execute(['wtf_pc2',1]);
//--------
