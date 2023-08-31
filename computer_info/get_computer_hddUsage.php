<?php
$target = $_POST["target"];
echo str_replace(array("FreeSpace : "),array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List FreeSpace"' . " 2>&1"));
