<?php

namespace Ancestor\Interaction\Stats;

class StatusEffect {

    const TYPE_BLEED = "bleed";
    const TYPE_BLIGHT = "bleed";
    const TYPE_HORROR = "horror";
    const TYPE_STUN = "stun";
    const TYPE_RESTORATION = "restoration";

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     * @required
     */
    public $duration;

    /**
     * @var int
     */
    public $chance = 100;

    /**
     * @var int
     */
    public $value;

    /**
     * @param string $type
     * @throws \Exception
     */
    public function setType(string $type) {
        if ($type === self::TYPE_BLEED
            || $type === self::TYPE_BLIGHT
            || $type === self::TYPE_STUN
            || $type === self::TYPE_HORROR
            || $type === self::TYPE_RESTORATION) {
            $this->type = $type;
            return;
        }
        throw new \Exception('Invalid StatusEffect type');
    }


    /**
     * @return string
     */
    public function getType() : string {
        return $this->type;
    }


}