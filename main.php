<?php
// Make sure you include the composer autoload.

use PAMI\Message\Event\EventMessage;

require __DIR__ . '/vendor/autoload.php';

$basePath = '/asterisk-vr/';
$sox = '/usr/bin/sox';

// Config
$options = array(
    'host' => '127.0.0.1',
    'scheme' => 'tcp://',
    'port' => 5038,
    'username' => 'cti',
    'secret' => 'ctiuser',
    'connect_timeout' => 10,
    'read_timeout' => 10
);

$client = new \PAMI\Client\Impl\ClientImpl($options);
$client->open();

$client->registerEventListener(
    function (\PAMI\Message\Event\HangupEvent $event) use ($basePath, $sox) {
        $uniqId = $event->getUniqueID();

        $fileBase = $basePath . DIRECTORY_SEPARATOR . $uniqId;

        $in = $fileBase . '-in.wav';
        $out = $fileBase . '-out.wav';
        $combine = $fileBase . '.wav';

        if (file_exists($in) && file_exists($out)) {
            $cmd = sprintf('%s -M %s %s %s', $sox, $in, $out, $combine);
            exec($cmd . ' > /dev/null &');

            echo 'Exec: ' . $cmd . "\n";
        } else {
            echo 'FileNotFound' . $fileBase . "\n";
        }
    },
    function (EventMessage $event) {
        return $event instanceof \PAMI\Message\Event\HangupEvent;
    }
);

while (true) {
    $client->process();
    usleep(1000);
}