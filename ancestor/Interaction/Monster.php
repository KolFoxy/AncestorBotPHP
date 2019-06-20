<?php

namespace Ancestor\Interaction;

use CharlotteDunois\Yasmin\Models\MessageEmbed;

class Monster extends AbstractLivingInteraction {

    /**
     * @var MonsterType
     */
    public $type;

    public function __construct(MonsterType $monsterType) {
        $this->type = $monsterType;
        $this->healthMax = $monsterType->healthMax;
    }

    /**
     * @param string $commandName
     * @param Action[]|null $userActions
     * @return MessageEmbed
     */
    public function getEmbedResponse(string $commandName, array $userActions = null) : MessageEmbed {
        return $this->type->getEmbedResponse($commandName, $userActions, $this->getHealthStatus());
    }
}