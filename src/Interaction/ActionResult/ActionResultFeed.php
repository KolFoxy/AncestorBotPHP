<?php

namespace Ancestor\Interaction\ActionResult;

class ActionResultFeed {
    /**
     * @var string[]
     */
    public array $stress = [];

    /**
     * @var string[]
     */
    public array $health = [];

    /**
     * @var string[]
     */
    public array $newEffects = [];

    /**
     * @var string[]
     */
    public array $removedEffects = [];

    /**
     * @var string[]
     */
    public array $resisted = [];

    public function isEmpty(): bool {
        return $this->stress === []
            && $this->health === []
            && $this->newEffects === []
            && $this->removedEffects === []
            && $this->resisted === [];
    }
}