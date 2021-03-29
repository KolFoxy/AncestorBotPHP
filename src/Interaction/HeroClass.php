<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\SpontaneousAction\SpontaneousAction;

class HeroClass extends AbstractLivingInteraction {

    /**
     * @var DirectAction|null
     */
    private ?DirectAction $defAction = null;

    const EMBED_COLOR = 13294;

    /**
     * Color of the embedResponse
     * @var integer
     */
    public int $embedColor = self::EMBED_COLOR;

    /**
     * @var null|HeroClass
     */
    protected ?HeroClass $transformClass = null;

    /**
     * @var null|SpontaneousAction[]
     */
    public ?array $spontaneousActions = null;

    /**
     * @param HeroClass|null $transformClass
     */
    public function setTransformClass(?HeroClass $transformClass): void {
        if ($transformClass === null) {
            return;
        }
        $this->transformClass = $transformClass;
        $this->transformClass->transformClass = $this;
    }

    /**
     * @return HeroClass|null
     */
    public function getTransformClass(): ?HeroClass {
        return $this->transformClass;
    }

    /**
     * @return DirectAction|null
     */
    public function getTransformAction(): ?DirectAction {
        foreach ($this->actions as $action) {
            if ($action->name === DirectAction::TRANSFORM_ACTION) {
                return $action;
            }
        }
        return null;
    }

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * @return DirectAction
     */
    public function defaultAction(): DirectAction {
        if ($this->defAction === null) {
            $action = new DirectAction();
            $action->name = 'pass turn';
            $action->requiresTarget = true;
            $effect = new DirectActionEffect();
            /** @noinspection PhpUnhandledExceptionInspection */
            $effect->setDescription('Hero passed the turn and suffered stress.');
            $effect->stress_value = 6;
            $effect->stressDeviation = 4;
            $effect->hitChance = -1;
            $effect->critChance = -1;
            $action->effect = $effect;
            $this->defAction = $action;
        }
        return $this->defAction;
    }

    /**
     * @param string $actionName
     * @return DirectAction|null
     */
    public function getActionIfValid(string $actionName) : ?DirectAction {
        return parent::getActionIfValid($actionName);
    }
}
