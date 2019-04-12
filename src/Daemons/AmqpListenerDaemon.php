<?php

namespace sigalx\Daemonic\Daemons;

use sigalx\amqpio\AmqpIo;

include_once(__DIR__ . '/../../vendor/sigalx/php-daemonic/src/Daemons/AbstractDaemon.php');
include_once(__DIR__ . '/../../vendor/sigalx/php-amqpio/src/AmqpIo.php');

abstract class AmqpListenerDaemon extends AbstractDaemon
{
    /** @var string */
    protected $_instanceName;
    /** @var array */
    protected $_amqpCredentials;

    /** @var string */
    protected $_queueName;
    /** @var \stdClass[] */
    protected $_bindings = [];
    /** @var int */
    protected $_queueFlags = AMQP_NOPARAM;
    /** @var int */
    protected $_consumeFlags = AMQP_NOPARAM;
    /** @var AmqpIo */
    protected $_amqpio;
    /** @var \AMQPQueue */
    protected $_queue;

    public function setQueueName(string $queueName): AmqpListenerDaemon
    {
        $this->_queueName = $queueName;
        return $this;
    }

    public function bind(string $exchangeName, string $subroute): AmqpListenerDaemon
    {
        $binding = new \stdClass();
        $binding->exchangeName = $exchangeName;
        $binding->subroute = $subroute;
        $this->_bindings[] = $binding;
        return $this;
    }

    public function bindDirect(string $subroute): AmqpListenerDaemon
    {
        $this->bind('amq.direct', $subroute);
        return $this;
    }

    public function bindTopic(string $subroute): AmqpListenerDaemon
    {
        $this->bind('amq.topic', $subroute);
        return $this;
    }

    public function bindFanout(string $subroute): AmqpListenerDaemon
    {
        $this->bind('amq.fanout', $subroute);
        return $this;
    }

    public function setQueueFlags(int $queueFlags): AmqpListenerDaemon
    {
        $this->_queueFlags = $queueFlags;
        return $this;
    }

    public function setConsumeFlags(string $consumeFlags): AmqpListenerDaemon
    {
        $this->_consumeFlags = $consumeFlags;
        return $this;
    }

    public function setInstanceName(string $instanceName): AmqpListenerDaemon
    {
        $this->_instanceName = $instanceName;
        return $this;
    }

    public function setAmqpCredentials(array $credentials): AmqpListenerDaemon
    {
        $this->_amqpCredentials = $credentials;
        return $this;
    }

    /**
     * @return bool
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPQueueException
     */
    protected function _init(): bool
    {
        if (!parent::_init()) {
            return false;
        }
        if (!$this->_instanceName) {
            $this->_instanceName = AmqpIo::$instanceName;
        }
        if (!$this->_amqpCredentials) {
            $this->_amqpCredentials = AmqpIo::$credentials;
        }
        $this->_amqpio = new AmqpIo($this->_instanceName, $this->_amqpCredentials);
        $queue = $this->_amqpio->initQueue($this->_queueName, $this->_queueFlags);
        foreach ($this->_bindings as $binding) {
            $queue->bind($binding->exchangeName, $binding->subroute);
            $routingKey = $this->_amqpio->makeRouteName($binding->subroute);
            $this->_talk("Queue \"{$this->_queueName}\" now listening from {$binding->exchangeName}/{$routingKey}");
        }
        $this->_queue = $queue->getInternal();
        return true;
    }

    abstract protected function _processMessage(\AMQPEnvelope $envelope, \AMQPQueue $queue): bool;

    /**
     * @return bool
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     */
    protected function _work(): bool
    {
        // non-blocking
        $envelope = $this->_queue->get();
        if (!$envelope) {
            return false;
        }
        $messagePart = $envelope->getBody();
        if (strlen($messagePart)) {
            $messagePart = substr($messagePart, 0, 30) . '...';
        }
        $this->_talk("Got message from {$envelope->getExchangeName()}/{$envelope->getRoutingKey()}: \"{$messagePart}\"");
        if ($this->_processMessage($envelope, $this->_queue)) {
            $this->_queue->ack($envelope->getDeliveryTag());
        }
        return true;
    }
}
