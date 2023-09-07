<?php
$target = $_POST["target"];
$Apps = explode("\n",str_replace(array("DisplayName : "),array(""), shell_exec('powershell -command "Invoke-Command -ComputerName ' . $target . ' -FilePath .\installed_apps.ps1"' . " 2>&1")));
$StrApps = [];

foreach($Apps as $app){
    if(!empty($app)){
        array_push($StrApps,$app);
    }
}

print_r($StrApps);
//DB: delete semua app where id ... + lalu upload / insert array nya