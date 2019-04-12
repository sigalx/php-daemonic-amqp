<?php

return [
    \sigalx\Daemonic\Daemons\GodFatherDaemon::class => [
        'init' => function (\sigalx\Daemonic\Daemons\GodFatherDaemon $daemon): \sigalx\Daemonic\Daemons\GodFatherDaemon {
            return ($daemon
                ->registerChildDaemon(ExampleListenerDaemon::class, 1, __DIR__ . '/ExampleListenerDaemon.php')
                ->registerChildDaemon(ExampleSenderDaemon::class, 1, __DIR__ . '/ExampleSenderDaemon.php')
            );
        },
    ],
    ExampleListenerDaemon::class => [
        'init' => function (ExampleListenerDaemon $daemon): ExampleListenerDaemon {
            $daemon
                ->setQueueName('example_queue')
                ->bindDirect('example_event');
            return $daemon;
        },
    ],

];
