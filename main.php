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
        function refresh_page() {
            window.location.href = 'main.php';
        }

        function scan_devices_ad($button) {
            document.getElementById($button).innerHTML = "Scanning...";
            $.ajax({
                type: "POST",
                url: "ajax/scan_devices_ad.php",
                data: ({}),
            }).done(function(msg) {
                alert(msg);
                document.getElementById($output).innerHTML = "Scan";
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
            <div class="navbar-nav">
                <a class="nav-item nav-link" href="#" data-toggle="modal" data-target="#sd_modal" data-backdrop="static" data-keyboard="false">Scan Devices</a>
            </div>
        </div>

    </nav>
    <div class="container-fluid h-100">
        <div class="row h-100">
            <div class="col-2">
                <h4>Clients</h4>
                <?php
                //Select
                $stmt = $pdo->prepare("SELECT * FROM `clients`");
                $stmt->execute();
                foreach ($stmt as $row) {
                    echo '<a href=\'' . '#' . '\'>' . $row['client_id'] . ': ' . $row['name'] . '</a><br><br>';
                }
                ?>
            </div>
            <div class="col-10" style="text-align: justify;">
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
                <p>Bootstrap Test:</p>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Success!</strong> Bootstrap v4.6 testing.
                </div>

                <p>SQL Test:</p>
                <?php
                //Select
                $stmt = $pdo->prepare("SELECT * FROM `clients`");
                $stmt->execute();
                foreach ($stmt as $row) {
                    echo 'Row ' . $row['client_id'] . ': ' . $row['name'] . ', ' . $row['os'] . ', ' . $row['cpu'] . ', ' . $row['gpu'] . ', ' . $row['ram'] . ', ' . $row['mem'];
                }

                //Update / Insert
                //$stmt = $pdo->prepare("UPDATE `clients` SET `name` = ? WHERE `client_id` = ?");
                //$stmt->execute(['wtf_pc3', 1]);
                ?>
            </div>
        </div>
    </div>
</body>

</html>