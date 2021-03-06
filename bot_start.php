<?php

use Discord\WebSockets\Event;
use React\EventLoop\Factory;

require(__DIR__ . '/vendor/autoload.php');

ini_set('memory_limit', '-1');

$token = getenv('abot_token');
if ($token === false) {
    echo 'No bot token in the environmental variable, attempting to load one from ".env"...' . PHP_EOL;
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();
    $token = getenv('abot_token');
    if ($token === false) {
        echo 'Can`t find a bot token. Shutting down.';
        return;
    }
}
$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
$loop = Factory::create();
$discord = new \Discord\Discord([
    'token' => $token,

    'disabledEvents' => [
        Event::TYPING_START, Event::MESSAGE_REACTION_ADD, Event::MESSAGE_REACTION_REMOVE, Event::CHANNEL_PINS_UPDATE,
        Event::VOICE_STATE_UPDATE, Event::VOICE_SERVER_UPDATE, Event::MESSAGE_REACTION_REMOVE_ALL, Event::GUILD_BAN_ADD,
        Event::GUILD_BAN_REMOVE, Event::GUILD_BAN_ADD,
    ],
    'loop' => $loop,

    'logger' => new \Ancestor\BasicConsoleLogger\BasicConsoleLogger(),

    'loggerLevel' => \Monolog\Logger::NOTICE
]);

$ancestorBot = new \Ancestor\AncestorBot($discord, $config);

$discord->run();
