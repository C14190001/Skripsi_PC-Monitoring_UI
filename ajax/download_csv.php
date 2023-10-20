<?php
require '..\config.php';
require 'pdo_init.php';

$stmt = $pdo->prepare("
SELECT `clients`.`client_id`, `name`, `os`, `cpu`, `i_gpu`, `e_gpu`, `ram`, `mem`, GROUP_CONCAT(`ip` SEPARATOR '|') AS `ip`, GROUP_CONCAT(`mac` SEPARATOR '|') AS `mac`, GROUP_CONCAT(`app` SEPARATOR '|') AS `app`
FROM `clients` 
LEFT JOIN `clients_network` ON `clients`.`client_id` = `clients_network`.`client_id`
LEFT JOIN `clients_app` ON `clients`.`client_id` = `clients_app`.`client_id`
GROUP BY `client_id`
ORDER BY `clients`.`client_id` ASC;");
$stmt->execute();

$delimiter = ",";
$filename = "clients_" . date('Y-m-d H:i:s') . ".csv";
$f = fopen('php://memory', 'w');
$fields = array('client_id', 'name', 'os', 'cpu', 'i_gpu', 'e_gpu', 'ram', 'mem', 'ip', 'mac', 'app');
fputcsv($f, $fields, $delimiter);

foreach ($stmt as $row) {
    $lineData = array($row['client_id'], $row['name'], $row['os'], $row['cpu'], $row['i_gpu'], $row['e_gpu'], $row['ram'], $row['mem'], $row['ip'], $row['mac'], $row['app']);
    fputcsv($f, $lineData, $delimiter);
}

fseek($f, 0);
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '";');
fpassthru($f);