<?php
$host="MyServer-CL1"; //Target

exec("tracert " . $host, $output, $result);
print_r($output);
echo "<br><br>";
if ($result == 0){
    echo "Trace Route success!";
}
else{
    echo "Trace Route Fail!";
}
?>