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
    //1. Load data dari DB [OK]
    //2. jika DB null, maka pake shell_exec() lalu simpan value nya ke DB [...]
    //3. Echo (Tombol Refresh) (spesifikasi): (Value)

    //Status (Selalu diupdate)
    echo '<h1>' . $row['name'] . '</h1>';
    $stmt2 = $pdo->prepare("SELECT * FROM `clients_status` WHERE `client_id` = " . $id);
    $stmt2->execute();
    $is_null = true;
    foreach ($stmt2 as $row2) {
        echo 'Connection status: ' . $row2['connection_status'] . ". ";
        echo 'CPU Usage: ' . $row2['cpu_usage'] . '%. ';
        echo 'RAM Usage: ' . $row2['ram_usage'] . 'GB. ';
        echo 'Memory Usage: ' . $row2['mem_usage'] . 'GB.';
        $is_null = false;
    }

    echo ">>[is_null: " . $is_null . "]<<"; //DEBUG
    if ($is_null) {
        //(Ambil kode nya get_computer_***usage.php)
        //Get CPU usage
        //Get RAM usage
        //Get HDD usage
        //INSERT status
    }

    //Commands
    echo '
    <br><br>
    <div class="container">
    <div class="row justify-content-center">
            <button class="btn btn-primary col-5 mb-2" onclick="shutdown_computer(\'' . $row['name'] . '\', \'false\')">Shutdown</button>
            <div class="col-1"></div>
            <button class="btn btn-primary col-5 mb-2" onclick="shutdown_computer(\'' . $row['name'] . '\', \'true\')">Restart</button>
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
    <button class="btn btn-primary col-11 mb-2"id="refresh_btn" onclick="refresh_client(\'' . $row['name'] . '\',\'' . $id . '\')">Refresh all info</button>
    </div
    </div>
    <hr>';

    //PC Info
    echo "<button class=\"btn btn-primary mr-2 mb-1\" onclick=\"get_computer_info('".$row['name']."', 'os', 'os')\"><img src=\"arrow-clockwise.svg\" alt=\"Refresh\"></button>";
    if(is_null($row['os'])){
        $get_val = str_replace("Caption : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem  -ComputerName ' . $row['name'] . ' | Format-List Caption"' . " 2>&1"));
        echo 'OS: <span id="os">' . $get_val . '</span><br>';
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `os` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val,$id]);
    }
    else{
        echo 'OS: <span id="os">' . $row['os'] . '</span><br>';
    }

    echo "<button class=\"btn btn-primary mr-2 mb-1\" onclick=\"get_computer_info('".$row['name']."', 'cpu', 'cpu')\"><img src=\"arrow-clockwise.svg\" alt=\"Refresh\"></button>";
    if(is_null($row['cpu'])){
        $get_val = str_replace("Name : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_Processor -ComputerName ' . $row['name'] . ' | Format-List Name"' . " 2>&1"));
        echo 'CPU: <span id="cpu">' . $get_val . '</span><br>';
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `cpu` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val,$id]);
    }
    else{
        echo 'CPU: <span id="cpu">' . $row['cpu'] . '</span><br>';
    }

    echo '<div class="container"><div class="row"><div class="col-1">';
    //echo "<button class=\"btn btn-primary\" onclick=\"get_computer_info('".$row['name']."', 'gpu', 'gpu')\"><img src=\"arrow-clockwise.svg\" alt=\"Refresh\"></button>";
    echo '</div><div class="col">';
    if(is_null($row['i_gpu'])&&is_null($row['e_gpu'])){
        //Kode ambil GPU (INFO: Check file get_computer_gpu.php)
        //Cek jika GPU nya ada 2. jika ada, dipisah
        //echo '<span id="gpu">iGPU: ' . $row['i_gpu'] . '<br>eGPU: '. $row['e_gpu'].'</span><br>';
    }
    else{
        echo '<span id="gpu">iGPU: ' . $row['i_gpu'] . '<br>eGPU: '. $row['e_gpu'].'</span><br>';
    }
    echo '</div></div></div>';

    echo "<button class=\"btn btn-primary mr-2 mb-1\" onclick=\"get_computer_info('".$row['name']."', 'ram', 'ram')\"><img src=\"arrow-clockwise.svg\" alt=\"Refresh\"></button>";
    if($row['ram'] == "0"){
        $get_val = round(str_replace("TotalVisibleMemorySize : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_OperatingSystem -ComputerName ' . $row['name'] . ' | Format-List TotalVisibleMemorySize"' . " 2>&1"))/1000000, 2);
        echo 'RAM: <span id="ram">' . $get_val . ' GB</span><br>';
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `ram` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val,$id]);
    }
    else{
        echo 'RAM: <span id="ram">' . $row['ram'] . ' GB</span><br>';
    }

    echo "<button class=\"btn btn-primary mr-2 mb-1\" onclick=\"get_computer_info('".$row['name']."', 'hdd', 'mem')\"><img src=\"arrow-clockwise.svg\" alt=\"Refresh\"></button>";
    if($row['mem'] == "0"){
        $get_val = round(str_replace("Size : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_LogicalDisk  -ComputerName ' . $row['name'] . ' | Format-List Size"' . " 2>&1"))/1073741824, 2);
        echo 'Memory: <span id="mem">' . $get_val . ' GB</span><br>';
        $stmt3 = $pdo->prepare("UPDATE `clients` SET `mem` = ? WHERE `client_id` = ?");
        $stmt3->execute([$get_val,$id]);
    }
    else{
        echo 'Memory: <span id="mem">' . $row['mem'] . ' GB</span><br>';
    }

    //Button Refresh + Cek NULL / Upload DB
    echo 'Network: <ul>';
    $is_null = true;
    $stmt2 = $pdo->prepare("SELECT * FROM `clients_network` WHERE `client_id` = " . $id);
    $stmt2->execute();
    foreach ($stmt2 as $row2) {
        echo '<li>' . $row2['ip'] . " - " . $row2['mac'] . '</li>';
        $is_null =false;
    }
    if($is_null){

    }

    echo "</ul>";
    //Button Refresh + Cek NULL / Upload DB
    echo 'Apps: <ul>';
    $is_null = true;
    $stmt2 = $pdo->prepare("SELECT * FROM `clients_app` WHERE `client_id` = " . $id);
    $stmt2->execute();
    foreach ($stmt2 as $row2) {
        echo '<li>' . $row2['app'] . '</li>';
        $is_null =false;
    }
    if($is_null){

    }
    echo "</ul>";
}
