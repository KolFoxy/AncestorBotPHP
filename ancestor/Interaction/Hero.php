<?php

namespace Ancestor\Interaction;

use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use function GuzzleHttp\Psr7\str;

class Hero extends AbstractLivingBeing {

    const MAX_STRESS = 199;

    const STRESS_ROLLBACK = 170;

    const DEATH_DOOR_RESIST = 67;

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


    const CRIT_STRESS_HEAL = -3;

    public function addStress(int $value) {
        $this->stress += $value;
        if ($this->stress < 0) {
            $this->stress = 0;
            return;
        }
        if ($this->stress > self::MAX_STRESS) {
            $this->bonusStressMessage = ' Heart attack!';
            if ($this->currentHealth === 0) {
                $this->isActuallyDead = true;
                return;
            }
            $this->addHealth(-$this->currentHealth);
            $this->stress = self::STRESS_ROLLBACK;
        }
    }

    public function addHealth(int $value) {
        if ($this->currentHealth === 0 && ($this->currentHealth += $value) < 0) {
            if (mt_rand(1, 100) > self::DEATH_DOOR_RESIST) {
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
            $this->bonusHealthMessage = ' At Death\'s Door!';
            return;
        }
    }

    public function addStressAndHealth(int $stressValue, int $healthValue) {
        $this->addHealth($healthValue);
        $this->addStress($stressValue);
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
        return $this->isActuallyDead;
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

    /**
     * @param Action $action
     * @param AbstractLivingBeing|Hero $target
     * @param Effect|null $forcedEffect
     * @return MessageEmbed
     */
    public function getHeroTurn(Action $action, AbstractLivingBeing $target, Effect $forcedEffect = null): MessageEmbed {
        $res = new MessageEmbed();
        $res->setTitle('**' . $this->name . '** uses **' . $action->name . '!**');
        $effect = is_null($forcedEffect) ? $action->getRandomEffect() : $forcedEffect;
        $res->setThumbnail($effect->image);
        $res->setFooter($this->getStatus());
        if (!$effect->isHeal && !$effect->isPositiveStressEffect() && !$this->rollWillHit($target)) {
            $res->setDescription('...and misses!');
            return $res;
        }

        $description = $effect->getDescription();
        $isCrit = $this->rollWillCrit($effect);
        if ($isCrit) {
            $description .= ' ***CRITICAL STRIKE!***';
        }
        $res->setDescription($description);

        $stressEffect = $effect->getStressValue();
        $healthEffect = $effect->getHealthValue();
        $targetIsHero = is_a($target, Hero::class);
        $targetName = $targetIsHero ? $target->name : $target->type->name;

        $target->addHealth($healthEffect);
        if ($effect->isHeal) {
            $res->addField('``' . $targetName . ' is healed for ' . $healthEffect . '!``'
                , '*Health: ' . $target->getStressStatus() . '*');
            if ($isCrit) {
                $stressEffect -= 10;
            }
        } elseif ($effect->isNegativeHealthEffect()) {
            $res->addField('``' . $targetName . ' gets hit for ' . $healthEffect . ' HP!``'
                , '*Health: ' . $target->getHealthStatus() . '*');
            if ($isCrit && $this->stress != 0) {
                $this->addStress(self::CRIT_STRESS_HEAL);
                $res->addField('``' . $this->name . ' feels confident! ' . self::CRIT_STRESS_HEAL . ' Stress!``'
                    , '*Stress: ' . $target->getStressStatus() . '*');
            }
        }

        if ($targetIsHero) {
            $target->addStress($stressEffect);
            if ($stressEffect < 0) {
                $res->addField('``' . $targetName . ' feels less tense. ' . $stressEffect . ' Stress!``'
                    , '*Stress: ' . $target->getStressStatus() . '*');
            } elseif ($stressEffect > 0) {
                $res->addField('``' . $targetName . ' suffers ' . $stressEffect . ' stress!``'
                    , '*Stress: ' . $target->getStressStatus() . '*');
            }
        }


        if ($target->isDead() && !$targetIsHero) {
            $res->addField('***DEATHBLOW***', '***' . RandomDataProvider::GetInstance()->GetRandomMonsterDeathQuote() . '***');
        }


    }

}