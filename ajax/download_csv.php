<?php
require '..\config.php';
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

//Sebelum ambil data: Update all clients (status_only)
//Karena ada cpu/ram/mem usage

$stmt = $pdo->prepare("
SELECT `clients`.`client_id`, `name`, `os`, `cpu`, `i_gpu`, `e_gpu`, `ram`, `mem`, GROUP_CONCAT(`ip` SEPARATOR ', ') AS `ip`, GROUP_CONCAT(`mac` SEPARATOR ', ') AS `mac`, GROUP_CONCAT(`app` SEPARATOR ', ') AS `app`, `cpu_usage`, `ram_usage`, `mem_usage`, `last_bootup`, `connection_status` 
FROM `clients` 
LEFT JOIN `clients_status` ON `clients`.`client_id` = `clients_status`.`client_id` 
LEFT JOIN `clients_network` ON `clients`.`client_id` = `clients_network`.`client_id`
LEFT JOIN `clients_app` ON `clients`.`client_id` = `clients_app`.`client_id`
GROUP BY `client_id`
ORDER BY `clients`.`client_id` ASC;");
$stmt->execute();

$delimiter = ",";
$filename = "clients_" . date('Y-m-d H:i:s') . ".csv";
$f = fopen('php://memory', 'w');
$fields = array('client_id', 'name', 'os', 'cpu', 'i_gpu', 'e_gpu', 'ram', 'mem', 'ip', 'mac', 'app', 'cpu_usage', 'ram_usage', 'mem_usage', 'last_bootup', 'connection_status');
fputcsv($f, $fields, $delimiter);

foreach ($stmt as $row) {
    $lineData = array($row['client_id'], $row['name'], $row['os'], $row['cpu'], $row['i_gpu'], $row['e_gpu'], $row['ram'], $row['mem'], $row['ip'], $row['mac'], $row['app'], $row['cpu_usage'], $row['ram_usage'], $row['mem_usage'], $row['last_bootup'], $row['connection_status']);
    fputcsv($f, $lineData, $delimiter);
}

fseek($f, 0);
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '";');
fpassthru($f);