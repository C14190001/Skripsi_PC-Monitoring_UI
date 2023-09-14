<?php
require '..\config.php';
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
$id = $_POST['id'];
$stmt = $pdo->prepare("SELECT * FROM `clients` WHERE `client_id` = " . $id);
$stmt->execute();
foreach ($stmt as $row) {
    $target = $row['name'];

    //CONN Status
    exec("ping -n 1 " . $target, $output, $result);
    $stmt = $pdo->prepare("UPDATE `clients_status` SET `connection_status` = ? WHERE `client_id` = ?");

    foreach($output as $a){
        if(str_contains($a,"Destination host unreachable")){
            $result = 1; //Disconnected
            break;
        }
    } 

    if ($result == 0) {
        echo "Connection status: <span style=\"color:green\">Connected</span><br>";
        $stmt->execute([1, $id]);
    } else {
        echo "Connection status: <span style=\"color:red\">Disconnected</span><br>";
        $stmt->execute([0, $id]);
    }

   

    if ($result == 0) {
        //CPU Usage
        $cpuUsages = explode("\n", str_replace(array("PercentProcessorTime : "), array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_PerfFormattedData_PerfOS_Processor  -ComputerName ' . $target . ' | Format-List PercentProcessorTime"' . " 2>&1")));
        $cpuUsages2 = [];
        foreach ($cpuUsages as $core) {
            if (!empty($core)) {
                array_push($cpuUsages2, $core);
            } else {
                array_push($cpuUsages2, 0);
            }
        }
        $total = 0;
        $n_core = 0;
        foreach ($cpuUsages2 as $core) {
            $total += (int)$core;
            $n_core++;
        }
        $total = $total / $n_core;
        $stmt2 = $pdo->prepare("UPDATE `clients_status` SET `cpu_usage` = ? WHERE `client_id` = ?");
        $stmt2->execute([round($total, 2), $id]);
        echo "CPU Usage: " . round($total, 2) . "%<br>";

        //RAM Usage
        //$ram_cap = round(str_replace("TotalVisibleMemorySize : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List TotalVisibleMemorySize"' . " 2>&1")) / 1000000, 2);
        if ($row['ram'] == "0") {
            $get_val = round(str_replace("TotalVisibleMemorySize : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $row['name'] . ' | Format-List TotalVisibleMemorySize"' . " 2>&1")) / 1000000, 2);
            $ram_cap = $get_val;
            $stmt3 = $pdo->prepare("UPDATE `clients` SET `ram` = ? WHERE `client_id` = ?");
            $stmt3->execute([$get_val, $id]);
        } else {
            $ram_cap = $row['ram'];
        }
        $ram_free = round(str_replace(array("FreePhysicalMemory : "), array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List FreePhysicalMemory"' . " 2>&1")) / 1000000, 2);
        $ram_usage = $ram_cap - $ram_free;
        $ram_usage_per = round(($ram_usage / $ram_cap) * 100, 2);
        $stmt3 = $pdo->prepare("UPDATE `clients_status` SET `ram_usage` = ? WHERE `client_id` = ?");
        $stmt3->execute([$ram_usage, $id]);
        echo "RAM Usage: " . $ram_usage . " GB (" . $ram_usage_per . "%)<br>";


        //MEM Usage
        //$hdd_cap = round(str_replace("Size : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List Size"' . " 2>&1")) / 1073741824, 2);
        if ($row['mem'] == "0") {
            $get_val = round(str_replace("Size : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $row['name'] . ' | Format-List Size"' . " 2>&1")) / 1073741824, 2);
            $hdd_cap = $get_val;
            $stmt3 = $pdo->prepare("UPDATE `clients` SET `mem` = ? WHERE `client_id` = ?");
            $stmt3->execute([$get_val, $id]);
        } else {
            $hdd_cap = $row['mem'];
        }
        $hdd_free = round(str_replace(array("FreeSpace : "), array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List FreeSpace"' . " 2>&1")) / 1073741824, 2);
        $hdd_usage = $hdd_cap - $hdd_free;
        $hdd_usage_per = round(($hdd_usage / $hdd_cap) * 100, 2);
        $stmt3 = $pdo->prepare("UPDATE `clients_status` SET `mem_usage` = ? WHERE `client_id` = ?");
        $stmt3->execute([$hdd_usage, $id]);
        echo "Memory Usage: " . $hdd_usage . " GB (" . $hdd_usage_per . "%)<br>";


        //Uptime
        $last_bootup_time = str_replace("LastBootUpTime : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List LastBootUpTime"' . " 2>&1"));
        $get_uptime = strtotime($last_bootup_time);
        $last_bootup_time = strtotime($last_bootup_time);
        $last_bootup_time = date("Y-m-d H:i:s", $last_bootup_time);

        $stmt3 = $pdo->prepare("UPDATE `clients_status` SET `last_bootup` = ? WHERE `client_id` = ?");
        $stmt3->execute([$last_bootup_time, $id]);

        $get_current_time = strtotime(shell_exec('powershell -command "Invoke-Command -ComputerName ' . $target . ' -ScriptBlock {get-date}"' . " 2>&1"));
        //echo "Uptime: " . ($get_current_time - $get_uptime) . " Seconds";
        echo "Uptime: ";
        $uptime_secs = $get_current_time - $get_uptime;
        $t_m = 0;
        $t_h = 0;
        $t_d = 0;
        while ($uptime_secs >= 60) {
            $t_m++;
            $uptime_secs -= 60;
        }
        while ($t_m >= 60) {
            $t_h++;
            $t_m -= 60;
        }
        while ($t_h >= 24) {
            $t_d++;
            $t_h -= 24;
        }
        echo $t_d, " Days ";
        echo $t_h, " Hours ";
        echo $t_m, " Minutes ";
        echo $uptime_secs, " Seconds ";
    }
}
