<?php
$target = $_POST["target"];
exec("ping -n 4 " . $target, $output, $result);
echo "[ " . $target . "'s ping results ]\n";
foreach ($output as $out) {
    echo $out . "\n";
}
