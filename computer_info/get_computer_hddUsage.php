<?php
require '../config.php';
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
$id = $_POST['id'];
$target = $_POST["target"];

$hdd_cap = round(str_replace("Size : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List Size"' . " 2>&1")) / 1073741824, 2);
$hdd_free = round(str_replace(array("FreeSpace : "),array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List FreeSpace"' . " 2>&1"))/1073741824, 2);
$hdd_usage = $hdd_cap - $hdd_free;
$hdd_usage_per = round(($hdd_usage / $hdd_cap) * 100, 2);
$stmt3 = $pdo->prepare("UPDATE `clients_status` SET `mem_usage` = ? WHERE `client_id` = ?");
$stmt3->execute([$hdd_usage, $id]);
echo $hdd_usage . " GB (" . $hdd_usage_per . "%)";

