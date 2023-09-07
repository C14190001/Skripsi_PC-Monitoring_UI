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
    //2. jika DB null, maka pake shell_exec() lalu simpan value nya ke DB
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

    //PC Info (Ambil dari DB)
    //TODO: Beri tombol logo Refresh di setiap value (Sebelum value)
    //Refresh: Ambil pake CIM
    echo 'OS: ' . $row['os'] . '<br>';
    echo 'CPU: ' . $row['cpu'] . '<br>';
    echo 'iGPU: ' . $row['i_gpu'] . '<br>';
    echo 'eGPU: ' . $row['e_gpu'] . '<br>';
    echo 'RAM: ' . $row['ram'] . ' GB<br>';
    echo 'Memory: ' . $row['mem'] . ' GB<br>';
    echo 'Network: <ul>';
    $stmt2 = $pdo->prepare("SELECT * FROM `clients_network` WHERE `client_id` = " . $id);
    $stmt2->execute();
    foreach ($stmt2 as $row2) {
        echo '<li>' . $row2['ip'] . " - " . $row2['mac'] . '</li>';
    }
    echo "</ul>";
    echo 'Apps: <ul>';
    $stmt2 = $pdo->prepare("SELECT * FROM `clients_app` WHERE `client_id` = " . $id);
    $stmt2->execute();
    foreach ($stmt2 as $row2) {
        echo '<li>' . $row2['app'] . '</li>';
    }
    echo "</ul>";
}
