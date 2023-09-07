<?php
$target = $_POST["target"];
echo round(str_replace("Size : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List Size"' . " 2>&1"))/1073741824, 2);
//Check DB jika null lalu insert / update