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
$Apps = explode("\n",str_replace(array("DisplayName : "),array(""), shell_exec('powershell -command "Invoke-Command -ComputerName ' . $target . ' -FilePath .\installed_apps.ps1"' . " 2>&1")));
$StrApps = [];
foreach($Apps as $app){
    if(!empty($app)){
        array_push($StrApps,$app);
    }
}

//Delete previous data
$stmt = $pdo->prepare("DELETE FROM `clients_app` WHERE `client_id` = ?");
$stmt->execute([$id]);

//Insert new data
foreach($StrApps as $a){
    $stmt = $pdo->prepare("INSERT INTO `clients_app` (`app_id`, `client_id`, `app`) VALUES (NULL, ?, ?)");
    $stmt->execute([$id, $a]);
}

//Preview data
foreach($StrApps as $a){
    echo '<li>' . $a . '</li>';
}