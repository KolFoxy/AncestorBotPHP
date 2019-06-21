<?php

namespace Ancestor\Interaction;

class Hero extends AbstractLivingBeing {
    /**
     * @var int
     */
    public $stress = 0;

    /**
     * @var HeroClass
     */
    public $type;


    public function getStressStatus(): string {
        return $this->stress . '/100';
    }

    public function __construct(HeroClass $class) {
        $this->type = $class;
        $this->healthMax = $class->healthMax;
    }

    public function isDead(): bool {
        if ($this->stress >= 200) {
            return true;
        }
        return parent::isDead();
    }

    /**
     * @param string $commandName
     * @return \CharlotteDunois\Yasmin\Models\MessageEmbed
     */
    public function getEmbedResponse(string $commandName = null) : \CharlotteDunois\Yasmin\Models\MessageEmbed {
        return $this->type->getEmbedResponse($commandName, $this->getStatus());
    }

    public function getStatus(): string {
        return 'Health: *' . $this->getHealthStatus() . '* | Stress: *' . $this->getStressStatus() . '*';
    }

}