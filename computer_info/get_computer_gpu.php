<?php
$target = $_POST["target"];
echo str_replace("Name : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_VideoController -ComputerName ' . $target . ' | Format-List Name"' . " 2>&1"));
//Hati hati ada 2 GPU nanti ...
//TIP: Coba di array
//Check DB jika null lalu insert / update