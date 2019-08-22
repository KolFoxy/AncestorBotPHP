<?php

namespace Ancestor\CommandHandler;

use CharlotteDunois\Yasmin\Models\MessageReaction;
use CharlotteDunois\Yasmin\Models\User;

interface ReactionHandlerInterface {

    /**
     * @param MessageReaction $reaction
     * @param User $user
     * @return bool Whether or not the reaction was handled by the handler.
     */
    public function handleReaction(MessageReaction $reaction, User $user): bool;
}