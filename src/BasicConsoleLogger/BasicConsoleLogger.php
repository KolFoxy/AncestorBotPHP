<?php

namespace Ancestor\BasicConsoleLogger;

use Psr\Log\LoggerInterface;

class BasicConsoleLogger implements LoggerInterface {

    public function basicOutput($message, array $context = array(), string $level = '') {
        echo PHP_EOL;
        if ($level !== '') {
            echo $level . ': ';
        }
        if (is_object($message)) {
            echo $message->__toString();
        } else {
            echo $message;
        }
        echo PHP_EOL;

        foreach ($context as $item) {
            if (is_object($item)) {
                echo $item->__toString();
                echo PHP_EOL;
            } elseif (is_string($item)) {
                echo $item;
                echo PHP_EOL;
            }
        }
    }

    public function emergency($message, array $context = array()) {
        $this->basicOutput($message, $context, 'EMERGENCY');
    }

    public function alert($message, array $context = array()) {
        $this->basicOutput($message, $context, 'ALERT');
    }

    public function critical($message, array $context = array()) {
        $this->basicOutput($message, $context, 'CRITICAL');
    }

    public function error($message, array $context = array()) {
        $this->basicOutput($message, $context, 'ERROR');
    }

    public function warning($message, array $context = array()) {
        $this->basicOutput($message, $context, 'WARNING');
    }

    public function notice($message, array $context = array()) {
        $this->basicOutput($message, $context, 'NOTICE');
    }

    public function info($message, array $context = array()) {
        //$this->basicOutput($message, $context, 'INFO');
    }

    public function debug($message, array $context = array()) {
        $this->basicOutput($message, $context, 'DEBUG');
    }

    public function log($level, $message, array $context = array()) {
        $this->basicOutput($message, $context, 'LOG');
    }
}