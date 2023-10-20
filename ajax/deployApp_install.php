<?php
require '..\config.php';
require 'client_info.php';
require 'pdo_init.php';

$target = $_POST['target'];
$id = $_POST['id'];
$dir = $_POST['dir'];
$app_name = $_POST['app'];
$vnc_app = $_POST['vnc']; //1 True or 0 False

if (getConnection($target) == 0) {
    //https://4sysops.com/archives/using-powershell-to-deploy-software/
    //Copy file to TEMP
    shell_exec('powershell -command "Copy-Item -Path "' . $dir . '" -Destination "\\\\' . $target . '\c$\Windows\Temp" -Force -Recurse" 2>&1');

    //Install
    if ($vnc_app == 1) {
        //TightVNC
        $install_c = 'msiexec /i "C:\Windows\Temp\\' . $app_name . '" /quiet ADDLOCAL="Server" SET_USEVNCAUTHENTICATION=1 VALUE_OF_USEVNCAUTHENTICATION=1 SET_PASSWORD=1 VALUE_OF_PASSWORD=' . $vnc_pass;
    } else {
        //Other apps
        $install_c = 'msiexec /i "C:\Windows\Temp\\' . $app_name . '" /quiet';
    }
    $install_c2 = "winrs -r:" . $target . " \"" . $install_c . "\" 2>&1";
    shell_exec($install_c2);
    
    //Update app DB
    getApps($target, 0, $id);
    echo "<span style=\"color:green;\">Deployed to Client " . $target . ".</span><br>";
} else {
    echo "<span style=\"color:red;\">Client " . $target . " is disconnected.</span><br>";
}
