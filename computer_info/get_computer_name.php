<?php
$target = $_POST["target"];
echo str_replace("Name : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_ComputerSystem -ComputerName ' . $target . ' | Format-List Name"' . " 2>&1"));
//REVISI: Kode akan Ambil dari DB (Karena di DB, name tidak boleh NULL)