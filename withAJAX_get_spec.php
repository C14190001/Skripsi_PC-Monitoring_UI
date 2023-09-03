<html>

<head>
    <title>PC Status Monitoring UI</title>
    <script src="jquery.min.js"></script>
    <script>
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
    </script>
</head>

<body>
    <b>Getting all clients (from AD) info using AJAX</b>
    <button onclick='window.location.reload()'>Refresh All</button>
    <hr>
    <span>
        <?php
        $specs = ["name", "os", "cpu", "gpu", "ram", "hdd", "ipMac", "apps", "uptime", "cpuUsage", "ramUsage", "hddUsage", "connection"];
        $specs2 = ["PC name", "OS name", "CPU name", "GPU name", "RAM capacity", "HDD capacity", "IP & MAC", "Installed apps", "Uptime (Seconds)", "CPU usage", "RAM free space", "HDD free space", "Connection status"];
        $ds = ldap_connect("LDAP://192.168.56.103"); //ADSI
        if ($ds) {
            $r = ldap_bind($ds, "Administrator@myserver.com", "abcd1234."); //Administrator (UNSAFE LOL)
            if ($r == 1) {
                $sr = ldap_search($ds, "OU=Computers,OU=My OU,DC=myserver,DC=com", "(objectClass=Computer)", array("cn", "dn")); //Location
                $info = ldap_get_entries($ds, $sr);
                for ($i = 0; $i < $info["count"]; $i++) {
                    echo "<b>Client #" . $i . " (" . $info[$i]["cn"][0] . "):</b><br><br>";
                    for ($j = 0; $j < count($specs); $j++) {
                        echo '<button onclick="get_computer_info(\'' . $info[$i]["cn"][0] . '\', \'' . $specs[$j] . '\', \'' . $specs[$j] . '' . $i . '\')">Refresh</button>
                            <span>' . $specs2[$j] . ':
                            <span id="' . $specs[$j] . $i . '"></span>
                            <script>get_computer_info("' . $info[$i]["cn"][0] . '", "' . $specs[$j] . '", "' . $specs[$j] . '' . $i . '")</script>
                            </span>
                            <br>';
                    }
                    // echo "
                    //     <script>setInterval(function() {
                    //         let x = document.getElementById('uptime" . $i . "').innerHTML;
                    //         x = parseInt(x) + 1;
                    //         document.getElementById('uptime" . $i . "').innerHTML = x;
                    //     }, 1000)
                    //     </script>
                    //     ";
                    echo '
                    <br><b>Commands:</b>
                    <button onclick="shutdown_computer(\'' . $info[$i]["cn"][0] . '\', \'false\')">Shutdown</button>
                    <button onclick="shutdown_computer(\'' . $info[$i]["cn"][0] . '\', \'true\')">Restart</button>
                    <button id="ping' . $i . '" onclick="ping_computer(\'' . $info[$i]["cn"][0] . '\',\'ping' . $i . '\')">Ping</button>
                    <button id="open_port' . $i . '" onclick="get_open_ports(\'' . $info[$i]["cn"][0] . '\',\'open_port' . $i . '\')">Open ports</button>
                    <input id="tracert_input' . $i . '" type="text" placeholder="Trace route destination">
                    <button id="tracert_btn' . $i . '" onclick="trace_route(\'' . $info[$i]["cn"][0] . '\',\'tracert_input' . $i . '\',\'tracert_btn' . $i . '\')">Trace route</button>
                    ';
                    echo "<hr>";
                }
            } else {
                echo "LDAP bind Failed.";
            }
            ldap_close($ds);
        } else {
            echo "Unable to connect to LDAP.";
        }
        ?>
    </span>
</body>

</html>