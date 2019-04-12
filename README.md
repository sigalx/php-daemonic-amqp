# Daemonic PHP #

The daemon for listen and processing AMQP messages in your own PHP-application.

Create your own long-running PHP daemon for listening AMQP queues by extending the AmqpListenerDaemon class. 

#### Requires: ####
* PHP 7.1 (due to using type hints)
* AMQP broker (like RabbitMQ)

#### Example: ####

Just run in CLI:
> ./examples/father.php
