<?php
$target = $_POST["target"] ?? null;
$id = $_POST["id"] ?? null;
$info = $_POST["info"] ?? null;

//Apps
function getApps($target, $conn = 0, $id = -1)
{
    require 'pdo_init.php';
    if ($conn == 0) {
        $Apps = explode("\n", str_replace(array("DisplayName : "), array(""), shell_exec('powershell -command "Invoke-Command -ComputerName ' . $target . ' -FilePath .\installed_apps.ps1"' . " 2>&1")));
        $StrApps = [];
        foreach ($Apps as $app) {
            if (!empty($app)) {
                array_push($StrApps, $app);
            }
        }

        if ($id != -1) {
            //Delete previous data
            $stmt = $pdo->prepare("DELETE FROM `clients_app` WHERE `client_id` = ?");
            $stmt->execute([$id]);

            //Insert new data
            foreach ($StrApps as $a) {
                $stmt = $pdo->prepare("INSERT INTO `clients_app` (`app_id`, `client_id`, `app`) VALUES (NULL, ?, ?)");
                $stmt->execute([$id, $a]);
            }
        }

        return $StrApps; //Array of Apps
    } else {
        return ["N/A"];
    }
}

//Connection
function getConnection($target, $id = -1)
{
    require 'pdo_init.php';
    exec("ping -n 1 " . $target, $output, $result);
    foreach ($output as $a) {
        if (str_contains($a, "Destination host unreachable") || str_contains($a, "Request timed out")) {
            $result = 1;
            break;
        }
    }

    if ($id != -1) {
        $stmt = $pdo->prepare("UPDATE `clients_status` SET `connection_status` = ? WHERE `client_id` = ?");
        if ($result == 0) {
            $stmt->execute([1, $id]);
        } else {
            $stmt->execute([0, $id]);
        }
    }
    return $result; //Integer: [0 Connected | 1 Disonnected]
}

//CPU
function getCpu($target, $conn = 0, $id = -1)
{
    require 'pdo_init.php';
    if ($conn == 0) {
        $get_val = explode("\n", str_replace("Name : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_Processor -ComputerName ' . $target . ' | Format-List Name"' . " 2>&1")));
        $get_val2 = [];
        foreach ($get_val as $a) {
            if (!empty($a)) {
                array_push($get_val2, $a);
            }
        }

        if ($id != -1) {
            $stmt3 = $pdo->prepare("UPDATE `clients` SET `cpu` = ? WHERE `client_id` = ?");
            $stmt3->execute([$get_val2[0], $id]);
        }
        return $get_val2[0]; //CPU name
    } else {
        return "N/A";
    }
}

//CPU usage
function getCpuUsage($target, $conn = 0, $id = -1)
{
    require 'pdo_init.php';
    if ($conn == 0) {
        $cpuUsages = explode("\n", str_replace(array("PercentProcessorTime : "), array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_PerfFormattedData_PerfOS_Processor  -ComputerName ' . $target . ' | Format-List PercentProcessorTime"' . " 2>&1")));
        $cpuUsages2 = [];
        foreach ($cpuUsages as $core) {
            if (!empty($core)) {
                array_push($cpuUsages2, $core);
            } else {
                array_push($cpuUsages2, 0);
            }
        }

        //Average
        $total = 0;
        $n_core = 0;
        foreach ($cpuUsages2 as $core) {
            $total += $core;
            $n_core++;
        }

        if ($id != -1) {
            $stmt3 = $pdo->prepare("UPDATE `clients_status` SET `cpu_usage` = ? WHERE `client_id` = ?");
            $stmt3->execute([round($total, 2), $id]);
        }
        return round($total, 2); //CPU usage (percentage)
    } else {
        return 0;
    }
}

//GPU
function getGpu($target, $conn = 0, $id = -1)
{
    require 'pdo_init.php';
    if ($conn == 0) {
        $get_val = explode("\n", str_replace("Name : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_VideoController -ComputerName ' . $target . ' | Format-List Name"' . " 2>&1")));
        $get_val2 = [];
        foreach ($get_val as $a) {
            if (!empty($a)) {
                array_push($get_val2, $a);
            }
        }

        $igpu = "N/A";
        $egpu = "N/A";
        if (count($get_val2) > 1) {
            $egpu = $get_val2[0];
            $igpu = $get_val2[1];
        } else {
            $igpu = $get_val2[0];
        }

        if ($id != -1) {
            $stmt3 = $pdo->prepare("UPDATE `clients` SET `i_gpu` = ? , `e_gpu` = ? WHERE `client_id` = ?");
            $stmt3->execute([$igpu, $egpu, $id]);
        }
        $ret_value = [];
        array_push($ret_value, $igpu);
        array_push($ret_value, $egpu);
        return $ret_value; //Array: [iGPU name, eGPU name]
    } else {
        return ["N/A", "N/A"];
    }
}

//HDD
function getHdd($target, $conn = 0, $id = -1, $usage = 0)
{
    require 'pdo_init.php';
    if ($conn == 0) {
        $hdd_cap = round((int)str_replace("Size : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List Size"' . " 2>&1")) / 1073741824, 2);
        $hdd_usage = 0;
        $hdd_usage_per = 0;
        if ($usage == 1) {
            $hdd_free = round((int)str_replace(array("FreeSpace : "), array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $target . ' | Format-List FreeSpace"' . " 2>&1")) / 1073741824, 2);
            $hdd_usage = $hdd_cap - $hdd_free;
            $hdd_usage_per = round(($hdd_usage / $hdd_cap) * 100, 2);
        }

        if ($id != -1) {
            $stmt3 = $pdo->prepare("UPDATE `clients` SET `mem` = ? WHERE `client_id` = ?");
            $stmt3->execute([$hdd_cap, $id]);
            if ($usage == 1) {
                $stmt3 = $pdo->prepare("UPDATE `clients_status` SET `mem_usage` = ? WHERE `client_id` = ?");
                $stmt3->execute([$hdd_usage, $id]);
            }
        }

        $ret_value = [];
        array_push($ret_value, $hdd_cap);
        array_push($ret_value, $hdd_usage);
        array_push($ret_value, $hdd_usage_per);
        return $ret_value; //Array: [HDD Capacity, HDD usage, HDD usage percentage]
    } else {
        return [0, 0, 0];
    }
}

//IP MAC
//$output 1 = Array IP (hati" ip ganda (ada karakter ',')) | 2 = Array MAC | 3 = Array [IP,MAC,IP,MAC,....]
function getIpMac($target, $conn = 0, $id = -1, $output = 0)
{
    require 'pdo_init.php';
    if ($conn == 0) {
        //IP Address
        $exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $target . ' | where {$_.MACAddress -ne $null } | Format-List IPAddress"' . " 2>&1");
        $exec = preg_replace('/\s+/', '', $exec); //Hapus Whitespace
        $exec = str_replace(array('{', '}'), '', $exec); //Hapus karakter '{' dan '}' (hanya untuk IP Address)
        $ip_arr = explode('IPAddress:', $exec); //Ubah jadi Array
        array_splice($ip_arr, 0, 1); //Buang index ke-0 (selalu kosong entah kenapa...)
        //Yang kosong akan diubah ke N/A (Jangan dibuang, karena jumlah IP = jumlah MAC)
        for ($i = 0; $i < count($ip_arr); $i++) {
            if (empty($ip_arr[$i])) {
                $ip_arr[$i] = "N/A";
            }
        }
        //--> $ip_arr skrg adalah Array IP | Info: Perlu cek adanya IP ganda (ada karakter ',') <--

        //MAC Address
        $exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $target . ' | where {$_.MACAddress -ne $null } | Format-List MACAddress"' . " 2>&1");
        $exec = preg_replace('/\s+/', '', $exec);
        $mac_arr = explode('MACAddress:', $exec);
        array_splice($mac_arr, 0, 1);
        //--> $mac_arr skrg adalah Array MAC <--

        if ($id != -1) {
            //Delete previous data
            $stmt = $pdo->prepare("DELETE FROM `clients_network` WHERE `client_id` = ?");
            $stmt->execute([$id]);
            //Upload new data
            for ($i = 0; $i < count($mac_arr); $i++) {
                if (str_contains($ip_arr[$i], ',')) {
                    $temp_ip = explode(',', $ip_arr[$i]);
                    for ($j = 0; $j < count($temp_ip); $j++) {
                        $stmt = $pdo->prepare("INSERT INTO `clients_network` (`network_id`, `client_id`, `ip`, `mac`) VALUES (NULL, ?, ?, ?)");
                        $stmt->execute([$id, $temp_ip[$j], $mac_arr[$i]]);
                    }
                } else {
                    $stmt = $pdo->prepare("INSERT INTO `clients_network` (`network_id`, `client_id`, `ip`, `mac`) VALUES (NULL, ?, ?, ?)");
                    $stmt->execute([$id, $ip_arr[$i], $mac_arr[$i]]);
                }
            }
        }

        switch ($output) {
            case 1:
                return $ip_arr;
                break;
            case 2:
                return $mac_arr;
                break;
            case 3:
                $ipMacArr = [];
                for ($i = 0; $i < count($mac_arr); $i++) {
                    $ipMacArr[] = $ip_arr[$i];
                    $ipMacArr[] = $mac_arr[$i];
                }
                return $ipMacArr;
                break;
            default:
                break;
        }
    } else {
        return ["N/A", "N/A"];
    }
}

//OS
function getOs($target, $conn = 0, $id = -1)
{
    require 'pdo_init.php';
    if ($conn == 0) {
        $get_val = explode("\n", str_replace("Caption : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem  -ComputerName ' . $target . ' | Format-List Caption"' . " 2>&1")));
        $get_val2 = [];
        foreach ($get_val as $a) {
            if (!empty($a)) {
                array_push($get_val2, $a);
            }
        }

        if ($id != -1) {
            $stmt3 = $pdo->prepare("UPDATE `clients` SET `os` = ? WHERE `client_id` = ?");
            $stmt3->execute([$get_val2[0], $id]);
        }
        return $get_val2[0]; //OS name
    } else {
        return "N/A";
    }
}

//RAM
function getRam($target, $conn = 0, $id = -1, $usage = 0)
{
    require 'pdo_init.php';
    if ($conn == 0) {
        $ram_cap = round(str_replace("TotalVisibleMemorySize : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List TotalVisibleMemorySize"' . " 2>&1")) / 1000000, 2);
        if ($usage == 1) {
            $ram_free = round(str_replace(array("FreePhysicalMemory : "), array(""), shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List FreePhysicalMemory"' . " 2>&1")) / 1000000, 2);
        }
        if ($id != -1) {
            $stmt3 = $pdo->prepare("UPDATE `clients` SET `ram` = ? WHERE `client_id` = ?");
            $stmt3->execute([$ram_cap, $id]);
        }

        $ram_usage = 0;
        $ram_usage_per = 0;
        if ($usage == 1) {
            $ram_usage = $ram_cap - $ram_free;
            $ram_usage_per = round(($ram_usage / $ram_cap) * 100, 2);
            if ($id != -1) {
                $stmt3 = $pdo->prepare("UPDATE `clients_status` SET `ram_usage` = ? WHERE `client_id` = ?");
                $stmt3->execute([$ram_usage, $id]);
            }
        }

        $ret_value = [];
        array_push($ret_value, $ram_cap);
        array_push($ret_value, $ram_usage);
        array_push($ret_value, $ram_usage_per);
        return $ret_value; //Array: [RAM Capacity, RAM usage, RAM usage percentage]
    } else {
        return [0, 0, 0];
    }
}

//Uptime
function getUptime($target, $conn = 0, $id = -1, $output = 0)
{
    require 'pdo_init.php';
    if ($conn == 0) {
        $last_bootup_time = str_replace("LastBootUpTime : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $target . ' | Format-List LastBootUpTime"' . " 2>&1"));
        $get_uptime = strtotime($last_bootup_time);
        $last_bootup_time = strtotime($last_bootup_time);
        $last_bootup_time = date("Y-m-d H:i:s", $last_bootup_time);

        if ($id != -1) {
            $stmt3 = $pdo->prepare("UPDATE `clients_status` SET `last_bootup` = ? WHERE `client_id` = ?");
            $stmt3->execute([$last_bootup_time, $id]);
        }

        $get_current_time = strtotime(shell_exec('powershell -command "Invoke-Command -ComputerName ' . $target . ' -ScriptBlock {get-date}"' . " 2>&1"));

        switch ($output) {
            case 1:
                return ($get_current_time - $get_uptime);
                break;
            case 2:
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

                return [$t_d, $t_h, $t_m, $uptime_secs];
                break;
            default:
                break;
        }
    } else {
        return "N/A";
    }
}

$conn = getConnection($target, $id);
switch ($info) {
    case 'os':
        echo getOs($target, $conn, $id);
        break;
    case 'cpu':
        echo getCpu($target, $conn, $id);
        break;
    case 'gpu':
        $gpus = getGpu($target, $conn, $id);
        echo 'iGPU: ' . $gpus[0] . '. eGPU: ' . $gpus[1];
        break;
    case 'ram':
        echo getRam($target, $conn, $id)[0] . " GB";
        break;
    case 'mem':
        echo getHdd($target, $conn, $id)[0] . " GB";
        break;
    case 'net':
        $networks = getIpMac($target, $conn, $id, 3);
        for ($i = 0; $i < (count($networks)); $i++) {
            if (str_contains($networks[$i], ',')) {
                $temp_ip = explode(',', $networks[$i]);
                for ($j = 0; $j < count($temp_ip); $j++) {
                    echo '<li>' . $temp_ip[$j] . " - " . $networks[$i + 1] . '</li>';
                }
            } else {
                echo '<li>' . $networks[$i] . " - " . $networks[$i + 1] . '</li>';
            }
            $i += 1;
        }
        break;
    case 'apps':
        $Apps = getApps($target, $conn, $id);
        foreach ($Apps as $app) {
            echo '<li>' . $app . '</li>';
        }
        break;
    case 'conn':
        $conn = getConnection($target, $id);
        if ($conn == 0) {
            echo "<span style=\"color:green;font-size: 24px;display: flex; justify-content: center;\">⦿</span>";
        } else {
            echo "<span style=\"color:red;font-size: 24px;display: flex; justify-content: center;\">⦿</span>";
        }
        break;
    case "Nothing":
        echo "(Unknown function)";
        break;
}
