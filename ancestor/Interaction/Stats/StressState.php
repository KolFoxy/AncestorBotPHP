<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\Hero;

class StressState extends AbstractPermanentState {

    /**
     * @var string
     */
    public $quote;

    public $isVirtue = false;

    public function __construct(Hero $host, string $name, string $quote) {
        $this->host = $host;
        $this->name = $name;
        $this->quote = $quote;
    }

    public function toField(): array {
        return [
            'name' => '**' . $this->host->name . '\'s resolve is tested...** ***' . $this->name . '***',
            'value' => '***' . $this->quote . '***' . PHP_EOL . $this->getStatModsString(),
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


}