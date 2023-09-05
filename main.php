<?php
require 'config.php';
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
?>
<!doctype html>
<html lang="en">

<head>
    <title>Monitoring UI</title>
    <script src="jquery.min.js"></script>
    <!--Bootstrap 4.6-->
    <!-- https://www.w3schools.com/bootstrap4/default.asp -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <!----------------->
    <script>
        function get_client_detail($id) {
            document.getElementById('client_detail').innerHTML = "Loading...";
            $.ajax({
                type: "POST",
                url: "ajax/get_client_detail.php",
                data: ({
                    id: $id,
                }),
            }).done(function(msg) {
                document.getElementById('client_detail').innerHTML = msg;
            });
        }

        function get_computer_info($client, $info, $output) {
            document.getElementById($output).innerHTML = "...";
            $.ajax({
                type: "POST",
                url: "computer_info/get_computer_" + $info + ".php",
                data: ({
                    target: $client,
                }),
            }).done(function(msg) {
                document.getElementById($output).innerHTML = msg;
            });
        }

        function shutdown_computer($client, $restart) {
            $.ajax({
                type: "POST",
                url: "computer_function/shutdown.php",
                data: ({
                    target: $client,
                    restart: $restart,
                }),
            }).done(function(msg) {
                alert(msg);
            });
        }

        function ping_computer($client, $output) {
            document.getElementById($output).innerHTML = "Pinging...";
            $.ajax({
                type: "POST",
                url: "computer_function/ping.php",
                data: ({
                    target: $client,
                }),
            }).done(function(msg) {
                alert(msg);
                document.getElementById($output).innerHTML = "Ping";
            });
        }

        function get_open_ports($client, $output) {
            document.getElementById($output).innerHTML = "Getting open ports...";
            $.ajax({
                type: "POST",
                url: "computer_function/open_ports.php",
                data: ({
                    target: $client,
                }),
            }).done(function(msg) {
                alert(msg);
                document.getElementById($output).innerHTML = "Open ports";
            });
        }

        function trace_route($client, $dest, $output) {
            document.getElementById($output).innerHTML = "Tracing route...";
            $.ajax({
                type: "POST",
                url: "computer_function/trace_route.php",
                data: ({
                    target: $client,
                    dest: document.getElementById($dest).value,
                }),
            }).done(function(msg) {
                alert(msg);
                document.getElementById($output).innerHTML = "Trace route";
            });
        }
        //[Function]
        //scan_devices_ip: 
        //Dialog / modal buat input value range IP, Scan, 
        //bandingkan dengan DB Client, Tambah Devices, get details.

        //[Function]
        //get_client_info_all(client,spanId) = edit <span>
        //get_client_info(client,info,spanId) = edit <span>
        //client_function(client,func) = Alert msg

        //[Function]
        //download_csv(jumlah_client): 
        //pake loop 0 ke jumlah_client, ambil document.getElementById(spanId).innerHTML.
        //masukkan ke array, buat csv.
    </script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="main.php">Monitoring UI</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#topnav_menu" aria-controls="topnav_menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="topnav_menu">
            <div class="navbar-nav mr-auto">
                <a class="nav-item nav-link" href="#" data-toggle="modal" data-target="#sd_modal" data-backdrop="static" data-keyboard="false">Scan Devices</a>
                <a class="nav-item nav-link" href="#">Download .CSV</a>
            </div>
            <div class="navbar-nav ml-auto">
                <button class="btn btn-primary">Logout</button>
            </div>
        </div>

    </nav>
    <div class="container-fluid">
        <div class="row">
            <div class="col-3" style="height: 90vh; overflow-y: scroll;">
                <h4>Clients</h4>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM `clients`");
                $stmt->execute();
                foreach ($stmt as $row) {
                    echo '<button type="button" class="btn btn-light w-100 text-left" onclick=\'get_client_detail(' .  $row['client_id'] . ')\'>' . $row['name'] . '<span class=\'float-right\' style=\'color:red\'>(Connection status script)</script></span>';
                    $stmt2 = $pdo->prepare("SELECT ip from clients_network WHERE client_id = " . $row['client_id']);
                    $stmt2->execute();
                    $c = 0;
                    echo "<ul>";
                    foreach ($stmt2 as $row2) {
                        echo '<li>' .$row2['ip'];
                        $c++;
                        if ($c > 1) {
                            echo '<li>...</li>';
                            break;
                        }
                    }
                    echo "</ul></button><br><br>";
                }
                ?>
            </div>
            <div class="col-9" style="text-align: justify; height: 90vh; overflow-y: scroll;">

                <div class="modal fade" id="sd_modal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Scan Devices</h4>
                            </div>
                            <div class="modal-body">
                                <p>Options: AD / IP</p>
                                <p>IP Range</p>
                                <p>Results:</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary">Scan</button>
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>
                </div>

                <div id='client_detail'></div>
            </div>
        </div>
    </div>
</body>

</html>