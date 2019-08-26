<?php

namespace Ancestor\Interaction\Incident;

use Ancestor\Interaction\AbstractAction;
use Ancestor\Interaction\ActionResult\ActionResult;
use Ancestor\Interaction\Hero;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class IncidentAction extends AbstractAction {

    /**
     * @var \Ancestor\Interaction\Effect
     */
    public $effect;

    /**
     * @var Incident|null
     */
    protected $resultIncident = null;

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

    /**
     * @return Incident|null
     */
    public function getResultIncident(): ?Incident {
        return $this->resultIncident;
    }

    /**
     * @param mixed|null $resultIncident
     * @throws \JsonMapper_Exception
     */
    public function setResultIncident($resultIncident): void {
        if ($resultIncident === null) {
            return;
        }
        if ($resultIncident instanceof Incident) {
            $this->resultIncident = $resultIncident;
            return;
        }
        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        if (is_object($resultIncident)) {
            $this->resultIncident = $mapper->map($resultIncident, new Incident());
            return;
        }
        if (is_string($resultIncident)) {
            $resultIncident = dirname(__DIR__, 3) . $resultIncident;
            if (!file_exists($resultIncident)) {
                throw new \Exception('ERROR: File "' . $resultIncident . '" doesn\'t exist.)');
            }
            $this->resultIncident = $mapper->map(json_decode(file_get_contents($resultIncident)), new Incident());
        }
    }

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

    public function getResult(Hero $hero, MessageEmbed $res): MessageEmbed {
        $res->setTitle('*' . $this->name . '*');
        $res->setDescription('*``' . $this->effect->getDescription() . '``*' . PHP_EOL . $this->applyEffectsGetResults($hero));
        if ($this->effect->image !== null) {
            $res->setThumbnail($this->effect->image);
        }
        if ($this->resultIncident !== null) {
            $res->addField($this->resultIncident->name, $this->resultIncident->description);
            $res->setImage($this->resultIncident->image);
        }
        return $res;
    }

    protected function applyEffectsGetResults(Hero $hero): string {
        $res = new ActionResult($hero, $hero, '', '');
        $healthResult = 0;
        $stressResult = 0;
        $this->effect->getApplicationResult($hero, $healthResult, $stressResult);
        $res->stressToTarget($stressResult);
        $res->healthToTarget($healthResult);
        if ($this->statusEffects !== null) {
            foreach ($this->statusEffects as $statusEffect) {
                $res->addTimedEffect($statusEffect);
            }
        }
        if ($this->statModifiers !== null) {
            foreach ($this->statModifiers as $statModifier) {
                $res->addTimedEffect($statModifier);
            }
        }
        $res->removeBlightBleedStealthFromTarget($this->effect->removesBlight, $this->effect->removesBleed, false, $this->effect->removesDebuff);
        return $res->__toString();
    }

    public function isFinal(): bool {
        return $this->resultIncident === null;
    }
}