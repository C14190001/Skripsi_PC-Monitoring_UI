<?php
$target = $_POST["target"];
//Shell exec 2x, dikurangi, total (echo pake persen juga)
echo round(str_replace(array("FreePhysicalMemory : "),array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List FreePhysicalMemory"' . " 2>&1"))/1000000, 2);
//Check DB jika null lalu insert / update