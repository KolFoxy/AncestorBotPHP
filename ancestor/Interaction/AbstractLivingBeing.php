<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\Stats\Stats;
use Ancestor\Interaction\Stats\StatsManager;
use function Composer\Autoload\includeFile;

abstract class AbstractLivingBeing {

    const MISS_MESSAGE = '``...and misses!``';
    const CRIT_MESSAGE = ' ***CRIT!***';

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $healthMax;

    /**
     * @var int
     */
    protected $currentHealth;

    /**
     * @var StatsManager;
     */
    public $statManager;

    /**
     * @var AbstractLivingInteraction;
     */
    public $type;

    /**
     * @var int|null
     */
    public $stress = null;


    /**
     * @return string Format: "Health: currentHealth/healthMax"
     */
    public function getHealthStatus(): string {
        return 'Health: ' . $this->currentHealth . '/' . $this->healthMax;
    }

    public function addStress($value) {
        if ($this->hasStress()) {
            $this->stress += $value;
        }
    }

    public function getStressStatus(): string {
        if (!$this->hasStress()) {
            return '';
        }
        return 'Stress: ' . $this->stress;
    }

    public function hasStress(): bool {
        return $this->stress !== null;
    }

    /**
     * @return bool
     */
    public function isDead(): bool {
        return $this->currentHealth <= 0;
    }

    /**
     * @param AbstractLivingBeing $target
     * @param Effect $effect
     * @return bool
     */
    public function rollWillHit(AbstractLivingBeing $target, Effect $effect): bool {
        if ($effect->hitChance >= 0 && mt_rand(1, $this->statManager->getStatValue(Stats::ACC_MOD) + $effect->hitChance)
            <= $target->statManager->getStatValue(Stats::DODGE)) {
            return false;
        }
        return true;
    }

    public function rollWillCrit(Effect $effect): bool {
        return $effect->canCrit() && mt_rand(1, 100) <= ($effect->critChance + $this->statManager->getStatValue(Stats::CRIT_CHANCE));
    }

    public function __construct(AbstractLivingInteraction $type) {
        $this->name = $type->name;
        $this->type = $type;
        $this->currentHealth = $this->healthMax = $type->healthMax;
        $this->statManager = new StatsManager($type->stats);
    }

    /**
     * @param int $value
     */
    abstract public function addHealth(int $value);

    abstract public function getDeathQuote(): string;

    public function getStunnedTurn(): array {
        $res = array_merge(
            ['name' => '**' . $this->name . '** has skipped their turn due to stun.',
                'value' => '...and did nothing.',
                'inline' => false,],
            $this->statManager->getProcessTurn()
        );

        if ($this->isDead()) {
            $res[] = [
                'name' => '***' . $this->name . ' has deceased.***',
                'value' => '***' . $this->getDeathQuote() . '***',
                'inline' => false,
            ];
        }
        return $res;
    }

    public function getTurn(AbstractLivingBeing $target, DirectAction $action): array {
        if ($this->statManager->isStunned()) {
            return $this->getStunnedTurn();
        }
        $res = [];
        $title = ('**' . $this->name . '** uses **' . $action->name . '**!');
        $effect = $action->effect;

        if (!$this->rollWillHit($target, $effect)) {
            $res[] = ['name' => $title, 'value' => self::MISS_MESSAGE, 'inline' => false];
            return $res;
        }
        // TODO finish the method
    }


}