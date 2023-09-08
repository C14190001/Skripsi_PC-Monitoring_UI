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
$target = $_POST["target"];
$id = $_POST["id"];

$get_val = round(str_replace("Size : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List Size"' . " 2>&1")) / 1073741824, 2);
echo $get_val . ' GB';
$stmt3 = $pdo->prepare("UPDATE `clients` SET `mem` = ? WHERE `client_id` = ?");
$stmt3->execute([$get_val, $id]);
