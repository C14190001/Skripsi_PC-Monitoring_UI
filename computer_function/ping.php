<?php
$target = $_POST["target"];
exec("ping -n 4 " . $target, $output, $result);
foreach ($output as $out) {
    echo $out . "<br>";
}
