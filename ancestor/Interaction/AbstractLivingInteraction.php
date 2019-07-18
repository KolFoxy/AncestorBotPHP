<?php

namespace Ancestor\Interaction;

abstract class AbstractLivingInteraction extends AbstractInteraction {

    /**
     * @var string[]
     */
    public $types = [''];

    /**
     * @var int
     */
    public $healthMax = 35;

    /**
     * @var DirectAction[]
     */
    public $actions;

    /**
     * @var array
     */
    public $stats = [];

    /**
     * @var DirectAction|null
     */
    public $riposteAction = null;

}