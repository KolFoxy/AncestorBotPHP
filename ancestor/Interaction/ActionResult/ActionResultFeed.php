<?php

namespace Ancestor\Interaction\ActionResult;

class ActionResultFeed {
    /**
     * @var string[]
     */
    public $stress = [];

    /**
     * @var string[]
     */
    public $health = [];

    /**
     * @var string[]
     */
    public $newEffects = [];

    /**
     * @var string[]
     */
    public $removedEffects = [];

    /**
     * @var string[]
     */
    public $resisted = [];

    public function isEmpty(): bool {
        return $this->stress === []
            && $this->health === []
            && $this->newEffects === []
            && $this->removedEffects === []
            && $this->resisted === [];
    }
}