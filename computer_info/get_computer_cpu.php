<?php
$target = $_POST["target"];
echo str_replace("Name : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_Processor -ComputerName ' . $target . ' | Format-List Name"' . " 2>&1"));
