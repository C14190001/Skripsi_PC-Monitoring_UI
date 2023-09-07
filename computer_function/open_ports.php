<?php
$target = $_POST['target'];
exec('powershell -command "Invoke-Command -ComputerName "' . $target . '" -ScriptBlock { netstat -an | findstr "LISTENING"; }" 2>&1', $output);

$open_ports = [];
foreach ($output as $port) {
    $s1 = str_replace(array("::", "[", "]"), array(""), $port);
    $s2 = substr($s1, strpos($s1, ":", 0) + 1, 5);
    $s3 = str_replace(" ", "", $s2);

    if (!in_array($s3, $open_ports)) {
        array_push($open_ports, $s3);
    }
}
sort($open_ports);

//print_r($open_ports);

//echo $target . "'s open ports:\n";
for ($i = 0; $i < count($open_ports); $i++) {
    echo $open_ports[$i];
    if($i < count($open_ports) - 1){
        echo ", ";
    }
}
echo ".";
