<?php
$target = $_POST["target"];
exec("ping -n 1 " . $target, $output, $result);
if ($result == 0) {
    echo "Connected";
} else {
    echo "Disconnected";
}