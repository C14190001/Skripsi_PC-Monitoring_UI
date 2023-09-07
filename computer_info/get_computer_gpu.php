<?php
$target = $_POST["target"];
$gpus = explode("\n",str_replace("Name : ","", shell_exec('powershell -command "Get-CimInstance -ClassName Win32_VideoController -ComputerName ' . $target . ' | Format-List Name"' . " 2>&1")));
$gpus2 = [];
foreach ($gpus as $a){
    if(!empty($a)){
        array_push($gpus2,$a);
    }
}

$igpu = "N/A";
$egpu = "N/A";
if(count($gpus2)>1){
    $egpu = $gpus2[0];
    $igpu = $gpus2[1];
}
else{
    $igpu = $gpus2[0];
}

echo "iGPU: ".$igpu.". eGPU: ".$egpu;

//Check DB jika null lalu insert / update