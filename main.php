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
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

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
                get_client_status($id);
            });
        }

        function get_computer_info($client, $id, $info, $output) {
            document.getElementById($output).innerHTML = "...";
            $.ajax({
                type: "POST",
                url: "ajax/client_info.php",
                data: ({
                    target: $client,
                    id: $id,
                    info: $info,
                }),
            }).done(function(msg) {
                document.getElementById($output).innerHTML = msg;
            });
        }

        function shutdown_computer($client, $restart) {
            if ($restart == "true") {
                document.getElementById("restart_btn").innerHTML = "Restarting...";
            } else {
                document.getElementById("shutdown_btn").innerHTML = "Shutting down...";
            }
            $.ajax({
                type: "POST",
                url: "computer_function/shutdown.php",
                data: ({
                    target: $client,
                    restart: $restart,
                }),
            }).done(function(msg) {
                if ($restart == "true") {
                    sleep(2000).then(() => {
                        document.getElementById("restart_btn").innerHTML = "Restart";
                    });
                    //$a = $client + "'s restart results";
                } else {
                    sleep(2000).then(() => {
                        document.getElementById("shutdown_btn").innerHTML = "Shutdown";
                    });
                    //$a = $client + "'s shutdown results";
                }
                //show_info_modal($a, msg);
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
                document.getElementById($output).innerHTML = "Trace route";
            });
        }

        function refresh_clients_list() {
            //Refresh daftar client dengan F5 (sementara)
            window.location.reload();
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

        function get_client_status($client_id, $conn) {
            document.getElementById("status").innerHTML = "Getting status...";
            $.ajax({
                type: "POST",
                url: "ajax/get_client_status.php",
                data: ({
                    id: $client_id,
                    conn: $conn
                }),
            }).done(function(msg) {
                document.getElementById("status").innerHTML = msg;
            });
        }

        function update_client($client_name, $id) {
            document.getElementById("update_btn").innerHTML = "Updating all info...";
            get_computer_info($client_name, $id, 'os', 'os');
            get_computer_info($client_name, $id, 'cpu', 'cpu');
            get_computer_info($client_name, $id, 'gpu', 'gpu');
            get_computer_info($client_name, $id, 'ram', 'ram');
            get_computer_info($client_name, $id, 'mem', 'mem');
            get_computer_info($client_name, $id, 'net', 'net');
            get_computer_info($client_name, $id, 'apps', 'app')
            get_client_status($id);
            sleep(2000).then(() => {
                document.getElementById("update_btn").innerHTML = "Update all info";
            });
        }

        function get_client_list() {
            document.getElementById("client_list").innerHTML = "Loading...";
            $.ajax({
                type: "POST",
                url: "ajax/get_client_list.php",
                data: ({}),
            }).done(function(msg) {
                document.getElementById("client_list").innerHTML = msg;
            });
        }

        // function scan_devices_ad() {
        //     if (document.getElementById("sd_dn_input").value == "") {
        //         alert("Please enter Distinguished Name");
        //     } else {
        //         document.getElementById("sd_results").innerHTML = "Searching for Computers...";
        //         $.ajax({
        //             type: "POST",
        //             url: "ajax/scan_devices_ad.php",
        //             data: ({
        //                 dn_search: document.getElementById("sd_dn_input").value,
        //             }),
        //         }).done(function(msg) {
        //             document.getElementById("sd_results").innerHTML = msg;
        //         });
        //     }
        // }

        function download_csv() {
            if (document.getElementById('radio_download_only').checked) {
                location.href = 'ajax/download_csv.php';
                $('#dcsv_modal').modal('hide');
            } else if (document.getElementById('radio_update_download').checked) {
                update_all_client(0, 1);
            }
        }

        // function update_all_client(is_button, download_csv) {
        //     //is_button = 1, jika berasal dari tombol 'Update all client'.
        //     if (is_button == 1) {
        //         document.getElementById("btn_update_all").innerHTML = "Updating...";
        //     }
        //     //download_csv = 1, jika berasal dari tombol modal Download .csv
        //     if (download_csv == 1) {
        //         document.getElementById("btn_download_csv").innerHTML = "Updating...";
        //     }
        //     $.ajax({
        //         type: "POST",
        //         url: "ajax/update_all_info.php",
        //         data: ({}),
        //     }).done(function(msg) {
        //         if (is_button == 1) {
        //             document.getElementById("btn_update_all").innerHTML = "Update all clients";
        //         }
        //         if (download_csv == 1) {
        //             location.href = 'ajax/download_csv.php';
        //             document.getElementById("btn_download_csv").innerHTML = "Download";
        //             $('#dcsv_modal').modal('hide');
        //         }
        //         //Refresh halaman
        //         refresh_clients_list();
        //     });
        // }

        function delete_client(client_id) {
            document.getElementById("dc_delete_btn").innerHTML = "Removing...";
            $.ajax({
                type: "POST",
                url: "ajax/delete_client.php",
                data: ({
                    id: client_id,
                }),
            }).done(function(msg) {
                refresh_clients_list();
            });
        }

        function delete_client_modal(client_id, client_name) {
            document.getElementById("dc_modal_body").innerHTML = "Are you sure want to remove " + client_name + "?";
            document.getElementById("dc_modal_footer").innerHTML = '<button type="button" class="btn btn-danger" id="dc_delete_btn" onclick="delete_client(\'' + client_id + '\')">Yes</button>';
            document.getElementById("dc_modal_footer").innerHTML += '<button type="button" class="btn btn-success" data-dismiss="modal">No</button>';
            $('#dc_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#dc_modal').modal('show');
        }

        function add_devices_scan() {
            if (document.getElementById("sd_dn_input").value == "") {
                alert("Please enter Distinguished Name");
            } else {
                $.ajax({
                    type: "POST",
                    url: "ajax/add_devices_scan.php",
                    data: ({
                        dn_search: document.getElementById("sd_dn_input").value,
                    }),
                }).done(function(msg) {
                    //Loop add_devices_add
                    let a = msg;
                    const b = a.split(',');
                    document.getElementById("sd_results").innerHTML = '<b>Found ' + b.length + ' clients:</b><br>';

                    for (let i = 0; i < b.length; i++) {
                        add_devices_add(b[i]);
                    }
                });
            }
        }

        function add_devices_add(device_name) {
            document.getElementById("sd_results").innerHTML += 'Adding client ' + device_name + '...<br>';
            $.ajax({
                type: "POST",
                url: "ajax/add_devices_add.php",
                data: ({
                    ad_client: device_name,
                }),
            }).done(function(msg) {
                document.getElementById("sd_results").innerHTML += msg;
            });
        }

        function updateAll_find() {
            document.getElementById("ua_modal_body").innerHTML = "";
            $('#ua_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#ua_modal').modal('show');

            $.ajax({
                type: "POST",
                url: "ajax/updateAll_find.php",
                data: ({}),
            }).done(function(msg) {
                //Loop updateAll_update
                let a = msg;
                const b = a.split(',');
                document.getElementById("ua_modal_body").innerHTML = '<b>Updating all ' + (b.length / 2) + ' clients:</b><br>';

                for (let i = 0; i < (b.length); i += 2) {
                    updateAll_update(b[i], b[i + 1]);
                }
            });
        }

        function updateAll_update(id, name) {
            document.getElementById("ua_modal_body").innerHTML += 'Updating client ' + name + '...<br>';
            $.ajax({
                type: "POST",
                url: "ajax/updateAll_update.php",
                data: ({
                    id: id,
                    name: name,
                }),
            }).done(function(msg) {
                document.getElementById("ua_modal_body").innerHTML += msg;
            });
        }
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
                <!--Left menu button-->
            </div>
            <div class="navbar-nav ml-auto">
                <!--Right menu button-->
                <button class="btn btn-primary mr-2 mt-1" onclick="$('#sd_modal').modal({backdrop: 'static',keyboard: false});$('#sd_modal').modal('show');">Scan devices</button>
                <!-- <button class="btn btn-primary mr-2 mt-1" onclick="update_all_client(1,0)" id="btn_update_all">Update all clients</button> -->
                <button class="btn btn-primary mr-2 mt-1" onclick="updateAll_find()" id="btn_update_all">Update all clients</button>
                <button class="btn btn-primary mr-2 mt-1" onclick="$('#dapp_modal').modal({backdrop: 'static',keyboard: false});$('#dapp_modal').modal('show');">Deploy app</button>
                <!-- <button class="btn btn-success mr-2 mt-1" onclick="$('#dcsv_modal').modal({backdrop: 'static',keyboard: false});$('#dcsv_modal').modal('show');">Download .csv</button> -->
                <button class="btn btn-success mr-2 mt-1" onclick="download_csv();">Download .csv</button>
            </div>
        </div>

    </nav>
    <div class="container-fluid">
        <div class="row">
            <div class="col-3" style="height: 90vh; overflow-y: scroll;">
                <div class="container">
                    <div class="row mt-3">
                        <h4 class="col-8">Clients</h4>
                        <button class="btn btn-outline-primary col" onclick="refresh_clients_list()" style="float: right;">Refresh</button>
                    </div>
                </div>
                <hr>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM `clients`");
                $stmt->execute();
                $i = 0;
                $is_null = true;
                foreach ($stmt as $row) {
                    $is_null = false;
                    echo '<button type="button" class="btn btn-light w-100 p-1" onclick=\'get_client_detail(' .  $row['client_id'] . ')\'>
                    <div class="container">
                    <img src=\'icons\pc-display.svg\' alt=\'PC\' style=\'width:40px; float:right;\'>
                        <div class="row">
                            <div class="col-1" id=\'conn_stat' . $i . '\' style="text-align: center;"></div>
                            <div class="col" style="display: flex; justify-content: center; flex-direction: column; align-items: flex-start;"><b>' . $row['name'] . '</b></div>
                        </div>
                        <div class="row">
                            <div class="col" style="vertical-align: top; text-align: left;">';
                    //IP Address
                    echo '<script>get_computer_info(\'' . $row['name'] . '\',\'' . $row['client_id'] . '\',\'conn\',\'conn_stat' . $i . '\')</script>';
                    $stmt2 = $pdo->prepare("SELECT ip from clients_network WHERE client_id = " . $row['client_id']);
                    $stmt2->execute();
                    $c = 0;
                    foreach ($stmt2 as $row2) {
                        if ($c > 1) {
                            echo '...';
                            break;
                        }
                        echo '• ' . $row2['ip'];
                        echo '<br>';
                        $c++;
                    }

                    echo "</div><div class=\"col-1\"></div></button><br><br>";
                    $i++;
                }
                if ($is_null) {
                    echo "<span style=\"color:red;\">There are no clients in DB.</span>";
                }
                ?>
            </div>
            <div class="col-9" style="text-align: justify; height: 90vh; overflow-y: scroll;">
                <!--(Tidak dipakai) Modal Download .csv-->
                <div class="modal fade" id="dcsv_modal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Download .csv</h4>
                            </div>
                            <div class="modal-body">
                                <div class="container-fluid">
                                    <h6>Options:</h6>
                                    <!-- Radio button: Download only + Update then download !-->
                                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                        <label class="btn btn-outline-secondary active">
                                            <input type="radio" name="dl_csv_op" id="radio_download_only" checked> Download only
                                        </label>
                                        <label class="btn btn-outline-secondary">
                                            <input type="radio" name="dl_csv_op" id="radio_update_download"> Update + Download
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" id="btn_download_csv" onclick="download_csv()">Download</button>
                                <!-- Onclick: Matikan radio button -->
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>
                </div>

                <!--Modal Scan Devices-->
                <div class="modal fade" id="sd_modal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Scan devices</h4>
                            </div>
                            <div class="modal-body" id="sd_modal_body">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-9">
                                            <input id="sd_dn_input" class="w-100 h-100" type="text" placeholder="Distinguished Name">
                                        </div>
                                        <div class="col-3">
                                            <!-- <button class="btn btn-primary w-100 h-100" id="sd_dn_search" onclick="scan_devices_ad()">Search</button> -->
                                            <button class="btn btn-primary w-100 h-100" id="sd_dn_search" onclick="add_devices_scan()">Search</button>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <span id="sd_results"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer" id="sd_modal_footer">
                                <!-- <button type="button" class="btn btn-danger" onclick="document.getElementById('sd_dn_input').value=''; document.getElementById('sd_results').innerHTML='';" data-dismiss="modal">Close</button> -->
                                <button type="button" class="btn btn-danger" onclick="document.getElementById('sd_dn_input').value=''; document.getElementById('sd_results').innerHTML=''; refresh_clients_list()" data-dismiss="modal">Close</button>
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
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>
                </div>

                <!--Modal Update All-->
                <div class="modal fade" id="ua_modal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="ua_modal_title">Update all clients</h4>
                            </div>
                            <div class="modal-body" id="ua_modal_body"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>
                </div>

                <!--Modal Delete client-->
                <div class="modal fade" id="dc_modal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="dc_modal_title">Remove client</h4>
                            </div>
                            <div class="modal-body" id="dc_modal_body"></div>
                            <div class="modal-footer" id="dc_modal_footer"></div>
                        </div>
                    </div>
                </div>

                <!--Modal Deploy App-->
                <div class="modal fade" id="dapp_modal">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="dapp_modal_title">Deploy app</h4>
                            </div>
                            <div class="modal-body" id="dapp_modal_body">
                                <form form target="_blank" action="ajax/deployApp_upload.php" method="post" enctype="multipart/form-data">
                                    <p>Select installer (.msi) to deploy (Max. 100 MB):<br><b>Important: </b>May take a very long time.</p>
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-9">
                                                <input type="file" class="w-100 h-100" id="dapp_file_upload" name="installer_file" required>
                                            </div>
                                            <div class="col-3">
                                                <button class="btn btn-success w-100 h-100" id="dapp_btn_upload" name="submit">Deploy</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" onclick="document.getElementById('dapp_file_upload').value='';$('#dapp_modal').modal('hide');" data-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>
                </div>
                <div id='client_detail'>Select client on the left for details.</div>
            </div>
        </div>
    </div>
</body>

</html>