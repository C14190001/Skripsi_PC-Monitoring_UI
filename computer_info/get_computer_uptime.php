<?php
$target = $_POST["target"];
$lastbootuptime = str_replace("LastBootUpTime : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List LastBootUpTime"' . " 2>&1"));
//echo $lastbootuptime;
$convertToSec1 = strtotime($lastbootuptime); 
$convertToSec2 = strtotime(shell_exec('powershell -command "Invoke-Command -ComputerName ' . $target . ' -ScriptBlock {get-date}"' . " 2>&1")); 
echo ($convertToSec2-$convertToSec1);