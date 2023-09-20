<?php
require '..\config.php';
require 'client_info.php';
require 'pdo_init.php';

$id = $_POST['id'];
$stmt = $pdo->prepare("SELECT * FROM `clients` WHERE `client_id` = " . $id);
$stmt->execute();
foreach ($stmt as $row) {
    $target = $row['name'];

    //CONN Status
    exec("ping -n 1 " . $target, $output, $result);
    $stmt = $pdo->prepare("UPDATE `clients_status` SET `connection_status` = ? WHERE `client_id` = ?");

    foreach ($output as $a) {
        if (str_contains($a, "Destination host unreachable") || str_contains($a, "Request timed out")) {
            $result = 1; //Disconnected
            break;
        }
    }

    if ($result == 0) {
        //Connected
        echo "Connection status: <span style=\"color:green\">Connected</span><br>";
        echo "CPU Usage: " . getCpuUsage($target, $id) . "%<br>";
        $ramUsage = getRam($target, $result, $id, 1);
        echo "RAM Usage: " . $ramUsage[1] . " GB (" . $ramUsage[2]  . "%)<br>";
        $hddUsage = getHdd($target, $result, $id, 1);
        echo "Memory Usage: " . $hddUsage[1] . " GB (" . $hddUsage[2] . "%)<br>";
        $uptime = getUptime($target, $result, $id, 2);
        echo "Uptime: ",  $uptime[0], " Days ", $uptime[1], " Hours ", $uptime[2], " Minutes ", $uptime[3], " Seconds";
    } else {
        echo "Connection status: <span style=\"color:red\">Disconnected</span><br>";
        $stmt2 = $pdo->prepare("UPDATE `clients_status` SET `cpu_usage` = ? WHERE `client_id` = ?");
        $stmt2->execute([0, $id]);
        $stmt3 = $pdo->prepare("UPDATE `clients_status` SET `ram_usage` = ? WHERE `client_id` = ?");
        $stmt3->execute([0, $id]);
        $stmt3 = $pdo->prepare("UPDATE `clients_status` SET `mem_usage` = ? WHERE `client_id` = ?");
        $stmt3->execute([0, $id]);
        $stmt3 = $pdo->prepare("UPDATE `clients_status` SET `last_bootup` = ? WHERE `client_id` = ?");
        $stmt3->execute(["N/A", $id]);
    }
}
