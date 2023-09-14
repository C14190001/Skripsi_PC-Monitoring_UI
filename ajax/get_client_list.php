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

$stmt = $pdo->prepare("SELECT * FROM `clients`");
$stmt->execute();
$i = 0;
foreach ($stmt as $row) {
    echo '<button type="button" class="btn btn-light w-100 p-1" onclick=\'get_client_detail(' .  $row['client_id'] . ')\'>
            <div class="container">
                <img src=\'icons\pc-display.svg\' alt=\'PC\' style=\'width:40px; float:right;\'>
                <div class="row">
                    <div class="col-1">';
                        exec("ping -n 1 " . $row['name'], $output, $result);
                        $stmt2 = $pdo->prepare("UPDATE `clients_status` SET `connection_status` = ? WHERE `client_id` = ?");
                        if ($result == 0) {
                            echo "<span style=\"color:green;font-size: 20px;\">⦿</span>";
                            $stmt2->execute([1, $row['client_id']]);
                        } else {
                            echo "<span style=\"color:red;font-size: 20px;\">⦿</span>";
                            $stmt2->execute([0, $row['client_id']]);
                        }
    echo '          </div>
                    <div class="col" style="text-align: left;"><b>' . $row['name'] . '</b></div>
                </div>
                    <div class="row">
                        <div class="col" style="vertical-align: top; text-align: left;">';
                            //IP Address
                            $stmt2 = $pdo->prepare("SELECT ip from clients_network WHERE client_id = " . $row['client_id']);
                            $stmt2->execute();
                            $c = 0;
                            foreach ($stmt2 as $row2) {
                                if ($c > 1) {
                                    echo '...';
                                    break;
                                }
                                echo '• ' . $row2['ip'];
                                echo '<br>';
                                $c++;
                            }
    echo "              </div>
                    <div class=\"col-1\"></div>
                </button><br><br>";
    $i++;
}
