<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace Ancestor\Interaction\Fight;

use Ancestor\BotIO\EmbedInterface;
use Ancestor\BotIO\EmbedObject;
use Ancestor\Command\CommandHelper as Helper;
use Ancestor\FileDownloader\FileDownloader;
use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\ImageTemplate\ImageTemplateApplier;
use Ancestor\Interaction\AbstractLivingBeing;
use Ancestor\Interaction\DirectAction;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\Incident\Incident;
use Ancestor\Interaction\Incident\IncidentAction;
use Ancestor\Interaction\Monster;
use Ancestor\Interaction\Stats\LightingEffect;
use Ancestor\Interaction\Stats\LightingEffectFactory;
use Ancestor\Interaction\Stats\Stats;
use Ancestor\Interaction\Stats\Trinket;
use Ancestor\Interaction\Stats\TrinketFactory;
use Ancestor\Zalgo\Zalgo;
use JsonMapper;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\ExtendedPromiseInterface;

class FightManager {

    /**
     * @var Hero
     */
    public Hero $hero;

    /**
     * @var AbstractLivingBeing
     */
    public AbstractLivingBeing $monster;

    /**
     * @var LoopInterface
     */
    public LoopInterface $loop;

    /**
     * @var int
     */
    public int $killCount = 0;

    /**
     * @var EncounterCollectionInterface
     */
    public EncounterCollectionInterface $encounterCollection;

    /**
     * @var bool
     */
    public bool $endless;

    /**
     * @var string
     */
    public string $chatCommand;

    /**
     * @var Trinket|null
     */
    public ?Trinket $newTrinket = null;

    /**
     * @var Incident|null
     */
    public ?Incident $incident = null;

    /**
     * @var string[]
     */
    public array $killedMonsters = [];

    /**
     * @var int
     */
    protected int $transformTimer = self::TRANSFORM_TURNS_CD;

    /**
     * @var ImageTemplate
     */
    protected ImageTemplate $loserTombstoneImageTemplate;

    /**
     * @var string
     */
    public string $heroPicUrl;

    /**
     * @var LightingEffect|null
     */
    public ?LightingEffect $currentLight = null;

    const ENDSCREEN_PATH = '/data/images/endscreen/';
    const ENDSCREEN_WIDTH = 246;
    const ENDSCREEN_HEIGHT = 500;
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
    const LOSER_TOMBSTONE_PATH = '/data/images/endscreen/loser/tombstone.png';
    const LOSER_TEXT_OFFSET = 20;
    const CAUSE_OF_DEATH_Y = 440;
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

    const CORRUPTED_HERO_THRESHOLD = 10;
    const CORRUPTED_HERO_CHANCE = 25;
    const CORRUPTED_NAME_MAXLENGTH = 6;
    const CORRUPTED_NAME_MINLENGTH = 3;
    const CORRUPTED_NAME_ZALGOCHARS = 4;
    const ELITE_MONSTER_THRESHOLD = 15;
    const ELITE_MONSTER_CHANCE = 25;
    const INCIDENT_THRESHOLD = 20;
    const INCIDENT_CHANCE = 20;

    const UTF8_ALPHABET_START = 65;
    const UTF8_ALPHABET_END = 90;

    const CORRUPTED_DEATHBLOW_RESIST = 30;
    const TRANSFORM_TURNS_CD = 4;

    const INVALID_ACTION_ERROR_MSG = 'Try again. If error persists - contact the developer with the issue on GitHub.';

    const RIP = 'R.I.P.';

    const LIGHT_CHANGE_INTERVAL = 24;

    public function __construct(Hero $hero, string $heroPicUrl, EncounterCollectionInterface $monsterCollection, string $chatCommand, LoopInterface $loop, bool $endless = false) {
        $this->hero = $hero;
        $this->loop = $loop;
        $this->heroPicUrl = $heroPicUrl;
        $this->encounterCollection = $monsterCollection;
        $this->endless = $endless;
        $this->chatCommand = $chatCommand;

        $this->loserTombstoneImageTemplate = new ImageTemplate();
        $mapper = new JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $json = json_decode(file_get_contents(
            dirname(__DIR__, 3) . str_replace('.png', '.json', self::LOSER_TOMBSTONE_PATH)
        ));
        $mapper->map($json, $this->loserTombstoneImageTemplate);
    }

    public function start(): EmbedInterface {
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
            $additionalEmbed = $this->monster->getTurn($this->hero);
            Helper::mergeEmbed($embed, $additionalEmbed);
        }
        $this->setCurrentFooter($embed);
        return $embed;
    }


    protected function setCurrentFooter(EmbedInterface $embed): void {
        if ($this->hero->isDead()) {
            $embed->setFooter('R.I.P. ' . $this->hero->name, $this->heroPicUrl);
            return;
        }
        if ($this->newTrinket !== null) {
            $embed->setFooter('Respond with "' . $this->chatCommand . ' [NUMBER]" to equip trinket in the corresponding slot.' . PHP_EOL . 'Alternatively, "'
                . $this->chatCommand . ' skip" will disregard the trinket. Skipping the trinket will provide you with time to quickly patch up and restore some HP.');
            return;
        }
        if ($this->incident !== null) {
            $embed->setFooter($this->incident->getDefaultFooterText($this->chatCommand, $this->hero->type->name));
            return;
        }
        $embed->setFooter($this->hero->type->getDefaultFooterText($this->chatCommand, $this->monster->isStealthed(), $this->noTransform())
            . PHP_EOL . ($this->killCount > 0 ? 'Kills: ' . $this->killCount : ''));
    }

    protected function resetTransformTimer(): void {
        $this->transformTimer = 0;
    }

    protected function transformTimerTick(): void {
        if ($this->transformTimer < self::TRANSFORM_TURNS_CD) {
            $this->transformTimer++;
        }
    }

    /**
     * @param IncidentAction|DirectAction|int $action
     * @return EmbedInterface
     */
    public function getTurn($action): EmbedInterface {
        if ($this->newTrinket !== null) {
            if (is_int($action)) {
                return $this->getEquipTrinketTurn($action);
            }
            return $this->getInvalidActionEmbed();
        }
        if ($this->incident !== null) {
            if ($action instanceof IncidentAction) {
                return $this->getIncidentTurn($action);
            }
            return $this->getInvalidActionEmbed();
        }
        return $this->getHeroTurn($action);
    }

    protected function getIncidentTurn(IncidentAction $action): EmbedInterface {
        $res = $this->newColoredEmbed();
        $this->incident = $action->getResult($this->hero, $res);
        if ($this->incident === null) {
            $this->newMonsterTurn($res);
        }
        $this->setCurrentFooter($res);
        return $res;
    }

    protected function rollTrinketTurn(EmbedInterface $resultEmbed): void {
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
        $this->setCurrentFooter($resultEmbed);
    }


    protected function getInvalidActionEmbed(): EmbedInterface {
        $res = new EmbedObject();
        $res->setTitle('Invalid action');
        $res->setDescription(self::INVALID_ACTION_ERROR_MSG);
        return $res;
    }


    /**
     * @param DirectAction|int $action
     * @return ExtendedPromiseInterface callback($messageData), canceller = fight is over
     */
    public function createTurnPromise($action): ExtendedPromiseInterface {
        $deferred = new Deferred();
        $turn = $this->getTurn($action);
        if ($this->isOver()) {
            $this->createEndscreen()->done(
                function ($data) use ($deferred, $turn) {
                    $deferred->reject(['embed' => $turn, 'fileData' => $data, 'fileName' => 'end.png']);
                },
                function () use ($deferred, $turn) {
                    $deferred->reject(['embed' => $turn, 'fileData' => null, 'fileName' => null]);
                }
            );
        } else {
            $deferred->resolve($turn);
        }
        return $deferred->promise();
    }

    public function createEndscreen(): ExtendedPromiseInterface {
        $fdl = new FileDownloader($this->loop);
        return $fdl->getDownloadAsyncImagePromise($this->heroPicUrl)->then(
            function ($imageFile) {
                return $this->killCount >= self::ENDSCREEN_THRESHOLD ? $this->composeBigEndscreen($imageFile) : $this->composeLoserEndscreen($imageFile);
            }
        );
    }


    protected function composeBigEndscreen($heroImageResource) {
        $endPath = dirname(__DIR__, 3) . self::ENDSCREEN_PATH
            . mb_strtolower(str_replace(' ', '_', $this->hero->type->name));
        $mapper = new JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $template = new ImageTemplate();
        $json = json_decode(file_get_contents($endPath . '.json'));
        $mapper->map($json, $template);

        $applier = new ImageTemplateApplier($template);
        $canvas = imagecreatefrompng($endPath . '.png');
        $applier->slapTemplate($heroImageResource, $canvas, true);
        $this->addCorpsesToImage($canvas);
        $this->addEndscreenTextToImage($canvas);

        ob_start();
        imagepng($canvas);
        $result = ob_get_clean();
        imagedestroy($canvas);

        return $result;
    }

    protected function composeLoserEndscreen($heroImageResource) {
        $canvas = imagecreatefrompng(dirname(__DIR__, 3) . self::LOSER_TOMBSTONE_PATH);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        $applier = new ImageTemplateApplier($this->loserTombstoneImageTemplate);
        $applier->slapTemplate($heroImageResource, $canvas, true);
        $this->addLoserTextToImage($canvas);

        ob_start();
        imagepng($canvas);
        $result = ob_get_clean();
        imagedestroy($canvas);

        return $result;
    }

    protected function addEndscreenTextToImage($image): void {
        $cyan = imagecolorallocate($image, 0, 255, 255);
        $red = imagecolorallocate($image, 255, 0, 0);
        $black = imagecolorallocate($image, 0, 0, 0);
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
        imagettftext($image,
            self::SMALL_FONT_SIZE,
            0,
            $this->getDefaultTextCenteredX(self::ENDSCREEN_WIDTH, self::SMALL_FONT_SIZE, $title),
            self::TITLE_Y,
            $cyan,
            $ttfPath,
            $title);

        if ($this->hero->causeOfDeath === null) {
            return;
        }
        if (mb_strpos($this->hero->causeOfDeath, AbstractLivingBeing::KILLER_CAUSE_OF_DEATH) !== false) {
            $killer = str_replace(AbstractLivingBeing::KILLER_CAUSE_OF_DEATH, '', $this->hero->causeOfDeath);
            $killerOffset = $this->getDefaultTtfBoundingBox(self::SMALL_FONT_SIZE, $killer)[1] + 24;
            $this->hero->causeOfDeath = AbstractLivingBeing::KILLER_CAUSE_OF_DEATH;
            imagettftext($image,
                self::SMALL_FONT_SIZE,
                0,
                1 + $this->getDefaultTextCenteredX(self::ENDSCREEN_WIDTH, self::SMALL_FONT_SIZE, $killer),
                1 + self::CAUSE_OF_DEATH_Y + $killerOffset,
                $black,
                $ttfPath,
                $killer);
            imagettftext($image,
                self::SMALL_FONT_SIZE,
                0,
                $this->getDefaultTextCenteredX(self::ENDSCREEN_WIDTH, self::SMALL_FONT_SIZE, $killer),
                self::CAUSE_OF_DEATH_Y + $killerOffset,
                $red,
                $ttfPath,
                $killer);
        }
        imagettftext($image,
            self::SMALL_FONT_SIZE,
            0,
            1 + $this->getDefaultTextCenteredX(self::ENDSCREEN_WIDTH, self::SMALL_FONT_SIZE, $this->hero->causeOfDeath),
            1 + self::CAUSE_OF_DEATH_Y,
            $black,
            $ttfPath,
            $this->hero->causeOfDeath);
        imagettftext($image,
            self::SMALL_FONT_SIZE,
            0,
            $this->getDefaultTextCenteredX(self::ENDSCREEN_WIDTH, self::SMALL_FONT_SIZE, $this->hero->causeOfDeath),
            self::CAUSE_OF_DEATH_Y,
            $red,
            $ttfPath,
            $this->hero->causeOfDeath);

    }

    protected function addLoserTextToImage($image): void {
        $ttfPath = dirname(__DIR__, 3) . self::FONT_PATH;
        $red = imagecolorallocate($image, 255, 0, 0);
        imagettftext($image,
            self::FONT_SIZE,
            0,
            $this->getDefaultTextCenteredX($this->loserTombstoneImageTemplate->templateW, self::FONT_SIZE, self::RIP),
            self::LOSER_TEXT_OFFSET + 30,
            $red,
            $ttfPath,
            self::RIP);

        $textDate = date('M d, Y');
        $textBoundingBox = $this->getDefaultTtfBoundingBox(self::FONT_SIZE, $textDate);
        imagettftext($image,
            self::FONT_SIZE,
            0,
            $this->getDefaultTextCenteredX($this->loserTombstoneImageTemplate->templateW, self::FONT_SIZE, $textDate),
            $this->loserTombstoneImageTemplate->templateH - self::LOSER_TEXT_OFFSET - $textBoundingBox[1],
            $red,
            $ttfPath,
            $textDate);
    }

    protected function getDefaultTtfBoundingBox(int $fontSize, string $text): array {
        $ttfPath = dirname(__DIR__, 3) . self::FONT_PATH;
        return imagettfbbox($fontSize, 0, $ttfPath, $text);
    }

    protected function getDefaultTextCenteredX(int $imageWidth, int $fontSize, string $text): int {
        $boundingBox = $this->getDefaultTtfBoundingBox($fontSize, $text);
        return $imageWidth / 2 - ($boundingBox[2] - $boundingBox[0]) / 2;
    }

    protected function addCorpsesToImage($image): void {
        $distributions = $this->getCorpsesDistributionArray();
        $mapper = new JsonMapper();
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

    protected function getEquipTrinketTurn(int $action): EmbedInterface {
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
        $this->setCurrentFooter($res);
        return $res;
    }

    protected function noTransform(): bool {
        if ($this->transformTimer < self::TRANSFORM_TURNS_CD) {
            return true;
        }
        return false;
    }

    /**
     * @param DirectAction $action
     * @return EmbedInterface
     */
    protected function getHeroTurn(DirectAction $action): EmbedInterface {
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
                    $embed->setFooter($this->hero->name . ' is victorious!', $this->heroPicUrl);
                }
                return $embed;
            }
        }
        $this->setCurrentFooter($embed);
        return $embed;
    }

    protected function monsterTurnIsFinal(EmbedInterface $embed): bool {
        if (!$this->monster->isDead()) {
            $embed->addField($this->monster->type->name . '\'s turn!', '*``' . $this->monster->getHealthStatus() . '``*');
            Helper::mergeEmbed($embed, $this->monster->getTurn($this->hero));
        }
        if ($this->monster->isDead()) {
            if ($this->endless) {
                $this->killCount++;
                $this->killedMonsters[] = $this->monster->type->name;
                return $this->rollNewEncounterIsFinal($embed);
            } else {
                return true;
            }
        }
        if ($this->hero->isStunned()) {
            Helper::mergeEmbed($embed, $this->hero->getTurn($this->hero));
            return $this->monsterTurnIsFinal($embed);
        }
        return false;
    }

    protected function rollNewEncounterIsFinal(EmbedInterface $embed): bool {
        if ($this->killCount >= self::TRINKET_KILLS_THRESHOLD) {
            if ($this->killCount >= self::INCIDENT_THRESHOLD && mt_rand(1, 100) <= self::INCIDENT_CHANCE) {
                $this->rollIncidentTurn($embed);
            } else {
                $this->rollTrinketTurn($embed);
            }
            return true;
        }
        return $this->newMonsterTurn($embed);
    }

    protected function rollIncidentTurn(EmbedInterface $embed) {
        $this->incident = $this->encounterCollection->randIncident();
        $embed->addField('*' . $this->incident->name . '*', '*``' . $this->incident->description . '``*');
        $embed->setImage($this->incident->image);
        $this->setCurrentFooter($embed);
    }

    protected function transformCdEmbed(): EmbedInterface {
        $embed = $this->newColoredEmbed();
        $embed->setTitle('Can\'t transform yet.');
        $embed->setDescription('Cooldown: ' . (self::TRANSFORM_TURNS_CD - $this->transformTimer) . ' turns.');
        return $embed;
    }

    /**
     * @param EmbedInterface $resultEmbed
     * @return bool Whether or not the first turn of the new monster is final. AKA if Monsters dies on his first turn.
     */
    public function newMonsterTurn(EmbedInterface $resultEmbed): bool {
        $this->monster = $this->rollNewMonster();
        $this->manageLight($resultEmbed);
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


    public function manageLight(EmbedInterface $resultEmbed) {
        if ($this->killCount % self::LIGHT_CHANGE_INTERVAL !== 0) {
            if ($this->currentLight !== null) {
                $this->currentLight->apply($this->monster);
            }
            return;
        }
        if ($this->currentLight !== null) {
            $resultEmbed->addField('***Light is no longer ' . $this->currentLight->name . '***', '*``Lighting effects removed.``*');
            $this->currentLight->remove($this->hero);
            $this->currentLight = null;
            return;
        }
        $this->currentLight = LightingEffectFactory::create();
        $this->currentLight->apply($this->hero);
        $this->currentLight->apply($this->monster);
        $resultEmbed->addField($this->currentLight->getTitle(), $this->currentLight->getDescription());
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

    public function getHeroStats(): EmbedInterface {
        return $this->hero->getStatsAndEffectsEmbed();
    }

    public function getHeroActionsDescriptions(): EmbedInterface {
        $res = $this->newColoredEmbed();
        $res->setTitle($this->hero->name . '\'s abilities and actions:');
        $description = '';
        foreach ($this->hero->type->actions as $action) {
            $description .= '***' . $action->name . '***' . PHP_EOL . '``' . $action->__toString() . '``' . PHP_EOL;
        }
        $description .= '*' . $this->hero->type->defaultAction()->name . '*'
            . PHP_EOL . '``' . $this->hero->type->defaultAction()->effect->getDescription() . '``';
        $res->setDescription($description);
        return $res;
    }

    public function isOver(): bool {
        return $this->hero->isDead() || ($this->monster->isDead() && !$this->endless);
    }

    /**
     * @param string $actionName
     * @return IncidentAction|DirectAction|int|null
     */
    public function getActionIfValid(string $actionName) {
        if ($this->newTrinket !== null) {
            if ($actionName === 'skip') {
                return self::SKIP_TRINKET_ACTION;
            }
            if (is_numeric($actionName)) {
                return (int)$actionName;
            }
            return null;
        }

        if ($this->incident !== null) {
            return $this->incident->getActionIfValid($actionName, $this->hero->type->name);
        }

        if ($this->noTransform() && $actionName === DirectAction::TRANSFORM_ACTION) {
            return null;
        }
        $action = $this->hero->type->getActionIfValid($actionName);
        return is_null($action) || (!$action->isUsableVsStealth() && $this->monster->statManager->isStealthed()) ? null : $action;
    }

    protected function newColoredEmbed(): EmbedInterface {
        $res = new EmbedObject();
        $res->setColor($this->hero->type->embedColor);
        return $res;
    }
}
