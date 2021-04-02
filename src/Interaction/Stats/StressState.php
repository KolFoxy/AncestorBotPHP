<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\Hero;

class StressState extends AbstractPermanentState {

    /**
     * @var string
     * @required
     */
    public string $quote;

    /**
     * @var bool
     */
    public bool $isVirtue = false;

    public function toField(Hero $host): array {
        return [
            'title' => '**' . $host->name . '\'s resolve is tested...** ***' . $this->name . '***',
            'body' => '***' . $this->getQuote() . '***' . PHP_EOL . $this->getStatModsString(),
            'inline' => false,
        ];
    }

    public function getStatModsString(): string {
        $statMods = '';
        foreach ($this->statModifiers as $statModifier) {
            $statMods .= '*``' . $statModifier->__toString() . '``*' . PHP_EOL;
        }
        return $statMods;
    }

    public function getQuote(): string {
        return $this->isVirtue || (mt_rand(1, 100) <= 50) ? $this->quote : StressStateFactory::getRandomAfflictionQuote();
    }


}