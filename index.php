<?php
require(__DIR__ . '/vendor/autoload.php');
$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
$loop = \React\EventLoop\Factory::create();
$client = new \CharlotteDunois\Yasmin\Client(array(), $loop);
$handler = new \Ancestor\CommandHandler\CommandHandler($client, $config['prefix']);
$handler->registerCommands(glob(__DIR__ . '/data/bot_commands/*.php'));
$client->on('ready', function () use ($client) {
    echo 'Successful login into ' . $client->user->tag . PHP_EOL;
});

$client->on('message', function (CharlotteDunois\Yasmin\Models\Message $message) use ($config, $handler) {
    if ($message->author->bot) return;
    if ($handler->handleMessage($message)) {
        return;
    }
    $msgLowered = strtolower($message->content);
    $index = strpos($msgLowered, 'resolve is tested');
    if ($index !== false) {
        CheckResolveResponse($message, $index, $msgLowered);
        return;
    }

    if (IsChannelNSFW($message->channel) && mt_rand(1, 100) <= $config['NSFWchance']) {
        RespondNSFW($message);
    }
});
$token = getenv('abot_token');
if ($token === false) {
    $token = $config['token'];
}
$client->login($token);
$loop->run();

function CheckResolveResponse(CharlotteDunois\Yasmin\Models\Message $message, $index, $msgLowered) {
    $response = \Ancestor\RandomData\RandomDataProvider::GetRandomResolve();
    $embedResponse = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
    $embedResponse->setFooter($message->client->user->username, $message->client->user->getAvatarURL());
    $embedResponse->setDescription('***'.$response['quote'].'***');
    if ($index != 0) {
        if (!empty($message->mentions->users) && count($message->mentions->users) > 0) {
            $message->channel->send('**' . '<@' . $message->mentions->users->last()->id . '>' .
                ' is ' . $response['name'] . '**', array('embed' => $embedResponse));
            return;
        }
        $index = strpos($msgLowered, 'my resolve is tested');
        if ($index !== false) {
            $message->channel->send('**' . '<@' . $message->author->id . '>' .
                ' is ' . $response['name'] . '**', array('embed' => $embedResponse));
            return;
        }
    }
    $message->channel->send('**' . $response['name'] . '**', array('embed' => $embedResponse));
}

function RespondNSFW(CharlotteDunois\Yasmin\Models\Message $message) {
    if ((!empty($message->attachments) && count($message->attachments) > 0) ||
        (!empty($message->embeds) && count($message->embeds) > 0) ||
        StringContainsRealURLS($message->content)) {
        $embedResponse = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
        $embedResponse->setFooter($message->client->user->username, $message->client->user->getAvatarURL());
        $embedResponse->setDescription(\Ancestor\RandomData\RandomDataProvider::GetRandomNSFWQuote());
        $message->channel->send('', array('embed' => $embedResponse));
    }
}

function IsChannelNSFW(\CharlotteDunois\Yasmin\Interfaces\TextChannelInterface $channel) {
    if ((!empty($channel->nsfw) && $channel->nsfw === true) ||
        (!empty($channel->name) && strpos(strtolower($channel->name), 'nsfw') !== false)) {
        return true;
    }
    return false;
}

function StringContainsRealURLS($str) {
    foreach (explode(' ', str_replace(array("\r", "\n"), ' ', $str)) as $item) {
        if (filter_var($item, FILTER_VALIDATE_URL)) {
            return true;
        }
    }
    return false;
}
