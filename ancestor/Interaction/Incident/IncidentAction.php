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
     * @throws \Exception
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
        if (is_string($resultIncident)) {
            $resultIncident = dirname(__DIR__, 3) . $resultIncident;
            if (!file_exists($resultIncident)) {
                throw new \Exception('ERROR: File "' . $resultIncident . '" doesn\'t exist.)');
            }

            if (mb_substr($resultIncident, -4) === '.php') {
                $this->resultIncident = require($resultIncident);
                return;
            }
            try {
                $this->resultIncident = $mapper->map(json_decode(file_get_contents($resultIncident)), new Incident());
            } catch (\Exception $e) {
                echo('Provided failed $resultIncident:');
                var_dump($resultIncident);
                throw $e;
            }
            return;
        }
        if (is_object($resultIncident)) {
            try {
                $this->resultIncident = $mapper->map($resultIncident, new Incident());
            } catch (\Exception $e) {
                echo('Provided failed $resultIncident:');
                var_dump($resultIncident);
                throw $e;
            }
            return;
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

    /**
     * @param Hero $hero
     * @param MessageEmbed $res
     * @return Incident|null Result Incident
     */
    public function getResult(Hero $hero, MessageEmbed $res): ?Incident {
        $res->setTitle('*' . $this->name . '*');
        $description = '*``' . $this->effect->getDescription() . '``*';
        if ($this->effect->image !== null) {
            $res->setThumbnail($this->effect->image);
        }
        $resultIncident = $this->getResultIncident();
        if ($resultIncident !== null) {
            if ($resultIncident->image !== null) {
                $res->setImage($resultIncident->image);
            }
            if ($resultIncident->description !== "") {
                $description .= PHP_EOL . '*``' . $resultIncident->description . '``*';
            }
        }
        $description .= PHP_EOL . $this->applyEffectsGetResults($hero);
        $res->setDescription($description);
        return $resultIncident;
    }

    protected function applyEffectsGetResults(Hero $hero): string {
        $res = new ActionResult($hero, $hero, '', '');
        $healthResult = 0;
        $stressResult = 0;
        $this->effect->getApplicationResult($hero, $healthResult, $stressResult);
        $res->stressToTarget($stressResult);
        $res->healthToTarget($healthResult);
        $this->addTimedEffectsToResult($res);
        $res->removeBlightBleedStealthFromTarget($this->effect->removesBlight, $this->effect->removesBleed, false, $this->effect->removesDebuff);
        return $res->__toString();
    }

    protected function addTimedEffectsToResult(ActionResult $result) {
        if ($this->statusEffects !== null) {
            foreach ($this->statusEffects as $statusEffect) {
                $result->addTimedEffect($statusEffect);
            }
        }
        if ($this->statModifiers !== null) {
            foreach ($this->statModifiers as $statModifier) {
                $result->addTimedEffect($statModifier);
            }
        }
    }

    public function isFinal(): bool {
        return $this->resultIncident === null;
    }
}