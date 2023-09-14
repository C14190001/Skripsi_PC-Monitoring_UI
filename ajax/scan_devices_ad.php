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
        for ($i = 0; $i < $info["count"]; $i++) {
            echo $i . " MAC:<br>";

            //0. Get MAC client AD
            $exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $info[$i]["cn"][0] . ' | where {$_.MACAddress -ne $null } | Format-List MACAddress"' . " 2>&1");
            $exec = preg_replace('/\s+/', '', $exec);
            $mac_arr = explode('MACAddress:', $exec);
            array_splice($mac_arr, 0, 1);
            //--> $mac_arr skrg adalah Array MAC <--

            print_r($mac_arr);
            echo "<br>SQL CODE:<br>";

            $sqlWhere = "WHERE `client_specs`.`mac` LIKE '%";
            for ($i = 0; $i < count($mac_arr); $i++) {
                $sqlWhere .= $mac_arr[$i] . "%'";
                if ($i < count($mac_arr) - 1) {
                    $sqlWhere .= "AND `client_specs`.`mac` LIKE '%";
                }
            }
            echo $sqlWhere;

            //1. Jalankan SQL, query sqlWhere

            //2. Jika MAC nya unique (hasil query di DB kosong), tambahkan di DB
            //INSERT table clients (nama pc doang).
            //INSERT table status (id client doang).
            //Apps & Network: biarin kosong.

            //Jika sudah scan / tambahkan, munculkan alert "Devices yang ditambahkan ...."
        }
    }
}
