<?php

require("sonos/sonos.class.php");

/**
 * User: frangarcia
 */
class CliSonosController
{
    /**
     * @var array keyed array of ips
     */
    private $_ips;

    /**
     * CliSonosController constructor.
     * @param array $ips keyed array of ips
     */
    public function __construct($ips)
    {
        $this->_ips = $ips;
    }

    /**
     * Run an action for all the devices
     *
     * @param string $action
     * @param $arguments
     */
    public function run($action, $arguments) {
        foreach ($this->_ips as $which => $ip) {
            $this->runInDevice($ip, $action, $arguments);
        }
    }

    /**
     * Replaces weird non standard characters
     * @param $name
     * @return string
     */
    protected function _cleanName($name) {
        $name = preg_replace('/[^A-Za-z0-9\-]/', '', $name);
        $name = str_replace('Office', '', $name);

        return $name;
    }

    /**
     * Gets the device name of a sonos controller
     * @param SonosPHPController $sonos
     * @return string
     */
    protected function _deviceName(SonosPHPController $sonos) {
        $info = $sonos->device_info();
        $name = $this->_cleanName($info['roomName']);

        return $name;
    }

    /**
     * Perform an action on an specific ip sonos device
     *
     * @param string $ip ip of the speaker
     * @param string $action info|next|prev|mute|vol|volget
     * @param array $arguments extra arguments
     * @return void
     */
    public function runInDevice($ip, $action, $arguments = null) {
        $sonos = new SonosPHPController($ip);
        $name = $this->_deviceName($sonos);
        echo " > " . $name . " : ";

        switch ($action) {
            case 'play':
                echo "Going to 'play' song";
                $sonos->Play();
                break;
            case 'next':
                echo "Going to 'next' song";
                $sonos->Next();
                break;
            case 'prev':
                echo "Going to 'prev' song";
                $sonos->Previous();
                break;
            case 'clear':
                echo "Clearing queue";
                $sonos->RemoveAllTracksFromQueue();
                break;
            case 'mute':
                $currentMute = (bool)$sonos->GetMute();
                echo "Current 'mute' mode: " . (int)$currentMute;

                $toggledMute = !$currentMute;
                $sonos->SetMute((int)$toggledMute);

                $currentMute = (bool)$sonos->GetMute();
                echo "Toggled to 'mute' mode: " . (int)$currentMute;

                break;
            case 'vol':
                $volume = (isset($arguments[2]) and is_numeric($arguments[2])) ? $arguments[2] : $sonos->GetVolume();
                if ($volume > 45) $volume = 45;
                $sonos->SetVolume($volume);
                echo "New volume: " . $sonos->GetVolume();
                break;
            case 'volget':
                echo "Current volume: " . $sonos->GetVolume();
                sleep(1);
                break;
            case 'song':
                $song_info = $sonos->GetPositionInfo();
                //echo "<pre>" . print_r($song_info, true). "</pre>"; exit;
                $info = array(
                    'title' => html_entity_decode($song_info['Title'], ENT_QUOTES | ENT_HTML5),
                    'album' => html_entity_decode($song_info['Album'], ENT_QUOTES | ENT_HTML5),
                    'artist' => html_entity_decode($song_info['TitleArtist'], ENT_QUOTES | ENT_HTML5),
                );
                //echo json_encode($info);
                print_r($info);
                sleep(5);
                break;
            case 'info':
                $info = $sonos->device_info();
                echo "Sonos info: " . $info['friendlyName'];
                break;
        }
        $coordinator = (array)$sonos->get_coordinator();
        echo (($ip == $coordinator['IP']) ? " :: COORDINATOR" : "") . PHP_EOL;
    }
}