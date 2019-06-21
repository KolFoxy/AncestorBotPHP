<?php

namespace Ancestor\Interaction;

class Hero extends AbstractLivingBeing {

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $stress = 0;

    /**
     * @var HeroClass
     */
    public $type;

    public function addStress(int $value) {
        //TODO:Implement the hecking method.
    }

    public function addHealth(int $value) {
        //TODO:Implement the hecking method.
    }

    public function addStressAndHealth(int $stressValue, int $healthValue) {
        $this->addStress($stressValue);
        $this->addHealth($healthValue);
    }

    public function getStressStatus(): string {
        return $this->stress . '/100';
    }

    public function __construct(HeroClass $class, string $name) {
        parent::__construct($class);
        $this->name = $name . ' the ' . $class->name;
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
    public function getEmbedResponse(string $commandName = null): \CharlotteDunois\Yasmin\Models\MessageEmbed {
        return $this->type->getEmbedResponse($commandName, $this->getStatus());
    }

    public function getStatus(): string {
        return 'Health: *' . $this->getHealthStatus() . '* | Stress: *' . $this->getStressStatus() . '*';
    }

}