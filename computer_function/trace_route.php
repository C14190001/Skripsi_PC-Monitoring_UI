<?php
$target = $_POST['target'];
$dest = $_POST['dest'];

if (!empty($dest)) {

    exec('powershell -command "Invoke-Command -ComputerName "' . $target . '" -ScriptBlock { tracert -d ' . $dest . ' }" 2>&1', $output, $result);

    echo $target . "'s trace route results:\n";

    foreach ($output as $s) {
        echo $s . "\n";
    }
} else {
    echo "Please set the destination first.";
}
