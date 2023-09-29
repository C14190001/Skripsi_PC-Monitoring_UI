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
    //https://www.w3schools.com/php/php_file_upload.asp
    $dir = "../deploy_app/" . basename($_FILES["installer_file"]["name"]);
    $fileType = strtolower(pathinfo($dir, PATHINFO_EXTENSION));
    $install_ok = false;

    function addText($text)
    {
        echo "<script>document.getElementById(\"info_modal_body\").innerHTML += \"" . $text . "<br>\"</script>";
    }

    if (isset($_POST["submit"])) {
        if ($fileType == "msi") {
            if (!file_exists($dir)) {
                if (move_uploaded_file($_FILES["installer_file"]["tmp_name"], $dir)) {
                    echo "<script>show_info_modal(\"Deploy app\", \"" . htmlspecialchars(basename($_FILES["installer_file"]["name"])) . " has been uploaded.<br>\")</script>";
                    $install_ok = true;
                } else {
                    echo "<script>show_info_modal(\"Deploy app\", \"Error uploading file!<br>\")</script>";
                }
            } else {
                echo "<script>show_info_modal(\"Deploy app\", \"File " . htmlspecialchars(basename($_FILES["installer_file"]["name"])) . " already exists.<br>\")</script>";
                $install_ok = true;
            }
        } else {
            echo "<script>show_info_modal(\"Deploy app\", \"File is not .msi installer (." . $fileType . ")<br>\")</script>";
        }
    }

    if ($install_ok) {
        require '..\config.php';
        require 'client_info.php';
        require 'pdo_init.php';
        $stmt = $pdo->prepare("SELECT `client_id`,`name` FROM `clients`");
        $stmt->execute();
        foreach ($stmt as $row) {
            //https://4sysops.com/archives/using-powershell-to-deploy-software/
            //Copy file to TEMP
            echo shell_exec('powershell -command "Copy-Item -Path "' . $dir . '" -Destination "\\\\' . $row['name'] . '\c$\Windows\Temp" -Force -Recurse" 2>&1');
            //Install (Not Working! idk why...)
            $install_command = 'powershell -command "Invoke-Command -ComputerName "' . $row['name'] . '" -ScriptBlock { msiexec /i "\c$\Windows\Temp\\' . htmlspecialchars(basename($_FILES["installer_file"]["name"])) . ' /qn"}" 2>&1';
            echo shell_exec($install_command);
            ////Remove file from TEMP
            //echo shell_exec('powershell -command "Remove-Item -Path "\\\\'.$row['name'].'\c$\Windows\Temp\\' . htmlspecialchars(basename($_FILES["installer_file"]["name"])) . '" -Force -Recurse" 2>&1');
            ////Update app DB
            //getApps($row['name'],0,$row['client_id']);
        }
        addText("<br>Deploying done.");
    }
    ?>
</body>