<?php
$target = $_POST["target"];
exec("ping -n 4 " . $target . " 2>&1", $output, $result);
if ($result == 0) {
    echo "[ ".$target."'s ping success ]\n";
} else {
    echo "[ ".$target."'s ping failed ]\n";
}
foreach ($output as $out) {
    echo $out . "\n";
}
