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

$get_val = explode("\n", str_replace("Name : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_VideoController -ComputerName ' . $target . ' | Format-List Name"' . " 2>&1")));
$get_val2 = [];
foreach ($get_val as $a) {
    if (!empty($a)) {
        array_push($get_val2, $a);
    }
}

$igpu = "N/A";
$egpu = "N/A";
if (count($get_val2) > 1) {
    $egpu = $get_val2[0];
    $igpu = $get_val2[1];
} else {
    $igpu = $get_val2[0];
}
echo 'iGPU: ' . $igpu . '. eGPU: ' . $egpu;
$stmt3 = $pdo->prepare("UPDATE `clients` SET `i_gpu` = ? , `e_gpu` = ? WHERE `client_id` = ?");
$stmt3->execute([$igpu, $egpu, $id]);
