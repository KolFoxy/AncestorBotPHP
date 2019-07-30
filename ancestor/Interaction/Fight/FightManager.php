<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace Ancestor\Interaction\Fight;

use Ancestor\CommandHandler\CommandHelper as Helper;
use Ancestor\Interaction\DirectAction;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\Monster;
use Ancestor\Interaction\Stats\Trinket;
use Ancestor\Interaction\Stats\TrinketFactory;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use function Composer\Autoload\includeFile;

class FightManager {

    /**
     * @var Hero
     */
    public $hero;

    /**
     * @var Monster
     */
    public $monster;

    /**
     * @var int
     */
    public $killCount = 0;

    /**
     * @var MonsterCollectionInterface
     */
    public $monsterCollection;

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

    const TRINKET_KILLS_THRESHOLD = 2;

    const SKIP_TRINKET_ACTION = -13505622;

    const SKIP_HEAL_PERCENTAGE = 0.2;

    public function __construct(Hero $hero, MonsterCollectionInterface $monsterCollection, string $chatCommand, bool $endless = false) {
        $this->hero = $hero;
        $this->monsterCollection = $monsterCollection;
        $this->endless = $endless;
        $this->chatCommand = $chatCommand;
    }

    public function start(): MessageEmbed {
        if (!isset($this->monster)) {
            $this->monster = new Monster($this->monsterCollection->getRandMonsterType());
        }
        $embed = new MessageEmbed();
        $embed->setTitle('**' . $this->hero->name . '**');
        $embed->setThumbnail($this->hero->type->image);
        $embed->setDescription('*``' . $this->hero->type->description . '``*' . PHP_EOL . '``' . $this->hero->getStatus() . '``');
        $embed->addField(
            'You encounter a vile **' . $this->monster->type->name . '**',
            '*``' . $this->monster->type->description . '``*' . PHP_EOL . '*``' . $this->monster->getHealthStatus() . '``*'
            . PHP_EOL . $this->monster->statManager->getAllCurrentEffectsString()
        );
        $embed->setImage($this->monster->type->image);
        $embed->setFooter($this->getCurrentFooter());

        if ((bool)mt_rand(0, 1)) {
            $additionalEmbed = $this->monster->getTurn($this->hero, $this->monster->getProgrammableAction());
            Helper::mergeEmbed($embed, $additionalEmbed);
        }
        return $embed;
    }

    protected function getCurrentFooter(): string {
        if ($this->newTrinket !== null) {
            return 'Respond with "' . $this->chatCommand . ' [NUMBER]" to equip trinket in the corresponding slot.' . PHP_EOL . 'Alternatively, "'
                . $this->chatCommand . ' skip" will disregard the trinket. Skipping the trinket will provide you with time to quickly patch up and restore some HP.';
        }
        return $this->hero->type->getDefaultFooterText($this->chatCommand, $this->monster->isStealthed())
            . PHP_EOL . ($this->killCount > 0 ? 'Kills: ' . $this->killCount : '');
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
            return (new MessageEmbed())->setTitle('***ERROR!***')->setDescription('Invalid action. Terminating session.');
        }
        return $this->getHeroTurn($action, $heroPicUrl);
    }

    protected function getEquipTrinketTurn(int $action): MessageEmbed {
        $res = new MessageEmbed();
        if ($action === self::SKIP_TRINKET_ACTION) {
            $heal = mt_rand(1, (int)($this->hero->healthMax) * self::SKIP_HEAL_PERCENTAGE);
            $this->hero->addHealth($heal);
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

    /**
     * @param DirectAction $action
     * @param string $heroPicUrl
     * @return MessageEmbed
     */
    protected function getHeroTurn(DirectAction $action, string $heroPicUrl): MessageEmbed {
        $target = $action->requiresTarget ? $this->hero : $this->monster;
        $embed = $this->hero->getHeroTurn($action, $target);
        if (!$this->hero->isDead()) {
            if (!$this->monster->isDead()) {
                $embed->addField($this->monster->type->name . '\'s turn!', '*``' . $this->monster->getHealthStatus() . '``*');
                Helper::mergeEmbed($embed, $this->monster->getTurn($this->hero, $this->monster->getProgrammableAction()));
            }
            if ($this->monster->isDead()) {
                if ($this->endless) {
                    $this->killCount++;
                    if ($this->rollTrinkets($embed)) {
                        return $embed;
                    }
                    $this->newMonsterTurn($embed);
                } else {
                    $embed->setFooter($this->hero->name . ' is victorious!', $heroPicUrl);
                    return $embed;
                }
            }
        }

        if ($this->hero->isDead()) {
            $embed->setFooter('R.I.P. ' . $this->hero->name, $heroPicUrl);
            return $embed;
        }

        $embed->setFooter($this->getCurrentFooter());
        return $embed;
    }

    public function newMonsterTurn(MessageEmbed $resultEmbed) {
        $this->monster = new Monster($this->monsterCollection->getRandMonsterType());
        $resultEmbed->addField('***' . $this->monster->type->name . ' emerges from the darkness!***',
            '*``' . $this->monster->type->description . '``*'
            . PHP_EOL . '*``' . $this->monster->getHealthStatus() . '``*');
        if ((bool)mt_rand(0, 1)) {
            Helper::mergeEmbed($resultEmbed, $this->monster->getTurn($this->hero, $this->monster->getProgrammableAction()));
        }
        $resultEmbed->setImage($this->monster->type->image);
    }

    public function getHeroStats(): MessageEmbed {
        return $this->hero->getStatsAndEffectsEmbed()->setFooter($this->getCurrentFooter());
    }

    public function getHeroActionsDescriptions(): MessageEmbed {
        $res = new MessageEmbed();
        $res->setTitle($this->hero->name . '\'s abilities and actions:');
        $description = '';
        foreach ($this->hero->type->actions as $action) {
            $description .= '***' . $action->name . '***' . PHP_EOL . '``' . $action->effect->getDescription() . '``' . PHP_EOL;
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
        $resultEmbed->addField('You\'ve found a new trinket: ***' . $newTrinket->name . '***',
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
        return $this->hero->type->getActionIfValid($actionName);
    }
}