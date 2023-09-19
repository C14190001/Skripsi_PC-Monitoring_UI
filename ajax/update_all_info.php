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

$stmt = $pdo->prepare("SELECT `client_id`,`name` FROM `clients`");
$stmt->execute();
foreach ($stmt as $row) {
    //Update semuanya (Kecuali status)
    $id = $row['client_id'];
    $target = $row['name'];
    
    //Cek koneksi
    exec("ping -n 1 " . $target, $output, $result);
    foreach ($output as $a) {
        if (str_contains($a, "Destination host unreachable") || str_contains($a, "Request timed out")) {
            $result = 1; //Disconnected
            break;
        }
    }
    if ($result == 0) {
        //OS
        $get_val = explode("\n", str_replace("Caption : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem  -ComputerName ' . $row['name'] . ' | Format-List Caption"' . " 2>&1")));
        $get_val2 = [];
        foreach ($get_val as $a) {
            if (!empty($a)) {
                array_push($get_val2, $a);
            }
        }
        echo 'OS: <span id="os">' . $get_val2[0] . '</span><br>';
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `os` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val2[0], $id]);

        //CPU
        $get_val = explode("\n", str_replace("Name : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_Processor -ComputerName ' . $row['name'] . ' | Format-List Name"' . " 2>&1")));
        $get_val2 = [];
        foreach ($get_val as $a) {
            if (!empty($a)) {
                array_push($get_val2, $a);
            }
        }
        echo 'CPU: <span id="cpu">' . $get_val2[0] . '</span><br>';
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `cpu` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val2[0], $id]);

        //GPU
        $get_val = explode("\n", str_replace("Name : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_VideoController -ComputerName ' . $row['name'] . ' | Format-List Name"' . " 2>&1")));
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
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `i_gpu` = ? , `e_gpu` = ? WHERE `client_id` = ?");
        $stmt3->execute([$igpu, $egpu, $id]);

        //RAM
        $get_val = round(str_replace("TotalVisibleMemorySize : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $row['name'] . ' | Format-List TotalVisibleMemorySize"' . " 2>&1")) / 1000000, 2);
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `ram` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val, $id]);

        //Memory
        $get_val = round(str_replace("Size : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $row['name'] . ' | Format-List Size"' . " 2>&1")) / 1073741824, 2);
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `mem` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val, $id]);

        //IP & MAC Address
        $exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $row['name'] . ' | where {$_.MACAddress -ne $null } | Format-List IPAddress"' . " 2>&1");
        $exec = preg_replace('/\s+/', '', $exec);
        $exec = str_replace(array('{', '}'), '', $exec);
        $ip_arr = explode('IPAddress:', $exec);
        array_splice($ip_arr, 0, 1);
        for ($i = 0; $i < count($ip_arr); $i++) {
            if (empty($ip_arr[$i])) {
                $ip_arr[$i] = "N/A";
            }
        }
        $exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $row['name'] . ' | where {$_.MACAddress -ne $null } | Format-List MACAddress"' . " 2>&1");
        $exec = preg_replace('/\s+/', '', $exec);
        $mac_arr = explode('MACAddress:', $exec);
        array_splice($mac_arr, 0, 1);
        $stmt3 = $pdo->prepare("DELETE FROM `clients_network` WHERE `client_id` = ?");
        $stmt3->execute([$id]);
        for ($i = 0; $i < count($mac_arr); $i++) {
            if (str_contains($ip_arr[$i], ',')) {
                $temp_ip = explode(',', $ip_arr[$i]);
                for ($j = 0; $j < count($temp_ip); $j++) {
                    $stmt3 = $pdo->prepare("INSERT INTO `clients_network` (`network_id`, `client_id`, `ip`, `mac`) VALUES (NULL, ?, ?, ?)");
                    $stmt3->execute([$id, $temp_ip[$j], $mac_arr[$i]]);
                }
            } else {
                $stmt3 = $pdo->prepare("INSERT INTO `clients_network` (`network_id`, `client_id`, `ip`, `mac`) VALUES (NULL, ?, ?, ?)");
                $stmt3->execute([$id, $ip_arr[$i], $mac_arr[$i]]);
            }
        }

        //Apps
        $Apps = explode("\n", str_replace(array("DisplayName : "), array(""), shell_exec('powershell -command "Invoke-Command -ComputerName "' . $row['name'] . '" -FilePath ..\computer_info\installed_apps.ps1"' . " 2>&1")));
        $StrApps = [];
        foreach ($Apps as $app) {
            if (!empty($app)) {
                array_push($StrApps, $app);
            }
        }
        $stmt3 = $pdo->prepare("DELETE FROM `clients_app` WHERE `client_id` = ?");
        $stmt3->execute([$id]);
        foreach ($StrApps as $a) {
            $stmt3 = $pdo->prepare("INSERT INTO `clients_app` (`app_id`, `client_id`, `app`) VALUES (NULL, ?, ?)");
            $stmt3->execute([$id, $a]);
        }
    }
}
