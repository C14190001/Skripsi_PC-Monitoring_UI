<?php
	//$com = 'powershell -command "Get-CimInstance -Class Win32_Processor -ComputerName MyServer-CL1 | Format-List Name"';
	//$com = 'powershell -command "Get-WmiObject -Class Win32_Product -ComputerName Win10-CL2 | Format-List Name"';
	$com = 'powershell -command "Get-CimInstance -ClassName Win32_ComputerSystem -ComputerName MyServer-CL1 | Format-List Name"';

	echo "[ Shell_exec Testing ]<br>Time: [" . shell_exec("echo %date% %time%") . "].";
	echo "<br>Run as: [" . shell_exec('whoami 2>&1') . "].<br>";
	echo "Command: " . $com . " 2>&1<br><br>[ Results ]<br>";
	echo shell_exec($com . " 2>&1");