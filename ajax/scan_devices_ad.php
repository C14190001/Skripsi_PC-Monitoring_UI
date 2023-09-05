<?php
require '..\config.php';
$ds = ldap_connect($ldap_host);
if ($ds) {
    $r = ldap_bind($ds, $ldap_user, $ldap_pass);
    if ($r == 1) {
        $sr = ldap_search($ds, "OU=Computers,OU=My OU,DC=myserver,DC=com", "(objectClass=Computer)", array("cn", "dn"));
        $info = ldap_get_entries($ds, $sr);
        for ($i = 0; $i < $info["count"]; $i++) {
            //Nama Client di AD: $info[$i]["cn"][0]
            //Check jika di DB Clients sudah ada atau belum (melalui 'MAC Address (unique)' atau 'nama PC'?)
            //Jika belum ada, tambahkan di DB

            //Jika sudah scan / tambahkan, munculkan alert "Devices yang ditambahkan ...."
        }
    }
}
