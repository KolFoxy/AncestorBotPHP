<?php

namespace Ancestor\Interaction;

use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use function GuzzleHttp\Psr7\str;
use function MongoDB\BSON\toJSON;

class Hero extends AbstractLivingBeing {


    const HEART_ATTACK_MESSAGE = ' HEART ATTACK!';

    const MAX_STRESS = 199;

    const STRESS_ROLLBACK = 170;

    const DEATH_DOOR_RESIST = 67;

    const CRIT_STRESS_HEAL = -3;

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


    public function addStress(int $value) {
        if ($this->isActuallyDead) {
            return;
        }
        $this->stress += $value;
        if ($this->stress < 0) {
            $this->stress = 0;
            return;
        }
        if ($this->stress > self::MAX_STRESS) {
            $this->bonusStressMessage = self::HEART_ATTACK_MESSAGE;
            if ($this->currentHealth === 0) {
                $this->isActuallyDead = true;
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
            $this->bonusHealthMessage = self::AT_DEATH_S_DOOR_MESSAGE;
            return;
        }
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
     * @param AbstractLivingBeing|Hero $target
     * @return MessageEmbed
     */
    public function getHeroTurn(DirectAction $action, AbstractLivingBeing $target): MessageEmbed {
        $res = new MessageEmbed();
        $isDefaultAction = $action === $this->type->defaultAction();

        if ($isDefaultAction) {
            $target = $this;
        }

        $title = ('**' . $this->name . '** uses **' . $action->name . '**!');
        $effect = $action->effect;
        $res->setThumbnail($effect->image);
        if (!$isDefaultAction && !$effect->isHealEffect() && !$effect->isPositiveStressEffect() && !$this->rollWillHit($target, $effect)) {
            $res->setDescription(self::MISS_MESSAGE);
            $res->setTitle($title);
            return $res;
        }

        $isCrit = !$isDefaultAction && $this->rollWillCrit($effect);
        $stressEffect = $effect->getStressValue();
        $healthEffect = $effect->getHealthValue();
        if ($isCrit) {
            $title .= self::CRIT_MESSAGE;
            $healthEffect *= 2;
        }
        $targetIsHero = is_a($target, Hero::class);
        $targetName = $targetIsHero ? $target->name : $target->type->name;
        $res->setDescription('*``' . $effect->getDescription() . '``*');
        $res->setTitle($title);

        $target->addHealth($healthEffect);
        if ($effect->isHealEffect()) {
            $res->addField('**' . $targetName . '** is healed for **' . abs($healthEffect) . 'HP**!'
                , '*``' . $target->getHealthStatus() . '``*');
            if ($isCrit) {
                $stressEffect -= 10;
            }
        } elseif ($effect->isDamageEffect()) {
            $res->addField('**' . $targetName . '** gets hit for **' . abs($healthEffect) . 'HP**!'
                , '*``' . $target->getHealthStatus() . '``*');
            if ($isCrit && $this->stress != 0) {
                $this->addStress(self::CRIT_STRESS_HEAL);
                $res->addField('**' . $this->name . '** feels confident! **' . self::CRIT_STRESS_HEAL . ' stress**!'
                    , '*``' . $this->getStressStatus() . '``*');
            }
        }

        if ($targetIsHero && $stressEffect !== 0) {
            $target->addStress($stressEffect);
            if ($stressEffect < 0) {
                $res->addField('**' . $targetName . '** feels less tense. **' . $stressEffect . ' stress**!'
                    , '*``' . $target->getStressStatus() . '``*');
            } elseif ($stressEffect > 0) {
                $res->addField('**' . $targetName . '** suffers **' . $stressEffect . ' stress**!'
                    , '*``' . $target->getStressStatus() . '``*');
            }
        }

        if ($target->isDead()) {
            $res->addField('***DEATHBLOW***', '***' . RandomDataProvider::GetInstance()->GetRandomMonsterDeathQuote() . '***');
        }

        return $res;
    }


}