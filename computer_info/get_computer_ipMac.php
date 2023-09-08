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
$id = $_POST['id'];
$target = $_POST["target"];

//IP Address
$exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $target . ' | where {$_.MACAddress -ne $null } | Format-List IPAddress"' . " 2>&1");
$exec = preg_replace('/\s+/', '', $exec); //Hapus Whitespace
$exec = str_replace(array('{', '}'), '', $exec); //Hapus karakter '{' dan '}' (hanya untuk IP Address)
$ip_arr = explode('IPAddress:', $exec); //Ubah jadi Array
array_splice($ip_arr, 0, 1); //Buang index ke-0 (selalu kosong entah kenapa...)
//Yang kosong akan diubah ke N/A (Jangan dibuang, karena jumlah IP = jumlah MAC)
for ($i = 0; $i < count($ip_arr); $i++) {
    if (empty($ip_arr[$i])) {
        $ip_arr[$i] = "N/A";
    }
}
//--> $ip_arr skrg adalah Array IP | Info: Perlu cek adanya IP ganda (ada karakter ',') <--

//MAC Address
$exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $target . ' | where {$_.MACAddress -ne $null } | Format-List MACAddress"' . " 2>&1");
$exec = preg_replace('/\s+/', '', $exec); //Hapus Whitespace
$mac_arr = explode('MACAddress:', $exec); //Ubah jadi Array
array_splice($mac_arr, 0, 1); //Buang index ke-0 (selalu kosong entah kenapa...)
//--> $mac_arr skrg adalah Array MAC <--

//Delete previous data
$stmt = $pdo->prepare("DELETE FROM `clients_network` WHERE `client_id` = ?");
$stmt->execute([$id]);

//Upload new data
for ($i = 0; $i < count($mac_arr); $i++) {
    if (str_contains($ip_arr[$i], ',')) {
        $temp_ip = explode(',', $ip_arr[$i]);
        for ($j = 0; $j < count($temp_ip); $j++) {
            $stmt = $pdo->prepare("INSERT INTO `clients_network` (`network_id`, `client_id`, `ip`, `mac`) VALUES (NULL, ?, ?, ?)");
            $stmt->execute([$id, $temp_ip[$j], $mac_arr[$i]]);
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO `clients_network` (`network_id`, `client_id`, `ip`, `mac`) VALUES (NULL, ?, ?, ?)");
        $stmt->execute([$id, $ip_arr[$i], $mac_arr[$i]]);
    }
}

//Preview Data
for ($i = 0; $i < count($mac_arr); $i++) {
    if (str_contains($ip_arr[$i], ',')) {
        $temp_ip = explode(',', $ip_arr[$i]);
        for ($j = 0; $j < count($temp_ip); $j++) {
            echo '<li>' . $temp_ip[$j] . " - " . $mac_arr[$i] . '</li>';
        }
    } else {
        echo '<li>' . $ip_arr[$i] . " - " . $mac_arr[$i] . '</li>';
    }
}
