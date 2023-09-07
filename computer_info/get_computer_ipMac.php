<?php
$target = $_POST["target"];
$address = explode("\n",str_replace(array("IPAddress","MACAddress", " : "),array("IP_","MAC_"), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $target . ' | where {$_.MACAddress -ne $null } | Format-List IPAddress, MACAddress"' . " 2>&1")));
$address2 = [];
foreach ($address as $a){
    if(!empty($a)){
        array_push($address2,$a);
    }
}
print_r($address2);
//DB: delete semua app where id ... + lalu upload / insert array nya