<!doctype html>
<html lang="en">

<head>
    <title>Monitoring UI | Deploy app</title>
    <script src="../jquery.min.js"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function show_info_modal($title, $body) {
            document.getElementById("info_modal_title").innerHTML = $title;
            document.getElementById("info_modal_body").innerHTML = $body;
            $('#info_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#info_modal').modal('show');
        }

        function deployApp_install($target, $id, $dir, $app) {
            document.getElementById("info_modal_body").innerHTML += "Deploying to Client " + $target + "...<br>";
            $.ajax({
                type: "POST",
                url: "deployApp_install.php",
                data: ({
                    id: $id,
                    target: $target,
                    dir: $dir,
                    app: $app,
                }),
            }).done(function(msg) {
                document.getElementById("info_modal_body").innerHTML += msg;
            });
        }
    </script>
</head>

<body>
    <div class="modal fade" id="info_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="info_modal_title"></h4>
                </div>
                <div class="modal-body" id="info_modal_body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="window.close();" data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

    <?php
    echo "<script>show_info_modal(\"Deploy app\", \"Uploading " . htmlspecialchars(basename($_FILES["installer_file"]["name"])) . "...<br>\")</script>";
    ?>

    <?php
    //https://www.w3schools.com/php/php_file_upload.asp
    $dir = "../deploy_app/" . basename($_FILES["installer_file"]["name"]);
    $fileType = strtolower(pathinfo($dir, PATHINFO_EXTENSION));
    $install_ok = false;

    function addText($text, $nl = true)
    {
        if ($nl == true) {
            echo "<script>document.getElementById(\"info_modal_body\").innerHTML += \"" . $text . "<br>\"</script>";
        } else {
            echo "<script>document.getElementById(\"info_modal_body\").innerHTML += \"" . $text . "\"</script>";
        }
    }

    if (isset($_POST["submit"])) {
        if ($fileType == "msi") {
            if (!file_exists($dir)) {
                if (move_uploaded_file($_FILES["installer_file"]["tmp_name"], $dir)) {
                    addText(htmlspecialchars(basename($_FILES["installer_file"]["name"])) . " has been uploaded.");
                    $install_ok = true;
                } else {
                    addText("Error uploading file!");
                }
            } else {
                addText("File " . htmlspecialchars(basename($_FILES["installer_file"]["name"])) . " already exists.");
                $install_ok = true;
            }
        } else {
            addText("File is not .msi installer (." . $fileType . ")");
        }
    }

    if ($install_ok) {
        require '..\config.php';
        require 'client_info.php';
        require 'pdo_init.php';
        $stmt = $pdo->prepare("SELECT `client_id`,`name` FROM `clients`");
        $stmt->execute();
        addText("<hr>", false);
        foreach ($stmt as $row) {
            $app_name = (string) htmlspecialchars(basename($_FILES["installer_file"]["name"]));
            echo "<script>deployApp_install(\"" . $row['name'] . "\",\"" . $row['client_id'] . "\",\"" . $dir . "\",\"" . $app_name . "\")</script>";
        }
    }
    ?>
</body>