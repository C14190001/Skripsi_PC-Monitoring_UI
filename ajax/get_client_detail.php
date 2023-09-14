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
    echo '<h1>' . $row['name'] . '</h1>';
    //Status (Selalu diupdate)
    echo "<span id='status'></span>";
    
    //Commands
    echo '
    <br><br>
    <div class="container">
    <div class="row justify-content-center">
            <button class="btn btn-primary col-5 mb-2" id="shutdown_btn" onclick="shutdown_computer(\'' . $row['name'] . '\', \'false\')">Shutdown</button>
            <div class="col-1"></div>
            <button class="btn btn-primary col-5 mb-2" id="restart_btn" onclick="shutdown_computer(\'' . $row['name'] . '\', \'true\')">Restart</button>
    </div>
    <div class="row justify-content-center">
            <button class="btn btn-primary col-5 mb-2"id="ping" onclick="ping_computer(\'' . $row['name'] . '\',\'ping\')">Ping</button>
            <div class="col-1"></div>
            <button class="btn btn-primary col-5 mb-2"id="open_port" onclick="get_open_ports(\'' . $row['name'] . '\',\'open_port\')">Open ports</button>
    </div>
    <div class="row justify-content-center">
            <input id="tracert_input" class="col-7 mb-2" type="text" placeholder="Trace route destination">
            <div class="col-1"></div>
            <button class="btn btn-primary col-3 mb-2" id="tracert_btn" onclick="trace_route(\'' . $row['name'] . '\',\'tracert_input\',\'tracert_btn\')">Trace route</button>
    </div>
    <div class="row justify-content-center">
    <button class="btn btn-primary col-11 mb-2"id="update_btn" onclick="update_client(\'' . $row['name'] . '\',\'' . $id . '\')">Update all info</button>
    </div
    </div>
    <hr>';

    //PC Info
    echo "<button class=\"btn btn-primary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'os', 'os')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    if (is_null($row['os'])) {
        $get_val = explode("\n", str_replace("Caption : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem  -ComputerName ' . $row['name'] . ' | Format-List Caption"' . " 2>&1")));
        $get_val2 = [];
        foreach ($get_val as $a) {
            if (!empty($a)) {
                array_push($get_val2, $a);
            }
        }
        echo 'OS: <span id="os">' . $get_val2[0] . '</span><br>';
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `os` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val2[0], $id]);
    } else {
        echo 'OS: <span id="os">' . $row['os'] . '</span><br>';
    }

    echo "<button class=\"btn btn-primary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'cpu', 'cpu')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    if (is_null($row['cpu'])) {
        $get_val = explode("\n", str_replace("Name : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_Processor -ComputerName ' . $row['name'] . ' | Format-List Name"' . " 2>&1")));
        $get_val2 = [];
        foreach ($get_val as $a) {
            if (!empty($a)) {
                array_push($get_val2, $a);
            }
        }
        echo 'CPU: <span id="cpu">' . $get_val2[0] . '</span><br>';
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `cpu` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val2[0], $id]);
    } else {
        echo 'CPU: <span id="cpu">' . $row['cpu'] . '</span><br>';
    }

    echo "<button class=\"btn btn-primary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'gpu', 'gpu')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    if (is_null($row['i_gpu']) && is_null($row['e_gpu'])) {
        $get_val = explode("\n", str_replace("Name : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_VideoController -ComputerName ' . $row['name'] . ' | Format-List Name"' . " 2>&1")));
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
        echo '<span id="gpu">iGPU: ' . $igpu . '. eGPU: ' . $egpu . '</span><br>';
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `i_gpu` = ? , `e_gpu` = ? WHERE `client_id` = ?");
        $stmt3->execute([$igpu, $egpu, $id]);
    } else {
        echo '<span id="gpu">iGPU: ' . $row['i_gpu'] . '. eGPU: ' . $row['e_gpu'] . '</span><br>';
    }

    echo "<button class=\"btn btn-primary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'ram', 'ram')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    if ($row['ram'] == "0") {
        $get_val = round(str_replace("TotalVisibleMemorySize : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $row['name'] . ' | Format-List TotalVisibleMemorySize"' . " 2>&1")) / 1000000, 2);
        echo 'RAM: <span id="ram">' . $get_val . ' GB</span><br>';
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `ram` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val, $id]);
    } else {
        echo 'RAM: <span id="ram">' . $row['ram'] . ' GB</span><br>';
    }

    echo "<button class=\"btn btn-primary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'hdd', 'mem')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    if ($row['mem'] == "0") {
        $get_val = round(str_replace("Size : ", "", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $row['name'] . ' | Format-List Size"' . " 2>&1")) / 1073741824, 2);
        echo 'Memory: <span id="mem">' . $get_val . ' GB</span><br>';
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `mem` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val, $id]);
    } else {
        echo 'Memory: <span id="mem">' . $row['mem'] . ' GB</span><br>';
    }

    echo "<button class=\"btn btn-primary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'ipMac', 'net')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    echo 'Network: <ul id="net">';
    $is_null = true;
    $stmt2 = $pdo->prepare("SELECT * FROM `clients_network` WHERE `client_id` = " . $id);
    $stmt2->execute();
    foreach ($stmt2 as $row2) {
        echo '<li>' . $row2['ip'] . " - " . $row2['mac'] . '</li>';
        $is_null = false;
    }
    if ($is_null) {
        //IP Address
        $exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $row['name'] . ' | where {$_.MACAddress -ne $null } | Format-List IPAddress"' . " 2>&1");
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
        $exec = shell_exec('powershell -command "Get-CimInstance -ClassName Win32_NetworkAdapterConfiguration -ComputerName ' . $row['name'] . ' | where {$_.MACAddress -ne $null } | Format-List MACAddress"' . " 2>&1");
        $exec = preg_replace('/\s+/', '', $exec); //Hapus Whitespace
        $mac_arr = explode('MACAddress:', $exec); //Ubah jadi Array
        array_splice($mac_arr, 0, 1); //Buang index ke-0 (selalu kosong entah kenapa...)
        //--> $mac_arr skrg adalah Array MAC <--

        //Delete previous data
        $stmt3 = $pdo->prepare("DELETE FROM `clients_network` WHERE `client_id` = ?");
        $stmt3->execute([$id]);

        //Upload new data
        for ($i = 0; $i < count($mac_arr); $i++) {
            if (str_contains($ip_arr[$i], ',')) {
                $temp_ip = explode(',', $ip_arr[$i]);
                for ($j = 0; $j < count($temp_ip); $j++) {
                    $stmt3 = $pdo->prepare("INSERT INTO `clients_network` (`network_id`, `client_id`, `ip`, `mac`) VALUES (NULL, ?, ?, ?)");
                    $stmt3->execute([$id, $temp_ip[$j], $mac_arr[$i]]);
                }
            } else {
                $stmt3 = $pdo->prepare("INSERT INTO `clients_network` (`network_id`, `client_id`, `ip`, `mac`) VALUES (NULL, ?, ?, ?)");
                $stmt3->execute([$id, $ip_arr[$i], $mac_arr[$i]]);
            }
        }

        //Preview Data
        for ($i = 0; $i < count($mac_arr); $i++) {
            if (str_contains($ip_arr[$i], ',')) {
                $temp_ip = explode(',', $ip_arr[$i]);
                for ($j = 0; $j < count($temp_ip); $j++) {
                    echo '<li>' . $temp_ip[$j] . " - " . $mac_arr[$i] . '</li>';
                }
            } else {
                echo '<li>' . $ip_arr[$i] . " - " . $mac_arr[$i] . '</li>';
            }
        }
    }

    echo "</ul>";
    echo "<button class=\"btn btn-primary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'apps', 'app')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    echo 'Apps: <ul id="app">';
    $is_null = true;
    $stmt2 = $pdo->prepare("SELECT * FROM `clients_app` WHERE `client_id` = " . $id);
    $stmt2->execute();
    foreach ($stmt2 as $row2) {
        echo '<li>' . $row2['app'] . '</li>';
        $is_null = false;
    }
    if ($is_null) {
        $Apps = explode("\n", str_replace(array("DisplayName : "), array(""), shell_exec('powershell -command "Invoke-Command -ComputerName "' . $row['name'] . '" -FilePath ..\computer_info\installed_apps.ps1"' . " 2>&1")));
        $StrApps = [];
        foreach ($Apps as $app) {
            if (!empty($app)) {
                array_push($StrApps, $app);
            }
        }

        //Delete previous data
        $stmt3 = $pdo->prepare("DELETE FROM `clients_app` WHERE `client_id` = ?");
        $stmt3->execute([$id]);

        //Insert new data
        foreach ($StrApps as $a) {
            $stmt3 = $pdo->prepare("INSERT INTO `clients_app` (`app_id`, `client_id`, `app`) VALUES (NULL, ?, ?)");
            $stmt3->execute([$id, $a]);
        }

        //Preview data
        foreach ($StrApps as $a) {
            echo '<li>' . $a . '</li>';
        }
    }
    echo "</ul>";
}
