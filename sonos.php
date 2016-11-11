<?php

require("cli.sonos.class.php");

$action = isset($argv[1]) ? $argv[1] : false;
if (!$action) {
    echo "Usage: php sonos.php [song|info|next|prev|mute|vol INTEGER]|volget|clear] [front|back]" . PHP_EOL;
}
else {
    $ips = array(
        'front' => '192.168.102.63',
        'back' => '192.168.102.79',     //coordinator
    );
    $global_actions = array('prev', 'next', 'clear', 'song', 'play');
    $delayed_actions = array('vol', 'next', 'mute', 'clear', 'prev');

    $global = false;
    $delayed = false;
    $filtered_ips = array();
    foreach ($argv as $a) {
        if (in_array($a, array_keys($ips))) {
            $filtered_ips[$a] = $ips[$a];
        }
        elseif (in_array($a, $global_actions)) {
            $global = true;
        }

        if (in_array($a, $delayed_actions)) {
            $delayed = true;
        }
    }
    if (!empty($filtered_ips)) {
        $ips = $filtered_ips;
    }
    if ($global) {
        //get coordinator
        $ips = array_slice($ips, 1, 1, true);
    }
    //print_r($ips);exit;

    if ($delayed) {
        sleep(rand(1, 4));
    }

    $sonosController = new CliSonosController($ips);
    $sonosController->run($action, $argv);
}

