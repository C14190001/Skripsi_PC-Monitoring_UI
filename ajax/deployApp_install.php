<?php
require '..\config.php';
require 'client_info.php';
require 'pdo_init.php';

$target = $_POST['target'];
$id = $_POST['id'];
$dir = $_POST['dir'];
$app_name = $_POST['app'];

if (getConnection($target) == 0) {
    //https://4sysops.com/archives/using-powershell-to-deploy-software/
    //Copy file to TEMP
    shell_exec('powershell -command "Copy-Item -Path "' . $dir . '" -Destination "\\\\' . $target . '\c$\Windows\Temp" -Force -Recurse" 2>&1');

    //Install
    $install_c = 'msiexec /i "C:\Windows\Temp\\' . $app_name . '" /quiet';
    $install_c2 = "winrs -r:" . $target . " " . $install_c . " 2>&1";
    shell_exec($install_c2);

    ////Remove file from TEMP
    //echo shell_exec('powershell -command "Remove-Item -Path "\\\\'.$target.'\c$\Windows\Temp\\' . $app_name . '" -Force -Recurse" 2>&1');

    //Update app DB
    getApps($target, 0, $id);
    echo "<span style=\"color:green;\">Deployed to Client " . $target . ".</span><br>";
} else {
    echo "<span style=\"color:red;\">Client " . $target . " is disconnected.</span><br>";
}
