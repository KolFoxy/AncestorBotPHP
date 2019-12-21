<?php

namespace Ancestor\Interaction\Incident\Special\CrookedStranger;

use Ancestor\Interaction\Effect;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\Incident\Incident;
use Ancestor\Interaction\Incident\IncidentAction;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class OfferTrinketAction extends IncidentAction {

    /**
     * @var int
     */
    protected $trinketSlot;

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * @param bool $offerSecondTrinket Uses first trinket slot by default.
     */
    public function __construct(bool $offerSecondTrinket = false) {
        $this->trinketSlot = $offerSecondTrinket ? 2 : 1;
        $this->name = $this->trinketSlot === 1 ? 'First' : 'Second';
        $this->effect = new Effect();
        $this->effect->setDescription('With a nod of its head, the figure takes what is offered. Hands lie on your form, cleaning the skin with something that smells like ethanol. Then they apply bandages and stitch the wounds with their pointy fingers. A true professional: works quick, with passion. You feel yourself renewed as the figure takes its leave, waving a hand.');
        $this->effect->health_value = 15;
        $this->effect->healthDeviation = 20;
    }

    public function getResult(Hero $hero, MessageEmbed $res): ?Incident {
        if ($this->trinketSlot === 1) {
            $hasTrinket = $hero->getFirstTrinket() !== null;
        } else {
            $hasTrinket = $hero->getSecondTrinket() !== null;
        }
        if ($hasTrinket) {
            parent::getResult($hero, $res);
        } else {
            $res->setDescription('*``"No payment, no services", says the figure and leaves.``*');
        }
        $res->setTitle('*Offer a trinket*');
        return null;
    }

    protected function applyEffectsGetResults(Hero $hero): string {
        $trinketName = $this->trinketSlot === 1 ? $hero->getFirstTrinket()->name : $hero->getSecondTrinket()->name;
        $hero->removeTrinketFromSlot($this->trinketSlot);
        return parent::applyEffectsGetResults($hero) . PHP_EOL . '``Lost trinket:`` **``' . $trinketName . '``**';
    }
}