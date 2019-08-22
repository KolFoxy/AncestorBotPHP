<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace Ancestor\Interaction\Fight;

use Ancestor\CommandHandler\CommandHelper as Helper;
use Ancestor\FileDownloader\FileDownloader;
use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\ImageTemplate\ImageTemplateApplier;
use Ancestor\Interaction\AbstractLivingBeing;
use Ancestor\Interaction\DirectAction;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\Monster;
use Ancestor\Interaction\Stats\Stats;
use Ancestor\Interaction\Stats\Trinket;
use Ancestor\Interaction\Stats\TrinketFactory;
use Ancestor\Zalgo\Zalgo;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\ExtendedPromiseInterface;

class FightManager {

    /**
     * @var Hero
     */
    public $hero;

    /**
     * @var AbstractLivingBeing
     */
    public $monster;

    /**
     * @var int
     */
    public $killCount = 0;

    /**
     * @var EncounterCollectionInterface
     */
    public $encounterCollection;

    /**
     * @var bool
     */
    public $endless;

    /**
     * @var string
     */
    public $chatCommand;

    /**
     * @var Trinket|null
     */
    public $newTrinket = null;

    /**
     * @var string[]
     */
    public $killedMonsters = [];

    /**
     * @var int
     */
    protected $transformTimer = self::TRANSFORM_TURNS_CD;

    const ENDSCREEN_PATH = '/data/images/endscreen/';
    const FONT_PATH = '/data/the_font/DwarvenAxeDynamic.ttf';
    const FONT_SIZE = 48;
    const SMALL_FONT_SIZE = 24;
    const KILLCOUNT_X = 13;
    const KILLCOUNT_Y = 195;
    const KILLS_NUMBER_X = 128;
    const TITLE_X = 65;
    const TITLE_Y = 220;
    const CORPSES_PATH = '/data/images/corpses/';
    const DEFAULT_CORPSE_PATH = '/data/images/corpses/default.png';
    const CORPSE_Y_POSITIONS = [
        0 => [
            'max' => 410,
            'min' => 393,
        ],
        1 => [
            'max' => 343,
            'min' => 326,
        ],
        2 => [
            'max' => 298,
            'min' => 281,
        ],
        3 => [
            'max' => 260,
            'min' => 243,
        ],
    ];
    const CORPSES_PER_LAYER = 8;
    const MAX_LAYER_INDEX = 3;
    const CORPSE_HEIGHT = 100;
    const CORPSE_WIDTH = 159;
    const CORPSE_MAX_X = 168;
    const CORPSE_MIN_X = -70;
    /**
     * array [int killThreshold => string Title]
     */
    const FINAL_TITLES = [
        100 => 'LEGEND',
        55 => 'CHAMPION',
        40 => 'VETERAN',
        25 => 'APPRENTICE',
        0 => 'RECRUIT',
    ];
    const ENDSCREEN_THRESHOLD = 15;

    const TRINKET_KILLS_THRESHOLD = 2;

    const SKIP_TRINKET_ACTION = -13505622;

    const SKIP_HEAL_PERCENTAGE = 0.1;
    const TRANSFORM_TURNS_CD = 4;
    const CORRUPTED_HERO_THRESHOLD = 10;
    const CORRUPTED_HERO_CHANCE = 25;

    const ELITE_MONSTER_THRESHOLD = 15;
    const ELITE_MONSTER_CHANCE = 25;
    const CORRUPTED_NAME_MAXLENGTH = 6;
    const CORRUPTED_NAME_MINLENGTH = 3;
    const CORRUPTED_NAME_ZALGOCHARS = 4;

    const UTF8_ALPHABET_START = 65;

    const UTF8_ALPHABET_END = 90;

    const CORRUPTED_DEATHBLOW_RESIST = 30;

    public function __construct(Hero $hero, EncounterCollectionInterface $monsterCollection, string $chatCommand, bool $endless = false) {
        $this->hero = $hero;
        $this->encounterCollection = $monsterCollection;
        $this->endless = $endless;
        $this->chatCommand = $chatCommand;
    }

    public function start(): MessageEmbed {
        if (!isset($this->monster)) {
            $this->monster = $this->rollNewMonster();
        }
        $embed = $this->newColoredEmbed();
        $embed->setTitle('**' . $this->hero->name . '**');
        $embed->setThumbnail($this->hero->type->image);
        $embed->setDescription('*``' . $this->hero->type->description . '``*' . PHP_EOL . '``' . $this->hero->getStatus() . '``');
        $embed->addField(
            'You encounter a vile **' . $this->monster->name . '**',
            '*``' . $this->monster->type->description . '``*' . PHP_EOL . '*``' . $this->monster->getHealthStatus() . '``*'
            . PHP_EOL . $this->monster->statManager->getAllCurrentEffectsString()
        );
        $embed->setImage($this->monster->type->image);
        if ((bool)mt_rand(0, 1)) {
            $additionalEmbed = $this->monster->getTurn($this->hero, $this->monster->getProgrammableAction());
            Helper::mergeEmbed($embed, $additionalEmbed);
        }
        $embed->setFooter($this->getCurrentFooter());
        return $embed;
    }


    protected function getCurrentFooter(): string {
        if ($this->newTrinket !== null) {
            return 'Respond with "' . $this->chatCommand . ' [NUMBER]" to equip trinket in the corresponding slot.' . PHP_EOL . 'Alternatively, "'
                . $this->chatCommand . ' skip" will disregard the trinket. Skipping the trinket will provide you with time to quickly patch up and restore some HP.';
        }
        return $this->hero->type->getDefaultFooterText($this->chatCommand, $this->monster->isStealthed(), $this->noTransform())
            . PHP_EOL . ($this->killCount > 0 ? 'Kills: ' . $this->killCount : '');
    }

    protected function resetTransformTimer() {
        $this->transformTimer = 0;
    }

    protected function transformTimerTick() {
        if ($this->transformTimer < self::TRANSFORM_TURNS_CD) {
            $this->transformTimer++;
        }
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @param DirectAction|int $action
     * @param string $heroPicUrl
     * @return MessageEmbed
     */
    public function getTurn($action, string $heroPicUrl): MessageEmbed {
        if (is_int($action)) {
            if ($this->newTrinket !== null) {
                return $this->getEquipTrinketTurn($action);
            }
            $this->hero->kill();
            return (new MessageEmbed())->setTitle('***FATAL ERROR!***')->setDescription('Invalid action. Terminating session.');
        }
        return $this->getHeroTurn($action, $heroPicUrl);
    }

    /**
     * @param DirectAction|int $action
     * @param string $heroPicUrl
     * @param LoopInterface $loop
     * @return ExtendedPromiseInterface callback($messageData), canceller = fight is over
     */
    public function createTurnPromise($action, string $heroPicUrl, LoopInterface $loop): ExtendedPromiseInterface {
        $deferred = new Deferred();
        $turn = $this->getTurn($action, $heroPicUrl);
        if ($this->isOver()) {
            if ($this->killCount < self::ENDSCREEN_THRESHOLD) {
                $deferred->reject(['embed' => $turn]);
            } else {
                $this->createEndscreen($heroPicUrl, $loop)->done(
                    function ($data) use ($deferred, $turn) {
                        $deferred->reject(['embed' => $turn, 'files' => [['data' => $data, 'name' => 'end.png']]]);
                    },
                    function () use ($deferred, $turn) {
                        $deferred->reject(['embed' => $turn]);
                    }
                );
            }
        } else {
            $deferred->resolve(['embed' => $turn]);
        }
        return $deferred->promise();
    }

    public function createEndscreen(string $heroPicUrl, LoopInterface $loop): ExtendedPromiseInterface {
        $fdl = new FileDownloader($loop);
        return $fdl->getDownloadAsyncImagePromise($heroPicUrl)->then(
            function ($imageFile) {
                $endPath = dirname(__DIR__, 3) . self::ENDSCREEN_PATH
                    . mb_strtolower(str_replace(' ', '_', $this->hero->type->name));
                $mapper = new \JsonMapper();
                $mapper->bExceptionOnMissingData = true;
                $template = new ImageTemplate();
                $json = json_decode(file_get_contents($endPath . '.json'));
                $mapper->map($json, $template);

                $applier = new ImageTemplateApplier($template);
                $canvas = imagecreatefrompng($endPath . '.png');
                $applier->slapTemplate($imageFile, $canvas, true);
                $this->addKillCountToImage($canvas);
                $this->addCorpsesToImage($canvas);

                ob_start();
                imagepng($canvas);
                $result = ob_get_clean();
                imagedestroy($canvas);

                return $result;
            }
        );
    }

    protected function addKillCountToImage($image) {
        $cyan = imagecolorallocate($image, 0, 255, 255);
        $red = imagecolorallocate($image, 255, 0, 0);
        $ttfPath = dirname(__DIR__, 3) . self::FONT_PATH;
        $numSize = $this->killCount >= 10000 ? self::SMALL_FONT_SIZE : self::FONT_SIZE;
        imagettftext($image, self::FONT_SIZE, 0, self::KILLCOUNT_X, self::KILLCOUNT_Y, $cyan, $ttfPath, 'Kills:');
        imagettftext($image, $numSize, 0, self::KILLS_NUMBER_X, self::KILLCOUNT_Y, $red, $ttfPath, (string)$this->killCount);
        $title = '';
        foreach (self::FINAL_TITLES as $threshold => $item) {
            if ($this->killCount >= $threshold) {
                $title = $item;
                break;
            }
        }
        imagettftext($image, self::SMALL_FONT_SIZE, 0, self::TITLE_X, self::TITLE_Y, $cyan, $ttfPath, $title);
    }

    protected function addCorpsesToImage($image) {
        $distributions = $this->getCorpsesDistributionArray();
        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $applier = new ImageTemplateApplier($this->getDefaultCorpseTemplate());
        foreach ($distributions as $layer) {
            foreach ($layer as $name => $positions) {
                $path = dirname(__DIR__, 3) . self::CORPSES_PATH
                    . str_replace(' ', '_', mb_strtolower($name)) . '.png';
                if (!file_exists($path)) {
                    $path = dirname(__DIR__, 3) . self::DEFAULT_CORPSE_PATH;
                }
                $corpseImage = imagecreatefrompng($path);
                if ($corpseImage === false) {
                    echo 'INVALID PATH "' . $path . '"' . PHP_EOL;
                    continue;
                }
                foreach ($positions as $position) {
                    $applier->imgTemplate->imgPositionX = $position[0];
                    $applier->imgTemplate->imgPositionY = $position[1];
                    $applier->slapTemplate($corpseImage, $image);
                }
                imagedestroy($corpseImage);
            }
        }

    }

    protected function getDefaultCorpseTemplate(): ImageTemplate {
        $res = new ImageTemplate();
        $res->imgH = self::CORPSE_HEIGHT;
        $res->imgW = self::CORPSE_WIDTH;
        return $res;
    }

    protected function getCorpsesDistributionArray(): array {
        $res = [];
        for ($i = self::MAX_LAYER_INDEX; $i >= 0; $i--) {
            $res[$i] = [];
        }
        $killed = count($this->killedMonsters);
        for ($i = 0; $i < $killed; $i++) {
            $layer = (int)($i / self::CORPSES_PER_LAYER);
            if ($layer > self::MAX_LAYER_INDEX) {
                $layer = mt_rand(0, self::MAX_LAYER_INDEX);
            }
            $position = [
                mt_rand(self::CORPSE_MIN_X, self::CORPSE_MAX_X),
                mt_rand(self::CORPSE_Y_POSITIONS[$layer]['min'], self::CORPSE_Y_POSITIONS[$layer]['max']),
            ];
            if (isset($res[$layer][$this->killedMonsters[$i]])) {
                $res[$layer][$this->killedMonsters[$i]][] = $position;
            } else {
                $res[$layer][$this->killedMonsters[$i]] = [$position];
            }
        }
        return $res;
    }

    protected function getEquipTrinketTurn(int $action): MessageEmbed {
        $res = $this->newColoredEmbed();
        if ($action === self::SKIP_TRINKET_ACTION) {
            $heal = mt_rand(1, (int)($this->hero->healthMax) * self::SKIP_HEAL_PERCENTAGE * $this->newTrinket->rarity);
            $heal = $this->hero->heal($heal);
            $res->setTitle('**' . $this->hero->name . '** used their time to heal for **' . $heal . 'HP**');
            $res->setDescription($this->hero->getHealthStatus());
        } else {
            $res->setTitle($this->hero->tryEquipTrinket($this->newTrinket, $action));
            $res->setDescription($this->hero->getTrinketStatus());
        }
        $this->newTrinket = null;
        $this->newMonsterTurn($res);
        $res->setFooter($this->getCurrentFooter());
        return $res;
    }

    protected function noTransform(): bool {
        if ($this->transformTimer < self::TRANSFORM_TURNS_CD) {
            return true;
        }
        return false;
    }

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * @param DirectAction $action
     * @param string $heroPicUrl
     * @return MessageEmbed
     */
    protected function getHeroTurn(DirectAction $action, string $heroPicUrl): MessageEmbed {
        $isTransformAction = $action->isTransformAction();
        if ($isTransformAction) {
            if ($this->noTransform()) {
                return $this->transformCdEmbed();
            }
            $this->resetTransformTimer();
        }
        $embed = $this->hero->getHeroTurn($action, $this->monster);
        if (!$this->hero->isDead() && !$isTransformAction) {
            $this->transformTimerTick();
            if ($this->monsterTurnIsFinal($embed)) {
                if ($this->endless === false) {
                    $embed->setFooter($this->hero->name . ' is victorious!', $heroPicUrl);
                }
                return $embed;
            }
        }
        if ($this->hero->isDead()) {
            $embed->setFooter('R.I.P. ' . $this->hero->name, $heroPicUrl);
            return $embed;
        }
        $embed->setFooter($this->getCurrentFooter());
        return $embed;
    }

    protected function monsterTurnIsFinal(MessageEmbed $embed): bool {
        if (!$this->monster->isDead()) {
            $embed->addField($this->monster->type->name . '\'s turn!', '*``' . $this->monster->getHealthStatus() . '``*');
            Helper::mergeEmbed($embed, $this->getMonsterTurnFields());
        }
        if ($this->monster->isDead()) {
            if ($this->endless) {
                $this->killCount++;
                $this->killedMonsters[] = $this->monster->type->name;
                if ($this->rollTrinkets($embed)) {
                    return true;
                }
                return $this->newMonsterTurn($embed);
            } else {
                return true;
            }
        }
        return false;
    }

    protected function transformCdEmbed(): MessageEmbed {
        $embed = $this->newColoredEmbed();
        $embed->setTitle('Can\'t transform yet.');
        $embed->setDescription('Cooldown: ' . (self::TRANSFORM_TURNS_CD - $this->transformTimer) . ' turns.');
        return $embed;
    }

    /**
     * @return array
     */
    protected function getMonsterTurnFields(): array {
        if ($this->hero->isStealthed()) {
            return $this->monster->getTurn($this->hero, $this->monster->type->getActionVsStealthed());
        }
        return $this->monster->getTurn($this->hero);
    }

    /**
     * @param MessageEmbed $resultEmbed
     * @return bool Whether or not the first turn of the new monster is final. AKA if Monsters dies on his first turn.
     */
    public function newMonsterTurn(MessageEmbed $resultEmbed): bool {
        $this->monster = $this->rollNewMonster();
        $resultEmbed->addField('***' . $this->monster->name . ' emerges from the darkness!***'
            , '*``' . $this->monster->type->description . '``*'
            . PHP_EOL . '*``' . $this->monster->getHealthStatus() . '``*'
            . PHP_EOL . $this->monster->statManager->getAllCurrentEffectsString()
        );
        $resultEmbed->setImage($this->monster->type->image);
        if ((bool)mt_rand(0, 1)) {
            return $this->monsterTurnIsFinal($resultEmbed);
        }
        return false;
    }

    /**
     * @return Hero|Monster
     */
    protected function rollNewMonster() {
        if ($this->killCount >= self::CORRUPTED_HERO_THRESHOLD && ((mt_rand(1, 100) <= self::CORRUPTED_HERO_CHANCE))) {
            return $this->rolLNewCorruptedHero();
        }
        if ($this->killCount >= self::ELITE_MONSTER_THRESHOLD && ((mt_rand(1, 100)) <= self::ELITE_MONSTER_CHANCE)) {
            return new Monster($this->encounterCollection->randEliteMonsterType());
        }
        return new Monster($this->encounterCollection->randRegularMonsterType());
    }

    protected function rolLNewCorruptedHero(): Hero {
        $corruptedHero = new Hero($this->encounterCollection->randHeroClass(), $this->generateCorruptedName());
        $corruptedHero->statManager->setStat(Stats::DEATHBLOW_RESIST, self::CORRUPTED_DEATHBLOW_RESIST);
        return $corruptedHero;
    }

    protected function generateCorruptedName() {
        $res = '';
        $len = mt_rand(self::CORRUPTED_NAME_MINLENGTH, self::CORRUPTED_NAME_MAXLENGTH);
        for ($i = 0; $i < $len; $i++) {
            $res .= mb_chr(mt_rand(self::UTF8_ALPHABET_START, self::UTF8_ALPHABET_END), 'UTF-8');
        }
        return Zalgo::zalgorizeString($res, self::CORRUPTED_NAME_ZALGOCHARS);
    }

    public function getHeroStats(): MessageEmbed {
        return $this->hero->getStatsAndEffectsEmbed()->setFooter($this->getCurrentFooter());
    }

    public function getHeroActionsDescriptions(): MessageEmbed {
        $res = $this->newColoredEmbed();
        $res->setTitle($this->hero->name . '\'s abilities and actions:');
        $description = '';
        foreach ($this->hero->type->actions as $action) {
            $description .= '***' . $action->name . '***' . PHP_EOL . '``' . $action->__toString() . '``' . PHP_EOL;
        }
        $description .= '*' . $this->hero->type->defaultAction()->name . '*'
            . PHP_EOL . '``' . $this->hero->type->defaultAction()->effect->getDescription() . '``';
        $res->setDescription($description);
        $res->setFooter($this->getCurrentFooter());
        return $res;
    }

    protected function rollTrinkets(MessageEmbed $resultEmbed): bool {
        if ($this->killCount < self::TRINKET_KILLS_THRESHOLD) {
            return false;
        }
        $newTrinket = TrinketFactory::create($this->hero);
        $this->newTrinket = $newTrinket;
        $resultEmbed->setImage($newTrinket->image);
        $trinketTitle = $newTrinket->name;
        for ($i = 0; $i < $this->newTrinket->rarity; $i++) {
            $trinketTitle .= '☆';
        }
        $resultEmbed->addField('You\'ve found a new trinket: ***' . $trinketTitle . '***',
            '``' . $newTrinket->getDescription() . '``'
            . PHP_EOL . $this->hero->getTrinketStatus());
        $resultEmbed->setFooter($this->getCurrentFooter());
        return true;
    }

    public function isOver(): bool {
        return $this->hero->isDead() || ($this->monster->isDead() && !$this->endless);
    }

    /**
     * @param string $actionName
     * @return DirectAction|int|null
     */
    public function getActionIfValid(string $actionName) {
        if (!is_null($this->newTrinket)) {
            if ($actionName === 'skip') {
                return self::SKIP_TRINKET_ACTION;
            }
            if (is_numeric($actionName)) {
                return (int)$actionName;
            }
            return null;
        }
        if ($this->noTransform() && $actionName === DirectAction::TRANSFORM_ACTION) {
            return null;
        }
        $action = $this->hero->type->getActionIfValid($actionName);
        return is_null($action) || (!$action->isUsableVsStealth() && $this->monster->statManager->isStealthed()) ? null : $action;
    }

    protected function newColoredEmbed(): MessageEmbed {
        return (new MessageEmbed())->setColor($this->hero->type->embedColor);
    }
}
