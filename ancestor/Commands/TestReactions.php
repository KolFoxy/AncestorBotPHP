<?php

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;
use Ancestor\CommandHandler\CommandHelper;
use CharlotteDunois\Collect\Collection;
use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\MessageReaction;
use CharlotteDunois\Yasmin\Models\User;

class TestReactions extends Command {

    public function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'tr', 'tr');
    }

    public function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        $channel = $message->channel;
        $message->reply('Collecting reactions to this message.')
            ->then(function (Message $sentMessage) {
                return $sentMessage->collectReactions(
                    function (MessageReaction $reaction, User $user) {
                        return !is_null(CommandHelper::emojiToNumber($reaction->emoji->name));
                    },
                    ['max' => 1, 'time' => 30,]
                );
            })
            ->then(function (Collection $collection) use ($channel) {
                if ($collection->first() === null) {
                    return $channel->send('There were no reactions.');
                }
                $user = $this->resultCollectionItemToUser($collection->first());
                $reaction = $this->resultCollectionItemToMessageReaction($collection->first());
                return $channel->send($user->__toString() . ' reacted with ' . $reaction->emoji->__toString()
                    . ' which has the uid of "' . $reaction->emoji->uid . '"');
            },
                function (\Exception $exception) use ($channel) {
                    return $channel->send($exception->getMessage());
                }
            )
            ->then(function (Message $sentMessage) {
                $sentMessage->react('1⃣');
            });
    }

    function resultCollectionItemToUser(array $res): User {
        return $res[1];
    }

    function resultCollectionItemToMessageReaction(array $res): MessageReaction {
        return $res[0];
    }

}