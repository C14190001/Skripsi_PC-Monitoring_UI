<?php
echo "[ Get PC specs using CIM + PS scripts ] [Time: " . shell_exec("echo %date% %time%") . "]";
echo "<br>Run as: [" . shell_exec('whoami 2>&1') . "]<br>";

$target = "MyServer-CL1";
echo "Target PC: [" . $target . "]<br><br>";

echo "[Computer Name] " . str_replace("Name : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_ComputerSystem -ComputerName ' . $target . ' | Format-List Name"' . " 2>&1"));
echo "<br><br>";

echo "[OS Name] " . str_replace("Caption : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem  -ComputerName ' . $target . ' | Format-List Caption"' . " 2>&1"));
echo "<br><br>";

echo "[CPU Name] " . str_replace("Name : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_Processor -ComputerName ' . $target . ' | Format-List Name"' . " 2>&1"));
echo "<br><br>";

echo "[GPU Name] " . str_replace("Name : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_VideoController -ComputerName ' . $target . ' | Format-List Name"' . " 2>&1"));
echo "<br><br>";

echo "[RAM Capacity] " . round(str_replace("TotalVisibleMemorySize : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List TotalVisibleMemorySize"' . " 2>&1"))/1000000, 2) . " GB";
echo "<br><br>";

echo "[HDD Capacity] " . round(str_replace("Size : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List Size"' . " 2>&1"))/1000000000, 2) . " GB";
echo "<br><br>";

echo "[IP & MAC Address] " . str_replace(array("IPAddress","MACAddress", " : "),array("IP=","MAC="), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $target . ' | where {$_.MACAddress -ne $null } | Format-List IPAddress, MACAddress"' . " 2>&1"));
echo "<br><br>";

$Apps = explode("\n",str_replace(array("DisplayName : "),array(""), shell_exec('powershell -command "Invoke-Command -ComputerName ' . $target . ' -FilePath .\get_installed_apps.ps1"' . " 2>&1")));
$StrApps = "";
foreach($Apps as $App){
    if(!empty($App)){
        $StrApps = $StrApps . $App . ", ";
    }
}
echo "[Installed Apps] " . $StrApps . "<br><br>";

$lastbootuptime = str_replace("LastBootUpTime : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List LastBootUpTime"' . " 2>&1"));
$convertToSec1 = strtotime($lastbootuptime); 
$convertToSec2 = strtotime(shell_exec("echo %date% %time%")); 
echo "[ Uptime ] " . ($convertToSec2-$convertToSec1) . " Seconds <br><br>";

echo "[CPU usage (?)] " . shell_exec('powershell -command "Get-CimInstance -ClassName Win32_PerfFormattedData_PerfOS_Processor  -ComputerName ' . $target . ' | Format-List PercentProcessorTime"' . " 2>&1");
echo "<br><br>";

echo "[RAM Free / Not used] " . shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List FreePhysicalMemory"' . " 2>&1");
echo "<br><br>";

echo "[HDD Free / Not used] " . shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List FreeSpace"' . " 2>&1");
echo "<br><br>";

exec("ping -n 1 " . $target, $output, $result);
echo "[Connection status] ";
if ($result == 0) {
    echo "Connected (Ping OK).";
} else {
    echo "Disconnected or Ping failed.";
}
echo "<br><br>";

//Sumber: 
//https://www.codeproject.com/Questions/844580/How-to-get-Memory-usage-from-WMI
//http://www.blackwasp.co.uk/GetMemory.aspx