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

$get_val = explode("\n", str_replace("Caption : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem  -ComputerName ' . $target . ' | Format-List Caption"' . " 2>&1")));
$get_val2 = [];
foreach ($get_val as $a) {
    if (!empty($a)) {
        array_push($get_val2, $a);
    }
}
echo $get_val2[0];
$stmt3 = $pdo->prepare("UPDATE `clients` SET `os` = ? WHERE `client_id` = ?");
$stmt3->execute([$get_val2[0], $id]);
