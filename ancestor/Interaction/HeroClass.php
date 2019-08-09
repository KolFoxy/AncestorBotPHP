<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\SpontaneousAction\SpontaneousAction;
use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class HeroClass extends AbstractLivingInteraction {

    /**
     * @var DirectAction|null
     */
    private $defAction = null;

    const EMBED_COLOR = 13294;

    /**
     * Color of the embedResponse
     * @var integer
     */
    public $embedColor = self::EMBED_COLOR;

    /**
     * @var null|HeroClass
     */
    protected $transformClass = null;

    /**
     * @var null|\Ancestor\Interaction\SpontaneousAction\SpontaneousAction[]
     */
    public $spontaneousActions = null;

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
     * @param string $commandName
     * @param string|null $status
     * @return MessageEmbed
     */
    public function getEmbedResponse(string $commandName = null, string $status = null): MessageEmbed {
        /** @noinspection PhpUnhandledExceptionInspection */
        $embedResponse = new MessageEmbed();
        $embedResponse->setThumbnail($this->image);
        $embedResponse->setTitle('Your class is **' . $this->name . '**');
        if ($this->embedColor === null) {
            $embedResponse->setColor(self::EMBED_COLOR);
        } else {
            $embedResponse->setColor($this->embedColor);
        }
        $embedResponse->setDescription('*' . $this->description . '*');
        if ($status != null) {
            $footerText = $status;
            $embedResponse->setFooter($footerText);
        }
        return $embedResponse;
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
     * @return Action|DirectAction|null
     */
    public function getActionIfValid(string $actionName) {
        return parent::getActionIfValid($actionName);
    }
}
