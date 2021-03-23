<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\AbstractLivingBeing;

class AbstractTypedPermanentState extends AbstractPermanentState {

    /**
     * @var TypeBonus[]
     */
    protected $typeBonuses = [];


    public function apply(AbstractLivingBeing $host) {
        parent::apply($host);
        foreach ($this->typeBonuses as $key => $bonus) {
            $host->statManager->typeBonuses[$key] = $bonus;
        }
    }

    public function remove(AbstractLivingBeing $host) {
        parent::remove($host);
        foreach ($this->typeBonuses as $key => $bonus) {
            unset($host->statManager->typeBonuses[$key]);
        }
    }

    /**
     * @param TypeBonus[] $typeBonuses
     */
    public function setTypeBonuses($typeBonuses) {
        foreach ($typeBonuses as $key => $bonus) {
            $this->typeBonuses[$this->name . $key . $bonus->type] = $bonus;
        }
    }

    /**
     * @return TypeBonus[]
     */
    public function getTypeBonuses() {
        return $this->typeBonuses;
    }
}