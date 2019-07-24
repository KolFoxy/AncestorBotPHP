<?php

namespace Ancestor\Interaction\Fight;

use Ancestor\CommandHandler\CommandHelper as Helper;
use Ancestor\Interaction\DirectAction;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\Monster;
use Ancestor\Interaction\Stats\TrinketFactory;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

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

    const TRINKET_KILLS_THRESHOLD = 2;

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
        $embed->setFooter($this->hero->type->getDefaultFooterText($this->chatCommand, $this->monster->isStealthed()));

        if ((bool)mt_rand(0, 1)) {
            $additionalEmbed = $this->monster->getTurn($this->hero, $this->monster->type->getRandomAction());
            Helper::mergeEmbed($embed, $additionalEmbed);
        }

        return $embed;
    }

    public function getTurn(DirectAction $action, string $heroPicUrl): MessageEmbed {
        $target = $action->requiresTarget ? $this->hero : $this->monster;
        $embed = $this->hero->getHeroTurn($action, $target);
        if (!$this->hero->isDead()) {
            if (!$this->monster->isDead()) {
                $extraEmbed = $this->monster->getTurn($this->hero, $this->monster->type->getRandomAction());
                $embed->addField($this->monster->type->name . '\'s turn!', '*``' . $this->monster->getHealthStatus() . '``*');
                Helper::mergeEmbed($embed, $extraEmbed);
            } else {
                if ($this->endless) {
                    $this->killCount++;
                    $this->monster = new Monster($this->monsterCollection->getRandMonsterType());
                    $embed->addField('***' . $this->monster->type->name . ' emerges from the darkness!***', '*``' . $this->monster->getHealthStatus() . '``*');
                    Helper::mergeEmbed($embed, $this->monster->getTurn($this->hero, $this->monster->type->getRandomAction()));
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

        $embed->setFooter($this->hero->type->getDefaultFooterText($this->chatCommand, $this->monster->isStealthed()) .
            ($this->killCount > 0 ? 'Kills: ' . $this->killCount : ''));
        return $embed;
    }

    public function getHeroStats(): MessageEmbed {
        return $this->hero->getStatsAndEffectsEmbed()->setFooter(
            $this->hero->type->getDefaultFooterText($this->chatCommand, $this->monster->isStealthed())
        );
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
        $res->setFooter($this->hero->type->getDefaultFooterText($this->chatCommand, $this->monster->isStealthed()));
        return $res;
    }

    protected function rollTrinkets(MessageEmbed $resultEmbed) {
        if ($this->killCount < self::TRINKET_KILLS_THRESHOLD) {
            return;
        }
        $newTrinket = TrinketFactory::create($this->hero);
        if ($this->hero->hasTrinket($newTrinket->name)) {
            return;
        }

    }

    public function isOver(): bool {
        return $this->hero->isDead() || ($this->monster->isDead() && !$this->endless);
    }

    public function getActionIfValid(string $actionName) {
        //TODO : trinket action support and mild refactor
    }
}