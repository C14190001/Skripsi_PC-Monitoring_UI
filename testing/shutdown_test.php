<?php
//[ METODE 1 ]
//Network Discovery harus nyala dulu

$host = "192.168.56.104"; //Target
echo "RunAs: " . shell_exec("whoami");
echo "<br>Shutdown / Restart ". $host . ":<br>";
echo shell_exec("shutdown /r /m \\\\" . $host . " -t 0");
//exec("shutdown /r /m \\\\" . $host, $output, $result);
//print_r($output);

//if ($result == 0){
//    echo "Shutdown command success!";
//}
//else{
//    echo "Shutdown command Fail!";
//}
?>


<?php
//[ METODE 2 ]
// $ipaddress = (' \\\\' . "192.168.56.105"); //Target
// $username = (' -u ' . "Administrator"); //Admin username
// $userpw = (' -p ' . "abcd1234."); //Admin password
// $shutdownMins = (' -t ' . "0"); //Time (mins)
//
// $all = (($ipaddress) . ($username) . ($userpw));
// $remoteshutdown = ('c:\windows\power\psshutdown.exe' . ($all) . ' -f -r' . ($shutdownMins));
// echo "$remoteshutdown";
// $a = shell_exec($remoteshutdown);
?>