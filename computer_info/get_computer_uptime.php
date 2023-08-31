<?php
$target = $_POST["target"];
$lastbootuptime = str_replace("LastBootUpTime : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List LastBootUpTime"' . " 2>&1"));
$convertToSec1 = strtotime($lastbootuptime); 
$convertToSec2 = strtotime(shell_exec("echo %date% %time%")); 
echo ($convertToSec2-$convertToSec1);