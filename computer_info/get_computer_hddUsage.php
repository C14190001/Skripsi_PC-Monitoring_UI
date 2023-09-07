<?php
$target = $_POST["target"];
//Shell exec 2x, dikurangi, total (pake persen juga)
echo round(str_replace(array("FreeSpace : "),array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List FreeSpace"' . " 2>&1"))/1073741824, 2);
//Check DB jika null lalu insert / update