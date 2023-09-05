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
$i = 0;
foreach ($stmt as $row) {
    //1. Load data dari DB
    //2. jika DB null, maka pake shell_exec() lalu simpan value nya ke DB
    //3. Echo (Tombol Refresh) (spesifikasi): (Value)

    //Status
    echo '<h1>' . $row['name'] . '</h1>';
    $stmt2 = $pdo->prepare("SELECT * FROM `clients_status` WHERE `client_id` = " . $id);
    $stmt2->execute();
    foreach ($stmt2 as $row2) {
        echo 'Connection status: ' . $row2['connection_status'] . ". ";
        echo 'CPU Usage: ' . $row2['cpu_usage'] . '%. ';
        echo 'RAM Usage: ' . $row2['ram_usage'] . 'GB. ';
        echo 'Memory Usage: ' . $row2['mem_usage'] . 'GB.';
    }

    //Commands
    echo '
    <br><br>
    <button class="btn btn-primary" onclick="shutdown_computer(\'' . $row['name'] . '\', \'false\')">Shutdown</button>
    <button class="btn btn-primary"onclick="shutdown_computer(\'' . $row['name'] . '\', \'true\')">Restart</button>
    <button class="btn btn-primary"id="ping' . $i . '" onclick="ping_computer(\'' . $row['name'] . '\',\'ping' . $i . '\')">Ping</button>
    <button class="btn btn-primary"id="open_port' . $i . '" onclick="get_open_ports(\'' . $row['name'] . '\',\'open_port' . $i . '\')">Open ports</button>
    <input id="tracert_input' . $i . '" type="text" placeholder="Trace route destination">
    <button class="btn btn-primary" id="tracert_btn' . $i . '" onclick="trace_route(\'' . $row['name'] . '\',\'tracert_input' . $i . '\',\'tracert_btn' . $i . '\')">Trace route</button>
    <hr>';

    //PC Info
    //Beri tombol logo Refresh di setiap value
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
    $i++;
}
