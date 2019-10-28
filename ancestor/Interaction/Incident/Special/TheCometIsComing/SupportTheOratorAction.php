<?php

namespace Ancestor\Interaction\Incident\Special\TheCometIsComing;

use Ancestor\Interaction\Effect;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\Incident\IActionSingletonInterface;
use Ancestor\Interaction\Incident\Incident;
use Ancestor\Interaction\Incident\IncidentAction;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class SupportTheOratorAction extends IncidentAction implements IActionSingletonInterface {

    /**
     * @var IncidentAction
     */
    protected $alternativeAction;
    /**
     * @var IncidentAction
     */
    protected static $instance = null;

    public static function getInstance(): IncidentAction {
        if (self::$instance === null) {
            self::$instance = new SupportTheOratorAction();
        }
        return self::$instance;
    }


    public function __construct() {
        $this->alternativeAction = new IncidentAction();
        $this->alternativeAction->name = $this->name = 'Support the orator';

        $this->effect = new Effect();
        $this->alternativeAction->effect = new Effect();

        $description = 'You know that the speaker’s words are true, but he completely fails to influence the public. Maybe if you help him, it’ll save a couple of innocent lives from the inevitability of cosmic madness?'
            . PHP_EOL . 'You hastily get up on the stage: a move that gets completely ignored by the man. No matter, you shout to the crowd your solidarity with the hapless prophet. With all the emotion and good will you have, you try your best to convince people to leave this doomed place…'
            . PHP_EOL;

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->effect->setDescription(
            $description
            . 'But these people are hardly influenceable at all. All they want from this is entertainment, something to kill their time with– not knowing that they really don’t have much of it to kill.'
            . PHP_EOL . 'Your efforts are fruitless. It is better not to waste any more time and energy on this and leave immediately.');
        $this->effect->stress_value = 8;
        $this->effect->stressDeviation = 7;

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->alternativeAction->effect->setDescription(
            $description
            . 'And they listen. You can see through their eyes that the horror and inevitability are slowly growing inside of them. The prophet assists you and soon, one by one, people are finally starting to break and ask questions. When will it come? What will happen to their houses, fields, stock? You know the answers, and you are not afraid to hit them with the truth.'
            . PHP_EOL . 'Hopefully, your efforts will save some lives. Though, most likely it will have no impact at all... But that thought is easy to ignore for now.');
        $this->alternativeAction->effect->stress_value = -8;
        $this->alternativeAction->effect->stressDeviation = -7;
    }

    public function getResult(Hero $hero, MessageEmbed $res): ?Incident {
        if (mt_rand(1, 100) <= 50) {
            return $this->alternativeAction->getResult($hero, $res);
        }
        return parent::getResult($hero, $res);
    }


}