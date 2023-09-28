<?php
require '..\config.php';
require 'pdo_init.php';
require 'client_info.php';

//Ambil nama Client dari AD
$dn_search = $_POST['dn_search'];

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
for ($i = 0; $i < count($ad_clients); $i++) {
    echo $ad_clients[$i];
    if($i < (count($ad_clients)-1)){
        echo ',';
    }
}