<?php
$target = $_POST["target"];
echo round(str_replace("TotalVisibleMemorySize : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List TotalVisibleMemorySize"' . " 2>&1"))/1000000, 2);
