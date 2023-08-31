<?php
$ds = ldap_connect("LDAP://192.168.56.103"); //ADSI
if ($ds) {
    $r = ldap_bind($ds, "Administrator@myserver.com", "abcd1234."); //Administrator
    if ($r == 1) {
        $sr = ldap_search($ds, "OU=Computers,OU=My OU,DC=myserver,DC=com", "(objectClass=Computer)", array("cn", "dn")); //Location
        $info = ldap_get_entries($ds, $sr);
        //echo "Search returns ".ldap_count_entries($ds, $sr) ." results:<br><br>"; //Count
        for ($i = 0; $i < $info["count"]; $i++) {
            //echo $info[$i]["dn"] . "<br>"; //Location
            echo $info[$i]["cn"][0]; //Value
            if ($i < ($info["count"] - 1)) {
                echo ",";
            }
        }
    } else {
        echo "LDAP bind Failed.";
    }
    ldap_close($ds);
} else {
    echo "Unable to connect to LDAP.";
}
