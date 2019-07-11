<?php

namespace Ancestor\Interaction;

use Ancestor\CommandHandler\CommandHelper;
use Ancestor\Interaction\Stats\Stats;
use Ancestor\Interaction\Stats\Trinket;
use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class Hero extends AbstractLivingBeing {


    const HEART_ATTACK_MESSAGE = ' HEART ATTACK!';

    const MAX_STRESS = 199;

    const STRESS_ROLLBACK = 170;

    const AT_DEATH_S_DOOR_MESSAGE = ' AT DEATH\'S DOOR!';

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
     * @var string
     */
    private $bonusStressMessage = '';

    /**
     * @var string
     */
    private $bonusHealthMessage = '';

    /**
     * @var Trinket[]
     */
    public $artifacts = [
        0 => null,
        1 => null,
    ];


    const DEAD_MESSAGE = ' DEAD';

    public function addStress(int $value) {
        if ($this->isActuallyDead) {
            return;
        }
        $this->stress += (int)($value * $this->statManager->getValueMod(Stats::STRESS_MOD));
        if ($this->stress < 0) {
            $this->stress = 0;
            return;
        }
        if ($this->stress > self::MAX_STRESS) {
            $this->bonusStressMessage = self::HEART_ATTACK_MESSAGE;
            if ($this->currentHealth === 0) {
                $this->isActuallyDead = true;
                $this->bonusHealthMessage = '';
                return;
            }
            $this->currentHealth = 0;
            $this->stress = self::STRESS_ROLLBACK;
            $this->bonusHealthMessage = self::AT_DEATH_S_DOOR_MESSAGE;
        }
    }

    public function addHealth(int $value) {
        if ($this->isActuallyDead) {
            return;
        }
        $atDeathDoor = $this->currentHealth === 0;
        $this->currentHealth += $value;
        if ($atDeathDoor && $this->currentHealth < 0) {
            if (mt_rand(1, 100) > $this->statManager->getStatValue(Stats::DEATHBLOW_RESIST)) {
                $this->bonusHealthMessage = self::DEAD_MESSAGE;
                $this->bonusStressMessage = '';
                $this->isActuallyDead = true;
                return;
            }
        }
        if ($this->currentHealth > $this->healthMax) {
            $this->currentHealth = $this->healthMax;
            return;
        }
        if ($this->currentHealth <= 0) {
            $this->currentHealth = 0;
            $this->bonusHealthMessage = self::AT_DEATH_S_DOOR_MESSAGE;
            return;
        }
        $this->bonusHealthMessage = '';
    }

    public function getStressStatus(): string {
        return 'Stress: ' . $this->stress . '/100' . $this->getBonusMessage($this->bonusStressMessage);
    }

    public function getHealthStatus(): string {
        return parent::getHealthStatus() . $this->getBonusMessage($this->bonusHealthMessage);
    }


    public function getStatus(): string {
        return $this->getHealthStatus() . ' | ' . $this->getStressStatus();
    }


    public function __construct(HeroClass $class, string $name) {
        parent::__construct($class);
        $this->name = $name . ' the ' . $class->name;
    }

    public function isDead(): bool {
        $this->addHealth(0);
        $this->addStress(0);
        return $this->isActuallyDead;
    }

    /**
     * @param string $commandName
     * @return \CharlotteDunois\Yasmin\Models\MessageEmbed
     */
    public function getEmbedResponse(string $commandName = null): MessageEmbed {
        return $this->type->getEmbedResponse($commandName, $this->getStatus());
    }

    private function getBonusMessage(string &$bonusString): string {
        if ($bonusString === '') {
            return '';
        }
        $res = $bonusString;
        $bonusString = '';
        return $res;
    }

    /**
     * @param DirectAction $action
     * @param AbstractLivingBeing $target
     * @return MessageEmbed
     */
    public function getHeroTurn(DirectAction $action, AbstractLivingBeing $target): MessageEmbed {
        $res = new MessageEmbed();
        if ($action === $this->type->defaultAction()) {
            $target = $this;
        }
        if (!$this->statManager->isStunned()) {
            $res->setThumbnail($action->effect->image);
        }
        $fields = $this->getTurn($target, $action);
        $topField = array_shift($fields);
        $res->setTitle($topField['name']);
        $res->setDescription($topField['value']);
        CommandHelper::mergeEmbed($res, $fields);
        return $res;
    }

    public function getDeathQuote(): string {
        return RandomDataProvider::GetInstance()->GetRandomHeroDeathQuote();
    }


}