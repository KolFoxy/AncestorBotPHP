<?php

namespace Ancestor\Interaction\Incident;

interface IncidentSingletonInterface {
    public static function getInstance(): Incident;
}