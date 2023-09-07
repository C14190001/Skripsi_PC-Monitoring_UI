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
                if ($restart == "true") {
                    $a = $client + "'s restart results";
                } else {
                    $a = $client + "'s shutdown results";
                }
                show_info_modal($a, msg);
                //alert(msg);
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
                $a = $client + "'s ping results";
                show_info_modal($a, msg);
                //alert(msg);
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
                $a = $client + "'s open ports";
                show_info_modal($a, msg);
                //alert(msg);
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
                if (document.getElementById($dest).value == "") {
                    $b = "NULL";
                } else {
                    $b = document.getElementById($dest).value;
                }
                $a = $client + "'s trace route to " + $b;
                show_info_modal($a, msg);
                //alert(msg);
                document.getElementById($output).innerHTML = "Trace route";
            });
        }

        function refresh_clients_list() {
            //Refresh daftar client dengan F5 (sementara)
            window.location.reload();
        }

        function check_computer_connection($client, $client_id, $output) {
            document.getElementById($output).innerHTML = "";
            $.ajax({
                type: "POST",
                url: "computer_info/get_computer_connection.php",
                data: ({
                    target: $client,
                    id: $client_id,
                }),
            }).done(function(msg) {
                document.getElementById($output).innerHTML = msg;
            });
        }

        function show_info_modal($title, $body) {
            document.getElementById("info_modal_title").innerHTML = $title;
            document.getElementById("info_modal_body").innerHTML = $body;
            $('#info_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#info_modal').modal('show');
        }

        //[Function]
        //get_client_info_all
        //download_csv()
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
                <button class="btn btn-primary" onclick="show_info_modal('Info','LogOut Success.')">Logout</button>
            </div>
        </div>

    </nav>
    <div class="container-fluid">
        <div class="row">
            <div class="col-3" style="height: 90vh; overflow-y: scroll;">
                <div class="container">
                    <div class="row mt-3">
                        <h4 class="col-8">Clients</h4>
                        <button class="btn btn-primary col" onclick="refresh_clients_list()" style="float: right;">Refresh All</button>
                    </div>
                </div>
                <hr>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM `clients`");
                $stmt->execute();
                $i = 0;
                foreach ($stmt as $row) {
                    echo '<button type="button" class="btn btn-light w-100" onclick=\'get_client_detail(' .  $row['client_id'] . ')\'>
                    <div class="container">
                        <div class="row">
                            <div class="col-1"><span id=\'conn_stat' . $i . '\'></span></div>
                            <div class="col" style="text-align: left;"><b>' . $row['name'] . '</b></div>
                        </div>
                        <div class="row">
                            <div class="col" style="vertical-align: top; text-align: left;">';

                    //IP Address
                    echo '<script>check_computer_connection(\'' . $row['name'] . '\',\'' . $row['client_id'] . '\',\'conn_stat' . $i . '\')</script>';
                    $stmt2 = $pdo->prepare("SELECT ip from clients_network WHERE client_id = " . $row['client_id']);
                    $stmt2->execute();
                    $c = 0;
                    foreach ($stmt2 as $row2) {
                        echo $row2['ip'];
                        $c++;
                        echo ', ';
                        if ($c > 1) {
                            echo '...';
                            break;
                        }
                    }

                    echo "</div><div class=\"col-1\"></div></button><br><br>";
                    $i++;
                }
                ?>
            </div>
            <div class="col-9" style="text-align: justify; height: 90vh; overflow-y: scroll;">
                <!--Modal Scan Devices-->
                <div class="modal fade" id="sd_modal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Scan Devices</h4>
                            </div>
                            <div class="modal-body">
                                <p>Options: AD </p>
                                <p>Results:</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary">Scan</button>
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>
                </div>

                <!--Modal Info-->
                <div class="modal fade" id="info_modal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="info_modal_title"></h4>
                            </div>
                            <div class="modal-body" id="info_modal_body"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
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