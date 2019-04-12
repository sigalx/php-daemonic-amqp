<?php

include_once(__DIR__ . '/../src/Daemons/AmqpListenerDaemon.php');

class ExampleListenerDaemon extends \sigalx\Daemonic\Daemons\AmqpListenerDaemon
{
    protected function _processMessage(AMQPEnvelope $envelope, AMQPQueue $queue): bool
    {
        $data = @json_decode($envelope->getBody(), true);
        if (!$data) {
            return true;
        }
        $this->_talk("Count on {$data['n']}");
        return true;
    }


}