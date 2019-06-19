<?php
/**
 * Created by PhpStorm.
 * User: KolBrony
 * Date: 19.06.2019
 * Time: 14:41
 */

namespace Ancestor\Interaction;

use CharlotteDunois\Yasmin\Models\MessageEmbed;

class Monster extends AbstractInteraction {

    /**
     * @var int
     * @required
     */
    public $healthMax;

    /**
     * @var int|null
     */
    private $currentHealth = null;

    /**
     * @var bool
     */
    public $isStunned = false;


    /**
     * @param string $commandName
     * @param Action[] $userActions
     * @return MessageEmbed
     */
    public function getEmbedResponse(string $commandName, $userActions = null): MessageEmbed {
        $embedResponse = new MessageEmbed();
        $embedResponse->setThumbnail($this->image);
        $embedResponse->setTitle('**You encounter ' . $this->name . '**');
        $embedResponse->setColor(DEFAULT_EMBED_COLOR);
        $embedResponse->setDescription('*' . $this->description . '*');
        if ($userActions != null) {
            $footerText = 'Respond with "' . $commandName . ' [ACTION]" to perform the corresponding action. ' . PHP_EOL
                . 'Available actions: ';
            foreach ($userActions as $action) {
                $footerText .= mb_strtolower($action->name) . ', ';
            }
            $footerText .= self::DEFAULT_ACTION;
            $embedResponse->setFooter($footerText);
        }
        return $embedResponse;
    }

    /**
     * @return Action
     */
    public function getRandomAction(): Action {
        return $this->actions[mt_rand(0, sizeof($this->actions))];
    }

    /**
     * @return int
     */
    public function getCurrentHealth(): int {
        if ($this->currentHealth === null) {
            $this->currentHealth = $this->healthMax;
        }
        return $this->currentHealth;
    }
}