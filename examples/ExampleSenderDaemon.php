<?php

class ExampleSenderDaemon extends \sigalx\Daemonic\Daemons\AbstractDaemon
{
    protected $_n = 1;

    protected function _init(): bool
    {
        parent::_init();
        $this->setSleepSeconds(1);
        return true;
    }

    protected function _work(): bool
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        \sigalx\amqpio\AmqpIo::AmqpIo()->getExchangeDirect()->sendMessage(json_encode(['n' => $this->_n++]), 'example_event');
        return false;
    }

}
