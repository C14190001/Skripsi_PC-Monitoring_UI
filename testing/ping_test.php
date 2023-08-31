<?php
$host = "MyServer-CL1";

echo "[Ping " . $host . "]<br>";
exec("ping -n 1 " . $host, $output, $result);
//echo substr($output[5],4,strlen($output[5])-5) . ".<br>";

foreach ($output as $o) {
    echo $o . "<br>";
}

echo "<br>STATUS: ";
if ($result == 0) {
    echo "Ping success!";
} else {
    echo "Ping Fail!";
}
