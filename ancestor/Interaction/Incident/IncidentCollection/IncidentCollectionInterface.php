<?php

namespace Ancestor\Interaction\Incident\IncidentCollection;

use Ancestor\Interaction\Incident\Incident;

interface IncidentCollectionInterface {
    /**
     * @return Incident
     */
    public function randIncident(): Incident;
}