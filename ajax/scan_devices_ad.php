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

$ds = ldap_connect($ldap_host);
if ($ds) {
    $r = ldap_bind($ds, $ldap_user, $ldap_pass);
    if ($r == 1) {
        $sr = ldap_search($ds, "OU=Computers,OU=My OU,DC=myserver,DC=com", "(objectClass=Computer)", array("cn", "dn"));
        $info = ldap_get_entries($ds, $sr);
        $c_devices = []; //DEBUG
        for ($i = 0; $i < $info["count"]; $i++) {
            array_push($c_devices, $info[$i]["cn"][0]); //DEBUG
            ////0. Get MAC client AD
            //$exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $info[$i]["cn"][0] . ' | where {$_.MACAddress -ne $null } | Format-List MACAddress"' . " 2>&1");
            //$exec = preg_replace('/\s+/', '', $exec);
            //$mac_arr = explode('MACAddress:', $exec); 
            //array_splice($mac_arr, 0, 1);
            ////--> $mac_arr skrg adalah Array MAC <--

            //$stmt = $pdo->prepare("SELECT * FROM `clients`");
            //$stmt->execute();
            //foreach ($stmt as $row) {
            ////1. Get MAC dari DB (jika null, ambil lalu upload)

            ////2. Bandingkan MAC
            //Tip (kode magang):
            // string sqlWhere = "WHERE `client_specs`.`mac` LIKE '%";
            // int b = 0;
            // for (int i = 0; i < getMAC.length(); i++) {
            //     if (getMAC[i] == '/') {
            //         if (b > 0) {
            //             sqlWhere += " AND `client_specs`.`mac` LIKE '%";
            //         }
            //         sqlWhere += getMAC.substr(b, i - b);
            //         sqlWhere += "%'";
            //         b = i + 1;
            //     }
            // }

            ////3. Jika MAC nya unique (tidak ada di DB (no. 2)), tambahkan di DB
            ////INSERT table clients (nama pc doang).
            ////INSERT table status (id client doang).
            ////Apps & Network: biarin kosong.
            //}

            ////Jika sudah scan / tambahkan, munculkan alert "Devices yang ditambahkan ...."
        }
    }
}
echo "DEBUG: "; print_r($c_devices); //DEBUG
