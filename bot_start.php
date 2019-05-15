<?php
require(__DIR__ . '/vendor/autoload.php');

$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
$loop = \React\EventLoop\Factory::create();

$ancestorBot = new \Ancestor\AncestorBot(
    new \CharlotteDunois\Yasmin\Client([
        'ws.disabledEvents' => ['TYPING_START'],
        'presenceCache' => false,
        'ws.presenceUpdate.ignoreUnknownUsers' => true,
        'messageCache' => false,], $loop),
    $config);

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

$ancestorBot->login($token);
$loop->run();
