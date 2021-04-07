<?php

namespace Ancestor\Interaction\Incident;

interface IActionSingletonInterface {
    public static function getInstance(): IncidentAction;
}