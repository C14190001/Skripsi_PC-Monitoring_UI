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

    if (isset($_POST["submit"])) {
        if ($fileType == "msi") {
            if (!file_exists($dir)) {
                if (move_uploaded_file($_FILES["installer_file"]["tmp_name"], $dir)) {
                    echo "<script>show_info_modal(\"Deploy app\", \"The file " . htmlspecialchars(basename($_FILES["installer_file"]["name"])) . " has been uploaded. Deploying...\")</script>";
                    $install_ok = true;
                } else {
                    echo "<script>show_info_modal(\"Deploy app\", \"Error uploading file!\")</script>";
                }
            } else {
                echo "<script>show_info_modal(\"Deploy app\", \"File already exists. Deploying it anyway...\")</script>";
                $install_ok = true;
            }
        } else {
            echo "<script>show_info_modal(\"Deploy app\", \"Wrong file type (." . $fileType . ")\")</script>";
        }
    }

    //echo "<script>document.getElementById(\"info_modal_body\").innerHTML += \"" . "Enter Text Here" . "\"</script>";
    //echo $dir; //File location
    if($install_ok){
        //[TODO: Deploy file]
    }
    ?>
</body>