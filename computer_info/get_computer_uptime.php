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

$last_bootup_time = str_replace("LastBootUpTime : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List LastBootUpTime"' . " 2>&1"));
$get_uptime= strtotime($last_bootup_time);
$last_bootup_time = strtotime($last_bootup_time); 
$last_bootup_time = date("Y-m-d H:i:s",$last_bootup_time); 

$stmt3 = $pdo->prepare("UPDATE `clients_status` SET `last_bootup` = '?' WHERE `client_id` = ?");
$stmt3->execute([$last_bootup_time, $id]);

$get_current_time = strtotime(shell_exec('powershell -command "Invoke-Command -ComputerName ' . $target . ' -ScriptBlock {get-date}"' . " 2>&1")); 
echo ($get_current_time-$get_uptime) . " Seconds";