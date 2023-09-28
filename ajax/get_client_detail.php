<?php
require '..\config.php';
require 'client_info.php';
require 'pdo_init.php';

$id = $_POST['id'];
$stmt = $pdo->prepare("SELECT * FROM `clients` WHERE `client_id` = " . $id);
$stmt->execute();

foreach ($stmt as $row) {
    echo '<button class="btn btn-outline-danger mt-2" style="float: right" onclick="delete_client_modal(\'' . $id . '\', \'' . $row['name'] . '\')"><img src="icons\trash.svg"> Remove</button>';
    echo '<h1>' . $row['name'] . '</h1>';
    //Status (Selalu diupdate)
    //Cek koneksi
    $conn_status = getConnection($row['name'], $id);
    $conn_str = "";
    if ($conn_status == 0) {
        $conn_str = "Connection status: <span style=\"color:green\">Connected</span><br>";
    } else {
        $conn_str = "Connection status: <span style=\"color:red\">Disconnected</span><br>";
    }
    echo "<span>" . $conn_str . "</span>";
    echo "<span id='status'>" . $conn_str . "</span>";

    //Commands
    if ($conn_status == 0) {
        echo '
        <br><br>
        <div class="container">
        <div class="row justify-content-center">
                <button class="btn btn-danger col-5 mb-2" id="shutdown_btn" onclick="shutdown_computer(\'' . $row['name'] . '\', \'false\')">Shutdown</button>
                <div class="col-1"></div>
               <button class="btn btn-warning col-5 mb-2" id="restart_btn" onclick="shutdown_computer(\'' . $row['name'] . '\', \'true\')">Restart</button>
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
            <button class="btn btn-success col-5 mb-2"id="update_btn" onclick="update_client(\'' . $row['name'] . '\',\'' . $id . '\')">Update all info</button>
            <div class="col-1"></div>
            <button class="btn btn-primary col-5 mb-2"id="ctrl_desk" onclick="">Control dekstop</button>
        </div>
        </div>';
        //--> control_desktop Button: ctrl_desk (⬆⬆⬆) <--
    }

    echo "<hr>";

    //PC Info
    //OS
    if ($conn_status == 0) {
        echo "<button class=\"btn btn-outline-secondary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'os', 'os')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    }
    if (is_null($row['os'])) {
        echo 'OS: <span id="os">' . getOs($row['name'], $conn_status, $id) . '</span><br>';
    } else {
        echo 'OS: <span id="os">' . $row['os'] . '</span><br>';
    }

    //CPU
    if ($conn_status == 0) {
        echo "<button class=\"btn btn-outline-secondary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'cpu', 'cpu')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    }
    if (is_null($row['cpu'])) {
        echo 'CPU: <span id="cpu">' . getCpu($row['name'], $conn_status, $id) . '</span><br>';
    } else {
        echo 'CPU: <span id="cpu">' . $row['cpu'] . '</span><br>';
    }

    //GPU
    if ($conn_status == 0) {
        echo "<button class=\"btn btn-outline-secondary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'gpu', 'gpu')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    }
    if (is_null($row['i_gpu']) && is_null($row['e_gpu'])) {
        $gpus = getGpu($row['name'], $conn_status, $id);
        echo '<span id="gpu">iGPU: ' . $gpus[0] . '. eGPU: ' . $gpus[1]  . '</span><br>';
    } else {
        echo '<span id="gpu">iGPU: ' . $row['i_gpu'] . '. eGPU: ' . $row['e_gpu'] . '</span><br>';
    }

    //RAM
    if ($conn_status == 0) {
        echo "<button class=\"btn btn-outline-secondary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'ram', 'ram')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    }
    if ($row['ram'] == "0") {
        echo 'RAM: <span id="ram">' . getRam($row['name'], $conn_status, $id, 0)[0] . ' GB</span><br>';
    } else {
        echo 'RAM: <span id="ram">' . $row['ram'] . ' GB</span><br>';
    }

    //MEM
    if ($conn_status == 0) {
        echo "<button class=\"btn btn-outline-secondary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'mem', 'mem')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    }
    if ($row['mem'] == "0") {
        echo 'Memory: <span id="mem">' . getHdd($row['name'], $conn_status, $id, 0)[0] . ' GB</span><br>';
    } else {
        echo 'Memory: <span id="mem">' . $row['mem'] . ' GB</span><br>';
    }

    //NET
    if ($conn_status == 0) {
        echo "<button class=\"btn btn-outline-secondary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'net', 'net')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    }
    echo 'Network: <ul id="net">';
    $is_null = true;
    $stmt2 = $pdo->prepare("SELECT * FROM `clients_network` WHERE `client_id` = " . $id);
    $stmt2->execute();
    foreach ($stmt2 as $row2) {
        echo '<li>' . $row2['ip'] . " - " . $row2['mac'] . '</li>';
        $is_null = false;
    }
    if ($is_null) {
        $networks = getIpMac($row['name'], $conn_status, $id, 3);
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
    }
    echo "</ul>";

    //APP
    if ($conn_status == 0) {
        echo "<button class=\"btn btn-outline-secondary mr-2 mb-1\" onclick=\"get_computer_info('" . $row['name'] . "','" . $id . "', 'apps', 'app')\"><img src=\"icons\arrow-clockwise.svg\" alt=\"Update\"></button>";
    }
    echo 'Apps: <ul id="app">';
    $is_null = true;
    $stmt2 = $pdo->prepare("SELECT * FROM `clients_app` WHERE `client_id` = " . $id);
    $stmt2->execute();
    foreach ($stmt2 as $row2) {
        echo '<li>' . $row2['app'] . '</li>';
        $is_null = false;
    }
    if ($is_null) {
        $Apps = getApps($row['name'], $conn_status, $id);
        foreach ($Apps as $app) {
            echo '<li>' . $app . '</li>';
        }
    }
    echo "</ul>";
}
