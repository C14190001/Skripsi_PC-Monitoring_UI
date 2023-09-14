<?php
require '..\config.php';
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

//Ambil nama Client dari AD
$refresh_btn = false;
$dn_search = $_POST['dn_search'];
echo "<b>Search Results:</b><br>";
$ds = ldap_connect($ldap_host);
$ad_clients = [];
if ($ds) {
    $r = ldap_bind($ds, $ldap_user, $ldap_pass);
    if ($r == 1) {
        $sr = ldap_search($ds, $dn_search, "(objectClass=Computer)", array("cn", "dn"));
        $info = ldap_get_entries($ds, $sr);
        for ($i = 0; $i < $info["count"]; $i++) {
            array_push($ad_clients, $info[$i]["cn"][0]);
        }
    }
}
ldap_close($ds);

//Membandingkan IP
for ($i = 0; $i < count($ad_clients); $i++) {
    echo ($i + 1) . ". " . $ad_clients[$i] . ": ";

    //Cek koneksi
    exec("ping -n 1 " . $ad_clients[$i], $conn_output, $conn_result);
    foreach ($conn_output as $a) {
        if (str_contains($a, "Destination host unreachable")) {
            $conn_result = 1; //Disconnected
            break;
        }
    }

    if ($conn_result == 0) {
        //echo "MAC Array:<br>";
        $exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $ad_clients[$i] . ' | where {$_.MACAddress -ne $null } | Format-List MACAddress"' . " 2>&1");
        $exec = preg_replace('/\s+/', '', $exec);
        $mac_arr = explode('MACAddress:', $exec);
        array_splice($mac_arr, 0, 1);

        //print_r($mac_arr);
        //echo "<br><br>SQL code:<br>";

        $sqlWhere = "SELECT DISTINCT `clients`.`client_id` FROM `clients` 
            LEFT JOIN `clients_network` ON `clients`.`client_id` = `clients_network`.`client_id` 
            WHERE `clients_network`.`mac` LIKE '%";
        for ($j = 0; $j < count($mac_arr); $j++) {
            $sqlWhere .= $mac_arr[$j] . "%' ";
            if ($j < count($mac_arr) - 1) {
                $sqlWhere .= "OR `clients_network`.`mac` LIKE '%";
            }
        }
        //echo $sqlWhere;
        //echo "<hr>";

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
            $stmt->execute([$ad_clients[$i]]);

            //Get new ID
            $stmt = $pdo->prepare("SELECT `client_id` as `c` FROM `clients` ORDER BY `client_id` DESC LIMIT 1;");
            $stmt->execute();
            foreach ($stmt as $row) {
                $new_id = $row['c'];
            }

            //Add new Status row
            $stmt = $pdo->prepare("INSERT INTO `clients_status` (`status_id`, `client_id`) VALUES (NULL, ?);");
            $stmt->execute([$new_id]);

            //Get IP Address
            $exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $ad_clients[$i] . ' | where {$_.MACAddress -ne $null } | Format-List IPAddress"' . " 2>&1");
            $exec = preg_replace('/\s+/', '', $exec);
            $exec = str_replace(array('{', '}'), '', $exec);
            $ip_arr = explode('IPAddress:', $exec);
            array_splice($ip_arr, 0, 1);
            for ($j = 0; $j < count($ip_arr); $j++) {
                if (empty($ip_arr[$j])) {
                    $ip_arr[$j] = "N/A";
                }
            }

            ////Add MAC Address
            // for ($j = 0; $j < count($mac_arr); $j++) {
            //     $stmt = $pdo->prepare("INSERT INTO `clients_network` (`network_id`, `client_id`, `mac`) VALUES (NULL, ?, ?);");
            //     $stmt->execute([$new_id, $mac_arr[$j]]);
            // }

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
            echo "<span style=\"color:green;\">Added to DB.</span>";
            $refresh_btn = true;
        } else {
            echo "<span style=\"color:red;\">Client is in DB.</span>";
        }
    } else {
        echo "<span style=\"color:red;\">Client is disconnected.</span>";
    }
    echo "<br>";
}

if ($refresh_btn) {
    echo "<br>New clients have been added. You need to refresh the page.<br>";
    echo "<button class=\"btn btn-primary w-100\" onclick=\"window.location.reload();\">Refresh page</button>";
}
