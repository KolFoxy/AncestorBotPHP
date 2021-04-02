<?php

namespace Ancestor\AncestorTraits;

use Ancestor\BotIO\BotIoInterface;
use Ancestor\BotIO\EmbedObject;
use Ancestor\BotIO\MessageInterface;
use Ancestor\Command\CommandHelper;
use Ancestor\RandomData\RandomDataProvider;

trait NsfwResponseTrait {

    /**
     * @param MessageInterface $message
     * @param BotIoInterface $client
     * @param int $chance
     * @return bool Whether the message was handled or not
     */
    public function nsfwResponse(MessageInterface $message, BotIoInterface $client, int $chance): bool {
        if (!$message->getChannel()->isNSFW()) {
            return false;
        }
        if ((empty($message->getAttachments()) || count($message->getAttachments()) === 0) && (
                !$message->hasEmbeds()
                || !CommandHelper::stringContainsURLs($message->getContent())
            )
        ) {
            return false;
        }

        if (mt_rand(1, 100) <= $chance) {
            $embedResponse = new EmbedObject();
            $embedResponse->setFooter($client->getUser()->getUsername(), $client->getUser()->getAvatarURL());
            $embedResponse->setDescription(RandomDataProvider::getInstance()->getRandomNSFWQuote());
            $message->getChannel()->send('', $embedResponse);
        }

        return true;
    }
}