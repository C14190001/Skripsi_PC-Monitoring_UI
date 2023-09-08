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

$target = $_POST["target"];
$id = $_POST['id'];
exec("ping -n 1 " . $target, $output, $result);

$stmt = $pdo->prepare("UPDATE `clients_status` SET `connection_status` = ? WHERE `client_id` = ?");
if ($result == 0) {
    echo "<span style=\"color:green;font-size: 20px;\">⦿</span>";
    $stmt->execute([1, $id]);
} else {
    echo "<span style=\"color:red;font-size: 20px;\">⦿</span>";
    $stmt->execute([0, $id]);
}