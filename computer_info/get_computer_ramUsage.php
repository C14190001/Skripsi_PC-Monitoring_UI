<?php
$target = $_POST["target"];
echo round(str_replace(array("FreePhysicalMemory : "),array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List FreePhysicalMemory"' . " 2>&1"))/1000000, 2);