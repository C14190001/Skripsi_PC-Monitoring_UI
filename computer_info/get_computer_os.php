<?php
$target = $_POST["target"];
echo str_replace("Caption : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem  -ComputerName ' . $target . ' | Format-List Caption"' . " 2>&1"));
//Check DB jika null lalu insert / update