<?php
require '..\config.php';
require 'pdo_init.php';
require 'client_info.php';

$ad_client = $_POST['ad_client'];

//Cek jika Client apakah sudah ada di DB.
$stmt = $pdo->prepare('SELECT `name` FROM `clients` WHERE name = \'' . $ad_client.'\'');
$stmt->execute();
$in_db = false;
foreach ($stmt as $row) {
    $in_db = true;
    break;
}

if (!$in_db) {
    //Cek koneksi
    if (getConnection($ad_client, -1) == 0) {
        $ipMAC = getIpMac($ad_client, 0, -1, 3);
        $mac_arr = [];
        for ($j = 1; $j < count($ipMAC); $j += 2) {
            $mac_arr[] = $ipMAC[$j];
        }
        $ip_arr = [];
        for ($j = 0; $j < count($ipMAC); $j += 2) {
            $ip_arr[] = $ipMAC[$j];
        }

        //Membandingkan MAC
        $sqlWhere = "SELECT DISTINCT `clients`.`client_id` FROM `clients` 
        LEFT JOIN `clients_network` ON `clients`.`client_id` = `clients_network`.`client_id` 
        WHERE `clients_network`.`mac` LIKE '%";
        for ($j = 0; $j < count($mac_arr); $j++) {
            $sqlWhere .= $mac_arr[$j] . "%' ";
            if ($j < count($mac_arr) - 1) {
                $sqlWhere .= "OR `clients_network`.`mac` LIKE '%";
            }
        }
        $is_null = true;
        $stmt = $pdo->prepare($sqlWhere);
        $stmt->execute();
        foreach ($stmt as $row) {
            $is_null = false;
            break;
        }

        if ($is_null) {
            //Add to Clients DB
            $stmt = $pdo->prepare("INSERT INTO `clients` (`client_id`, `name`) VALUES (NULL, ?);");
            $stmt->execute([$ad_client]);

            //Get new ID
            $stmt = $pdo->prepare("SELECT `client_id` as `c` FROM `clients` ORDER BY `client_id` DESC LIMIT 1;");
            $stmt->execute();
            foreach ($stmt as $row) {
                $new_id = $row['c'];
            }

            //Add new Status row
            $stmt = $pdo->prepare("INSERT INTO `clients_status` (`status_id`, `client_id`) VALUES (NULL, ?);");
            $stmt->execute([$new_id]);

            //Add Networks
            for ($j = 0; $j < count($mac_arr); $j++) {
                if (str_contains($ip_arr[$j], ',')) {
                    $temp_ip = explode(',', $ip_arr[$j]);
                    for ($k = 0; $k < count($temp_ip); $k++) {
                        $stmt3 = $pdo->prepare("INSERT INTO `clients_network` (`network_id`, `client_id`, `ip`, `mac`) VALUES (NULL, ?, ?, ?)");
                        $stmt3->execute([$new_id, $temp_ip[$k], $mac_arr[$j]]);
                    }
                } else {
                    $stmt3 = $pdo->prepare("INSERT INTO `clients_network` (`network_id`, `client_id`, `ip`, `mac`) VALUES (NULL, ?, ?, ?)");
                    $stmt3->execute([$new_id, $ip_arr[$j], $mac_arr[$j]]);
                }
            }
            echo "<span style=\"color:green;\">Client " . $ad_client . " is added to DB.</span>";
        } else {
            echo "<span style=\"color:red;\">Client " . $ad_client . " is already in DB.</span>";
        }
    } else {
        echo "<span style=\"color:red;\">Client " . $ad_client . " is disconnected.</span>";
    }
} else {
    echo "<span style=\"color:red;\">Client " . $ad_client . " is already in DB.</span>";
}
echo "<br>";
