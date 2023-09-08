<?php
//Kode ini tidak dipakai sementara
//(dipakai pada demonstrasi pada file 'withAJAX....')
$target = $_POST["target"];
echo str_replace("Name : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_ComputerSystem -ComputerName ' . $target . ' | Format-List Name"' . " 2>&1"));