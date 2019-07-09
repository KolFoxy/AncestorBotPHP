<?php

namespace Ancestor\Interaction;

use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class Monster extends AbstractLivingBeing {

    /**
     * @var MonsterType
     */
    public $type;

    public function __construct(MonsterType $monsterType) {
        parent::__construct($monsterType);
    }

    /**
     * @param string $commandName
     * @param Action[]|null $userActions
     * @return MessageEmbed
     */
    public function getEmbedResponse(string $commandName, array $userActions = null): MessageEmbed {
        return $this->type->getEmbedResponse($commandName, $userActions, $this->getHealthStatus());
    }

    public function getDeathQuote(): string {
        return RandomDataProvider::GetInstance()->GetRandomMonsterDeathQuote();
    }

    public function addHealth(int $value) {
        $this->currentHealth += $value;
        if ($this->currentHealth > $this->healthMax) {
            $this->currentHealth = $this->healthMax;
        }
    }

}