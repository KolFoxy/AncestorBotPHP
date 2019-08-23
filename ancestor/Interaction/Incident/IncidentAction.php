<?php

namespace Ancestor\Interaction\Incident;

use Ancestor\Interaction\AbstractAction;
use Ancestor\Interaction\ActionResult\ActionResult;
use Ancestor\Interaction\ActionResult\ActionResultFeed;
use Ancestor\Interaction\Hero;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class IncidentAction extends AbstractAction {

    /**
     * @var \Ancestor\Interaction\Effect
     * @required
     */
    public $effect;

    /**
     * @var Incident|null
     */
    public $resultIncident = null;

    /**
     * @var string[]|null List of classes that this action is available for.
     */
    protected $exclusiveClasses = null;

    /**
     * @var \Ancestor\Interaction\Stats\StatusEffect[]|null
     */
    public $statusEffects = null;

    /**
     * @var \Ancestor\Interaction\Stats\StatModifier[]|null
     */
    public $statModifiers = null;

    public function isAvailableForClass(?string $className): bool {
        if ($this->exclusiveClasses === null) {
            return true;
        }
        if ($className === null) {
            return false;
        }
        $className = mb_strtolower(trim($className));
        foreach ($this->exclusiveClasses as $class) {
            if ($className === $class) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return null|string[]
     */
    public function getExclusiveClasses(): ?array {
        return $this->exclusiveClasses;
    }

    /**
     * @param null|string[] $exclusiveClasses
     */
    public function setExclusiveClasses(?array $exclusiveClasses): void {
        if ($exclusiveClasses === null) {
            return;
        }
        $this->exclusiveClasses = [];
        foreach ($exclusiveClasses as $class) {
            $this->exclusiveClasses[] = mb_strtolower(trim($class));
        }
    }

    public function getResult(Hero $hero): MessageEmbed {
        /** @noinspection PhpUnhandledExceptionInspection */
        $res = new MessageEmbed();
        $res->setTitle('*' . $this->name . '*');
        $res->setDescription('*``' . $this->effect->getDescription() . '``*' . PHP_EOL . $this->applyEffectsGetResults($hero));
        return $res;
    }

    protected function applyEffectsGetResults(Hero $hero) {
        $res = new ActionResult($hero, $hero, '', $this->effect->getDescription());
        $this->effect->getApplicationResult($hero, $healthResult, $stressResult);
        $res->stressToTarget($stressResult);
        $res->healthToTarget($healthResult);
        foreach ($this->statusEffects as $statusEffect) {
            $res->addTimedEffect($statusEffect);
        }
        foreach ($this->statModifiers as $statModifier) {
            $res->addTimedEffect($statModifier);
        }
        $res->removeBlightBleedStealthFromTarget($this->effect->removesBlight, $this->effect->removesBleed, false, $this->effect->removesDebuff);
        return $res->__toString();
    }

    public function isFinal(): bool {
        return $this->resultIncident === null;
    }
}