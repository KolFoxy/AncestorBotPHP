<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Ancestor\Interaction;

use Ancestor\CommandHandler\CommandHelper;
use Ancestor\Interaction\Stats\Stats;
use Ancestor\Interaction\Stats\StressState;
use Ancestor\Interaction\Stats\StressStateFactory;
use Ancestor\Interaction\Stats\Trinket;
use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class Hero extends AbstractLivingBeing {
    const DEAD_MESSAGE = ' DEAD';

    const HEART_ATTACK_MESSAGE = ' HEART ATTACK!';

    const MAX_STRESS = 199;

    const STRESS_ROLLBACK = 170;

    const AT_DEATH_S_DOOR_MESSAGE = ' AT DEATH\'S DOOR!';

    const TRANSFORM_ACTION = 'Transform';

    const TRANSFORM_PATH_SUFFIX = '_transform';

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $stress = 0;

    /**
     * @var HeroClass
     */
    public $type;

    /**
     * @var bool
     */
    private $isActuallyDead = false;

    /**
     * @var string
     */
    private $bonusStressMessage = '';

    /**
     * @var string
     */
    private $bonusHealthMessage = '';

    /**
     * @var Trinket[]
     */
    protected $trinkets = [
        1 => null,
        2 => null,
    ];

    /**
     * @var null|HeroClass
     */
    protected $transformType = null;

    /**
     * @var null|StressState
     */
    protected $stressState = null;

    protected function transform() {
        if ($this->transformType === null) {

        }
    }

    protected function setTransformType() {
        $suffixedType = $this->type->name . self::TRANSFORM_PATH_SUFFIX;
        $path = dirname(__DIR__, 2) . '/data/heroes/' . $suffixedType . '/' . $suffixedType . '.json';
        if (!file_exists($path)) {
            throw new \Exception('FILE NOT FOUND "' . $path . '" For transforming hero type ' . $this->type->name);
        }
        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $json = json_decode(file_get_contents($path));
        try {
            $this->transformType = $mapper->map($json, new HeroClass());
        } catch (\JsonMapper_Exception $e) {
            throw new \Exception($e->getMessage() . ' IN PATH="' . $path . '"');
        }
    }

    public function getTrinketStatus(): string {
        return '``Trinket slot`` **``1``**: ***``'
            . (is_null($this->getFirstTrinket()) ? '[EMPTY]``***' : $this->getFirstTrinket()->name . '``***')
            . '⚫``Trinket slot`` **``2``**: ***``'
            . (is_null($this->getSecondTrinket()) ? '[EMPTY]``***' : $this->getSecondTrinket()->name . '``***');
    }

    public function addStress(int $value) {
        if ($this->isActuallyDead) {
            return;
        }
        $this->stress += $value;
        if ($this->stress < 0) {
            $this->stress = 0;
            if ($this->stressState !== null && !$this->stressState->isVirtue) {
                $this->removeStressState();
            }
            return;
        }
        if ($this->stress > 100) {
            if ($this->stressState === null) {
                if ($this->addStressState()->isVirtue) {
                    $this->stress = 45;
                    return;
                }
            }
            if ($this->stress > self::MAX_STRESS) {
                if ($this->stressState !== null && $this->stressState->isVirtue) {
                    $this->stress = 0;
                    $this->bonusStressMessage = ' ' . $this->name . ' is no longer ``' . $this->stressState->name . '``';
                    $this->removeStressState();
                    return;
                }
                $this->bonusStressMessage = self::HEART_ATTACK_MESSAGE;
                if ($this->currentHealth === 0) {
                    $this->isActuallyDead = true;
                    $this->bonusHealthMessage = '';
                    return;
                }
                $this->currentHealth = 0;
                $this->stress = self::STRESS_ROLLBACK;
                $this->bonusHealthMessage = self::AT_DEATH_S_DOOR_MESSAGE;
                $this->bonusStressMessage .= ' ' . $this->getHealthStatus();
            }
        }
    }


    public function getStatsAndEffectsEmbed(): MessageEmbed {
        $res = new MessageEmbed();
        $res->setTitle('**' . $this->name . '**');
        $res->setDescription('*``' . $this->type->description . '``*'
            . PHP_EOL . '**``' . $this->getHealthStatus() . ' ' . $this->getStressStatus() . '``**'
            . PHP_EOL . $this->getTrinketStatus());
        $res->addField('**Stats:**', $this->statManager->getCurrentStatsString(), true);
        $res->setThumbnail($this->type->image);
        $effects = $this->statManager->getAllCurrentEffectsString();
        if ($effects !== '') {
            $res->addField('**Effects:**', $effects, true);
        }
        return $res;
    }

    /**
     * @return StressState
     */
    public function addStressState(): StressState {
        if ($this->stressState !== null) {
            return $this->stressState;
        }
        $this->stressState = StressStateFactory::create($this);
        $this->stressState->apply();
        return $this->stressState;
    }

    /**
     * @return StressState|null
     */
    public function getStressState() {
        return $this->stressState;
    }

    public function removeStressState() {
        if ($this->stressState === null) {
            return;
        }
        $this->stressState->remove();
        $this->stressState = null;
    }

    public function addHealth(int $value) {
        if ($this->isActuallyDead) {
            return;
        }
        $atDeathDoor = $this->currentHealth === 0;
        $this->currentHealth += $value;
        if ($atDeathDoor && $this->currentHealth < 0) {
            if (mt_rand(1, 100) > $this->statManager->getStatValue(Stats::DEATHBLOW_RESIST)) {
                $this->bonusHealthMessage = self::DEAD_MESSAGE;
                $this->bonusStressMessage = '';
                $this->isActuallyDead = true;
                return;
            }
        }
        if ($this->currentHealth > $this->healthMax) {
            $this->currentHealth = $this->healthMax;
            return;
        }
        if ($this->currentHealth <= 0) {
            $this->currentHealth = 0;
            $this->bonusHealthMessage = self::AT_DEATH_S_DOOR_MESSAGE;
            return;
        }
        $this->bonusHealthMessage = '';
    }

    public function getStressStatus(): string {
        return 'Stress: ' . $this->stress . '/100' . $this->getBonusMessage($this->bonusStressMessage);
    }

    public function getHealthStatus(): string {
        return parent::getHealthStatus() . $this->getBonusMessage($this->bonusHealthMessage);
    }

    /**
     * @return Trinket|null
     */
    public function getFirstTrinket() {
        return $this->trinkets[1];
    }

    /**
     * @return Trinket|null
     */
    public function getSecondTrinket() {
        return $this->trinkets[2];
    }

    public function getStatus(): string {
        return $this->getHealthStatus() . ' | ' . $this->getStressStatus();
    }


    public function __construct(HeroClass $class, string $name) {
        parent::__construct($class);
        $this->name = $name . ' the ' . $class->name;
    }

    public function isDead(): bool {
        $this->addHealth(0);
        $this->addStress(0);
        return $this->isActuallyDead;
    }

    public function hasTrinket(string $trinketName): bool {
        foreach ($this->trinkets as $trinket) {
            if ($trinket !== null && $trinket->name === $trinketName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Trinket $trinket
     * @param int $slot
     * @return string Result of trinket equipment.
     */
    public function tryEquipTrinket(Trinket $trinket, int $slot = 0): string {
        if ($trinket->classRestriction !== null && $trinket->classRestriction !== $this->type->name) {
            return 'Can\'t equip ' . $trinket->name . ' since ' . $this->name . ' is not a ' . $trinket->classRestriction;
        }
        if ($this->hasTrinket($trinket->name)) {
            return 'Can\'t have duplicate trinkets.';
        }
        $res = 'Equipped ' . $trinket->name . ' in slot ';
        if ($slot <= 1) {
            $slot = 1;
        } elseif ($slot > 1) {
            $slot = 2;
        }
        $res .= $slot;
        if ($this->trinkets[$slot] !== null) {
            $res .= ', replacing ' . $this->trinkets[$slot]->name;
            $this->trinkets[$slot]->remove();
            unset($this->trinkets[$slot]);
        }
        $res .= '.';
        $this->trinkets[$slot] = $trinket;
        $trinket->apply();
        return $res;
    }

    /**
     * @param string $commandName
     * @return \CharlotteDunois\Yasmin\Models\MessageEmbed
     */
    public function getEmbedResponse(string $commandName = null): MessageEmbed {
        return $this->type->getEmbedResponse($commandName, $this->getStatus());
    }

    private function getBonusMessage(string &$bonusString): string {
        if ($bonusString === '') {
            return '';
        }
        $res = $bonusString;
        $bonusString = '';
        return $res;
    }

    /**
     * @param DirectAction $action
     * @param AbstractLivingBeing|Hero $target
     * @return MessageEmbed
     */
    public function getHeroTurn(DirectAction $action, AbstractLivingBeing $target): MessageEmbed {
        $res = new MessageEmbed();
        if ($action === $this->type->defaultAction()) {
            $target = $this;
        }
        if (!$this->statManager->isStunned()) {
            $res->setThumbnail($action->effect->image);
        }
        $heroStressStateChecker = is_a($target, Hero::class) && is_null($target->getStressState());
        $fields = $this->getTurn($target, $action);
        if ($heroStressStateChecker && !is_null($target->getStressState())) {
            $fields[] = $target->getStressState()->toField();
        }

        if ($target->isDead() && !is_a($target, self::class)) {
            if ((bool)mt_rand(0, 1)) {
                $this->addStress(parent::DEFAULT_STRESS_SELF_HEAL);
                $fields[] = CommandHelper::getEmbedField('The act of killing inspires the hero! ' . parent::DEFAULT_STRESS_SELF_HEAL . ' stress!',
                    $this->getStressStatus());
            }
        }
        $topField = array_shift($fields);
        $res->setTitle($topField['name']);
        $res->setDescription($topField['value']);
        CommandHelper::mergeEmbed($res, $fields);
        return $res;
    }

    public function getDeathQuote(): string {
        return RandomDataProvider::GetInstance()->GetRandomHeroDeathQuote();
    }

    public function debuffIsPermanent(string $debuffKey): bool {
        foreach ($this->trinkets as $trinket) {
            if ($trinket !== null) {
                if ($trinket->keyIsInModifiers($debuffKey)) {
                    return true;
                }
            }
        }
        if ($this->stressState !== null && $this->stressState->keyIsInModifiers($debuffKey)) {
            return true;
        }
        return false;
    }

    public function kill() {
        $this->currentHealth = -1;
        $this->isActuallyDead = true;
    }

}