<?php
/**
 * Zalgo command
 * Adds special characters to the sentence to make it appear like it was cursed.
 */

namespace Ancestor\Commands;

use Ancestor\BotIO\EmbedObject;
use Ancestor\BotIO\MessageInterface;
use Ancestor\Command\Command as Command;
use Ancestor\Command\CommandHandler as CommandHandler;
use Ancestor\Command\CommandHelper;

use Ancestor\Zalgo\Zalgo as Zalgolizer;

class Zalgo extends Command {

    const MAX_ZALGO_CHARACTERS = 150;
    const ZALGO_PER_CHAR = 2;
    const FIELD_MAX_LENGTH = self::MAX_ZALGO_CHARACTERS / self::ZALGO_PER_CHAR;
    /**
     * @var string[]
     */
    private $zalgoTitles;

    private $ztCount;

    function __construct(CommandHandler $handler) {
        $this->zalgoTitles = $array = file(dirname(__DIR__, 2) . '/data/zalgoTitles');
        $this->ztCount = count($this->zalgoTitles) - 1;
        parent::__construct($handler, 'zalgo', 'transforms given sentence into something ' .
            Zalgolizer::zalgorizeString('like this', 3), ['cursed']);
    }

    function run(MessageInterface $message, array $args) {
        if (empty($args)) {
            return;
        }
        $embedResponse = new EmbedObject();
        //Maybe replace trim with more extensive cleaning method, testing required
        $strArray = CommandHelper::mb_str_split(trim(implode(' ', $args)), self::FIELD_MAX_LENGTH);
        foreach ($strArray as $str) {
            $embedResponse->addField('``' . Zalgolizer::zalgorizeString($this->getRandomZalgoTitle(), self::ZALGO_PER_CHAR) . '``',
                Zalgolizer::zalgorizeString($str, self::ZALGO_PER_CHAR));
        }
        $embedResponse->setFooter(Zalgolizer::zalgorizeString($message->getAuthor()->getUsername(), 1), $message->getAuthor()->getAvatarURL());
        $message->getChannel()->send('', $embedResponse);
    }

    function getRandomZalgoTitle(): string {
        return $this->zalgoTitles[mt_rand(0, $this->ztCount)];
    }

}