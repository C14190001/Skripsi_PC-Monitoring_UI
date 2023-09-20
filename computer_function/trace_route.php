<?php
$target = $_POST['target'];
$dest = $_POST['dest'];

if (!empty($dest)) {
    exec('powershell -command "Invoke-Command -ComputerName "' . $target . '" -ScriptBlock { tracert -h 5 -d ' . $dest . ' }" 2>&1', $output, $result);
    foreach ($output as $s) {
        echo $s . "<br>";
    }
} else {
    echo "Please set the destination first.";
}
