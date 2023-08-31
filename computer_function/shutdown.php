<?php
$target = $_POST["target"];
$restart = $_POST["restart"];

if($restart == "true"){
    shell_exec('shutdown -r -m \\\\' . $target . ' -t 0 2>&1');
    echo "Restarting " . $target . ".";
}
else{
    shell_exec('shutdown -s -m \\\\' . $target . ' -t 0 2>&1');
    echo "Shutting down " . $target . ".";
}

