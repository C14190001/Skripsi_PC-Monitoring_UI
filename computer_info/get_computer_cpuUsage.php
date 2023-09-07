<?php
$target = $_POST["target"];
$cpuUsages = explode("\n", str_replace(array("PercentProcessorTime : "), array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_PerfFormattedData_PerfOS_Processor  -ComputerName ' . $target . ' | Format-List PercentProcessorTime"' . " 2>&1")));
$cpuUsages2 = [];
foreach ($cpuUsages as $core) {
    if (!empty($core)) {
        array_push($cpuUsages2, $core);
    }
    else{
        array_push($cpuUsages2, 0);
    }
}
print_r($cpuUsages2);

//Array nya di average persen nya
//lalu dioutput
//Check DB jika null lalu insert / update