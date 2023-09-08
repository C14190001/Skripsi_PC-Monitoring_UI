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

$ram_cap = round(str_replace("TotalVisibleMemorySize : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List TotalVisibleMemorySize"' . " 2>&1")) / 1000000, 2);
$ram_free = round(str_replace(array("FreePhysicalMemory : "), array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List FreePhysicalMemory"' . " 2>&1")) / 1000000, 2);
$ram_usage = $ram_cap - $ram_free;
$ram_usage_per = round(($ram_usage / $ram_cap) * 100, 2);
$stmt3 = $pdo->prepare("UPDATE `clients_status` SET `ram_usage` = ? WHERE `client_id` = ?");
$stmt3->execute([$ram_usage, $id]);
echo $ram_usage . " GB (" . $ram_usage_per . "%)";