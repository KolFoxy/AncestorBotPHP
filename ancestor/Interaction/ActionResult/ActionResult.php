<?php

namespace Ancestor\Interaction\ActionResult;

use Ancestor\Interaction\AbstractLivingBeing;
use Ancestor\Interaction\Stats\StatModifier;
use Ancestor\Interaction\Stats\Stats;
use Ancestor\CommandHandler\CommandHelper as Helper;
use Ancestor\Interaction\Stats\StatusEffect;

class ActionResult {
    /**
     * @var AbstractLivingBeing
     */
    public $target;
    /**
     * @var AbstractLivingBeing
     */
    public $caster;

    /**
     * @var string
     */
    public $actionName;

    /**
     * @var ActionResultFeed
     */
    protected $targetFeed;

    /**
     * @var ActionResultFeed
     */
    protected $casterFeed;

    /**
     * @var bool
     */
    public $miss = false;

    protected $message = '';

    /**
     * @var null|bool
     */
    protected $resistedDebuff = null;

    protected $isCrit = false;

    /**
     * @var string
     */
    public $description;

    const MISS_MESSAGE = '``...and misses!``';
    const CRIT_MESSAGE = ' ***CRIT!***';

    const SIGNED_DECIMAL_FORMAT = '%+d';


    public function __construct(AbstractLivingBeing $caster, AbstractLivingBeing $target, string $actionName, string $description) {
        $this->caster = $caster;
        $this->target = $target;
        $this->description = $description;
        $this->actionName = $actionName;
        $this->targetFeed = new ActionResultFeed();
        if ($target === $caster) {
            $this->casterFeed = $this->targetFeed;
            return;
        }
        $this->casterFeed = new ActionResultFeed();
    }

    public function toFields(string $extra = '', ?string $forcedTitle = null): array {
        $title = $forcedTitle ?? '**' . $this->caster->name . '** uses **' . $this->actionName . '**';
        if ($this->isCrit) {
            $title .= self::CRIT_MESSAGE;
        }
        $res = $this->__toString();
        $this->notEmptyAddEol($res, $extra);
        return Helper::getEmbedField($title, $res);
    }

    public function __toString(): string {
        $res = $this->miss ? self::MISS_MESSAGE : '';
        $this->notEmptyAddEol($res, $this->feedToResultString($this->targetFeed, $this->target->name, $this->target));
        if ($this->targetFeed === $this->casterFeed) {
            return $this->defaultResult($res);
        }
        $this->notEmptyAddEol($res, $this->feedToResultString($this->casterFeed, $this->caster->name, $this->caster));
        return $this->defaultResult($res);
    }

    protected function defaultResult(string &$res): string {
        if ($res === '') {
            $res = $this->description;
        }
        return $res;
    }

    protected function feedToResultString(ActionResultFeed $feed, string $name, AbstractLivingBeing $being): string {
        if ($feed->isEmpty()) {
            return '';
        }
        $res = $this->sourceValueArrayToResult($feed->health, 'HP');
        $feedHasStress = !empty($feed->stress);
        if ($res !== '' && $feedHasStress) {
            $res .= ', ';
        }
        $res .= $this->sourceValueArrayToResult($feed->stress, ' Stress');
        if ($res !== '') {
            $res = $name . ': ' . $res . '.' . PHP_EOL;
            if (!empty($feed->health)) {
                $res .= '*``' . $being->getHealthStatus() . '``*';
                if ($feedHasStress) {
                    $res .= '; ';
                }
            }
            if ($feedHasStress) {
                $res .= '*``' . $being->getStressStatus() . '``*';
            }
        }
        $this->notEmptyAddEol($res, $this->feedToStatusEffectResult($feed, $name));
        if ($being->isDead()) {
            $res .= PHP_EOL . '***DEATHBLOW***' . PHP_EOL . '***' . $being->getDeathQuote() . '***';
        }
        return $res;
    }

    protected function notEmptyAddEol(string &$str, string $toAdd) {
        if ($toAdd === '') {
            return;
        }
        if ($str === '') {
            $str = $toAdd;
            return;
        }
        $str .= PHP_EOL . $toAdd;
    }

    protected function feedToStatusEffectResult(ActionResultFeed $feed, string $targetName): string {
        $res = '';
        if (!empty($feed->newEffects)) {
            $res .= 'now has: ' . $this->combineResults($feed->newEffects);
        }
        if (!empty($feed->resisted)) {
            if ($res !== '') {
                $res .= '; ';
            }
            $res .= 'resisted: ' . $this->combineResults($feed->resisted);
        }
        if (!empty($feed->removedEffects)) {
            if ($res !== '') {
                $res .= '; ';
            }
            $res .= 'no longer has: ' . $this->combineResults($feed->removedEffects);
        }
        return $res === '' ? $res : ($targetName . ' ' . $res);
    }

    protected function combineResults(array &$results): string {
        $res = '';
        $maxIndex = count($results) - 1;
        for ($i = 0; $i <= $maxIndex; $i++) {
            $res .= '**``' . $results[$i] . '``**';
            if ($i !== $maxIndex) {
                $res .= ', ';
            }
        }
        return $res;
    }

    protected function sourceValueArrayToResult(array &$arr, string $valueName): string {
        $res = '';
        if (empty($arr)) {
            return $res;
        }
        $freeVal = 0;
        $maxIndex = count($arr) - 1;
        for ($i = 0; $i <= $maxIndex; $i++) {
            $value = $arr[$i]['value'];
            if ($arr[$i]['source'] === '') {
                $freeVal += $value;
                continue;
            }
            $res .= '**``'
                . ($value === 0 ? $value : sprintf(self::SIGNED_DECIMAL_FORMAT, $value))
                . $valueName
                . '(' . $arr[$i]['source']
                . ')``**';
            if ($i !== $maxIndex) {
                $res .= ', ';
            }
        }
        if ($freeVal !== 0) {
            $freeValStr = '**``' . sprintf(self::SIGNED_DECIMAL_FORMAT, $freeVal) . $valueName . '``**';
            $res = $res === '' ? $freeValStr : ($freeValStr . ', ' . $res);
        }
        return $res;
    }

    public function addMessage(string $message) {
        if ($this->message === '') {
            $this->message = $message;
        }
        $this->message .= PHP_EOL . $this->message;
    }

    public function setCrit() {
        $this->isCrit = true;
    }

    public function setMiss() {
        $this->miss = true;
    }


    public function healthToTarget(int $healthValue, string $source = '') {
        $this->healthToBeing($this->target, $this->targetFeed, $healthValue, $source);
    }

    public function stressToTarget(int $stressValue, string $source = '') {
        $this->stressToBeing($this->target, $this->targetFeed, $stressValue, $source);
    }

    public function healthToCaster(int $healthValue, string $source = '') {
        $this->healthToBeing($this->caster, $this->casterFeed, $healthValue, $source);
    }

    public function stressToCaster(int $stressValue, string $source = '') {
        $this->stressToBeing($this->caster, $this->casterFeed, $stressValue, $source);
    }

    protected function stressToBeing(AbstractLivingBeing $being, ActionResultFeed $feed, int $value, string $source) {
        if (!$being->hasStress() || $value === 0) {
            return;
        }
        $being->addStress($value);
        $feed->stress[] = ['value' => $value, 'source' => $source];
    }

    protected function healthToBeing(AbstractLivingBeing $being, ActionResultFeed $feed, int $value, string $source) {
        $being->addHealth($value);
        $feed->health[] = ['value' => $value, 'source' => $source];
    }

    public function removeBlightBleedStealthFromTarget(bool $removeBlight, bool $removeBleed, bool $removeStealth) {
        if ($removeStealth && $this->target->isStealthed()) {
            $this->target->statManager->removeStatusEffectType(StatusEffect::TYPE_STEALTH);
            $this->targetFeed->removedEffects[] = StatusEffect::TYPE_STEALTH;
        }
        if ($removeBlight && $this->target->statManager->removeBlight()) {
            $this->targetFeed->removedEffects[] = StatusEffect::TYPE_BLIGHT;
        }
        if ($removeBleed && $this->target->statManager->removeBleeds()) {
            $this->targetFeed->removedEffects[] = StatusEffect::TYPE_BLEED;
        }
    }

    public function removedFromTarget(string $whatWasRemoved) {
        $this->targetFeed->removedEffects[] = $whatWasRemoved;
    }

    public function removedFromCaster(string $whatWasRemoved) {
        $this->casterFeed->removedEffects[] = $whatWasRemoved;
    }

    /**
     * @param $timedEffect StatusEffect|StatModifier
     */
    public function addTimedEffect($timedEffect) {
        $toAdd = $timedEffect->clone();
        if (!$toAdd->guaranteedApplication()) {
            $toAdd->chance += $this->caster->statManager->getStatValue($toAdd->getType() . Stats::SKILL_CHANCE_SUFFIX) ?? 0;
        }
        if ($toAdd->targetSelf) {
            $effectTarget = $this->caster;
            $feed = $this->casterFeed;
        } else {
            $effectTarget = $this->target;
            $feed = $this->targetFeed;
        }
        if ($effectTarget->isDead()) {
            return;
        }
        if (is_a($toAdd, StatusEffect::class)) {
            if ($effectTarget->statManager->addStatusEffect($toAdd)) {
                $feed->newEffects[] = $effectTarget->statManager->getStatusEffectState($toAdd->getType());
                return;
            }
            $feed->resisted[] = $toAdd->getType();
            return;
        }

        if ($toAdd->getType() === StatModifier::TYPE_DEBUFF) {
            if ($this->resistedDebuff === true) {
                return;
            }
            if ($this->resistedDebuff === false) {
                $toAdd->chance = -1;
                $effectTarget->statManager->addModifier($toAdd);
            }
            if (is_null($this->resistedDebuff)) {
                $this->resistedDebuff = !$effectTarget->statManager->addModifier($toAdd);
                if ($this->resistedDebuff === true) {
                    $feed->resisted[] = StatModifier::TYPE_DEBUFF;
                    return;
                }
            }
        } else {
            $effectTarget->statManager->addModifier($toAdd);
        }
        $feed->newEffects[] = $toAdd->__toString();
    }


}