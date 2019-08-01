<?php
/**
 * Zalgo command
 * Adds special characters to the sentence to make it appear like it was cursed.
 */

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command as Command;
use Ancestor\CommandHandler\CommandHandler as CommandHandler;
use Ancestor\CommandHandler\CommandHelper;
use Ancestor\RandomData\RandomDataProvider as RandomDataProvider;
use CharlotteDunois\Yasmin\Utils\MessageHelpers;
use Ancestor\Zalgo\Zalgo as Zalgorizer;

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
            Zalgorizer::zalgorizeString('like this', 3), ['cursed']);
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        if (empty($args)) {
            return;
        }
        $embedResponse = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
        $strArray = CommandHelper::mb_str_split(MessageHelpers::cleanContent($message, implode(' ', $args)), self::FIELD_MAX_LENGTH);
        foreach ($strArray as $str) {
            $embedResponse->addField('``' . Zalgorizer::zalgorizeString($this->getRandomZalgoTitle(), self::ZALGO_PER_CHAR) . '``',
                Zalgorizer::zalgorizeString($str, self::ZALGO_PER_CHAR));
        };
        $embedResponse->setFooter(Zalgorizer::zalgorizeString($message->author->username, 1), $message->author->getAvatarURL());
        $message->channel->send('', ['embed' => $embedResponse]);
    }

    function getRandomZalgoTitle(): string {
        return $this->zalgoTitles[mt_rand(0, $this->ztCount)];
    }

}