<?php
$target = $_POST["target"];
echo str_replace(array("FreePhysicalMemory : "),array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List FreePhysicalMemory"' . " 2>&1"));
