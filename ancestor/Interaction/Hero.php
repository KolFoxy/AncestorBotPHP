<?php

namespace Ancestor\Interaction;

use function GuzzleHttp\Psr7\str;

class Hero extends AbstractLivingBeing {

    const MAX_STRESS = 199;

    const STRESS_ROLLBACK = 170;

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

    /**
     * @var bool
     */
    private $isActuallyDead = false;

    /**
     * @var string|null
     */
    private $bonusStressMessage = null;

    /**
     * @var string|null
     */
    private $bonusHealthMessage = null;


    public function addStress(int $value) {
        $this->stress += $value;
        if ($this->stress < 0) {
            $this->stress = 0;
            return;
        }
        if ($this->stress > self::MAX_STRESS) {
            if ($this->getCurrentHealth() === 0) {
                $this->isActuallyDead = true;
                return;
            }
            $this->currentHealth = 0;
            $this->stress = self::STRESS_ROLLBACK;
            $this->bonusStressMessage = ' Heart attack!';
        }
    }

    public function addHealth(int $value) {
        //TODO:Implement the hecking method.
    }

    public function addStressAndHealth(int $stressValue, int $healthValue) {
        $this->addStress($stressValue);
        $this->addHealth($healthValue);
    }

    public function getStressStatus(): string {
        return $this->stress . '/100' . $this->getBonusMessage($this->bonusStressMessage);
    }

    public function getHealthStatus(): string {
        return parent::getHealthStatus() . $this->getBonusMessage($this->bonusHealthMessage);
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

    private function getBonusMessage(string &$bonusString): string {
        if ($bonusString === null) {
            return '';
        }
        $res = $bonusString;
        $bonusString = null;
        return $res;
    }

}