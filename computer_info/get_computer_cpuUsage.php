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

//Get CPU Usage
$cpuUsages = explode("\n", str_replace(array("PercentProcessorTime : "), array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_PerfFormattedData_PerfOS_Processor  -ComputerName ' . $target . ' | Format-List PercentProcessorTime"' . " 2>&1")));
$cpuUsages2 = [];
foreach ($cpuUsages as $core) {
    if (!empty($core)) {
        array_push($cpuUsages2, $core);
    } else {
        array_push($cpuUsages2, 0);
    }
}

//Average
$total = 0;
$n_core = 0;
foreach ($cpuUsages2 as $core) {
    $total += $core;
    $n_core++;
}
echo round($total,2) . "%";
$stmt3 = $pdo->prepare("UPDATE `clients_status` SET `cpu_usage` = ? WHERE `client_id` = ?");
$stmt3->execute([round($total,2), $id]);
