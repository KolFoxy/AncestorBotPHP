<?php

namespace Ancestor\AncestorTraits;

use Ancestor\BotIO\BotIoInterface;
use Ancestor\BotIO\EmbedObject;
use Ancestor\BotIO\MessageInterface;
use Ancestor\Interaction\Stats\StressStateFactory;

trait CheckResolveTrait {
    /**
     * @param MessageInterface $message
     * @param BotIoInterface $client
     * @return bool Whether the message was handled or not
     */
    public function checkResolveResponse(MessageInterface $message, BotIoInterface $client): bool {
        $msgLowered = mb_strtolower($message->getContent());
        $index = mb_strpos($msgLowered, 'resolve is tested');
        if ($index === false) {
            return false;
        }
        $stressState = StressStateFactory::create();
        $embedResponse = new EmbedObject();
        $embedResponse->setFooter($client->getUser()->getUsername(), $client->getUser()->getAvatarUrl());
        $embedResponse->setDescription('***' . $stressState->getQuote() . '***');
        if ($index !== 0) {
            if (!empty($message->getUserMentions()) && ($mentionsCount = count($message->getUserMentions())) > 0) {
                $mention = $message->getUserMentions()[$mentionsCount - 1]->getMention();
                $message->getChannel()->send('**' . $mention . ' is ' . $stressState->name . '**');
                return true;
            }
            $index = strpos($msgLowered, 'my resolve is tested');
            if ($index !== false) {
                $message->getChannel()->send('**' . $message->getAuthor()->getMention() .
                    ' is ' . $stressState->name . '**', $embedResponse);
                return true;
            }
        }
        $message->getChannel()->send('**' . $stressState->name . '**', $embedResponse);

        return true;
    }
}