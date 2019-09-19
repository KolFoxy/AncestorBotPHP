<?php

namespace Ancestor\Interaction\Incident\IncidentCollection;

use Ancestor\Interaction\Incident\Incident;

class IncidentCollection implements IncidentCollectionInterface {

    protected static $instance = null;

    /**
     * @var Incident[]
     */
    private $incidents;
    /**
     * @var int
     */
    private $incidentsMaxIndex;

    public static function getInstance() : IncidentCollection {
        if (self::$instance === null){
            self::$instance = new IncidentCollection();
        }
        return self::$instance;
    }

    protected function __construct() {
        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $mapper->bExceptionOnUndefinedProperty = true;
        foreach (glob(dirname(__DIR__, 4) . '/data/incidents/*.json') as $path) {
            $json = json_decode(file_get_contents($path));
            try {
                $this->incidents[] = $mapper->map($json, new Incident());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage() . ' IN PATH="' . $path . '"' . $e->getTraceAsString());
            }
        }
        foreach (glob(dirname(__DIR__, 4) . '/data/incidents/*.php') as $path) {
            try {
                $this->incidents[] = require($path);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage() . ' IN PATH="' . $path . '"' . $e->getTraceAsString());
            }
        }
        $this->incidentsMaxIndex = count($this->incidents) - 1;
    }

    public function randIncident(): Incident {
        return $this->incidents[mt_rand(0, $this->incidentsMaxIndex)];
    }
}