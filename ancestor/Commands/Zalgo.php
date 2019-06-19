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

class Zalgo extends Command {

    const MAX_ZALGO_CHARACTERS = 150;
    const ZALGO_PER_CHAR = 2;
    const UTF8_ZALGO_FIRST = 768;
    const UTF8_ZALGO_LAST = 879;
    private $fieldLength;
    /**
     * @var string[]
     */
    private $zalgoTitles;

    function __construct(CommandHandler $handler) {
        $this->zalgoTitles = $array = file(dirname(__DIR__, 2) . '/data/zalgoTitles');
        $this->fieldLength = self::MAX_ZALGO_CHARACTERS / self::ZALGO_PER_CHAR;
        parent::__construct($handler, 'zalgo', 'transforms given sentence into something ' .
            $this->ZalgorizeString('like this', 3), array('cursed'));
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        if (empty($args)) {
            return;
        }
        $embedResponse = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
        $strArray = CommandHelper::mb_str_split(MessageHelpers::cleanContent($message, implode(' ', $args)), $this->fieldLength);
        foreach ($strArray as $str) {
            $embedResponse->addField('``' .
                $this->ZalgorizeString(RandomDataProvider::GetInstance()->GetRandomData($this->zalgoTitles), 2) . '``',
                $this->ZalgorizeString($str, self::ZALGO_PER_CHAR));
        };
        $embedResponse->setFooter($this->ZalgorizeString($message->author->username, 1), $message->author->getAvatarURL());
        $message->channel->send('', ['embed' => $embedResponse]);
    }

    function ZalgorizeString(string $input, int $zalgoPerChar): string {
        $result = '';
        $strLen = mb_strlen($input);

        for ($i = 0; $i < $strLen; $i++) {
            $result .= $this->GetRandomZalgoString($zalgoPerChar) . mb_substr($input, $i, 1);
        }

        if ($strLen >= $this->fieldLength) {
            $result .= '…';
        }
        return $result;
    }

    /**
     * @return string
     */
    function GetRandomZalgoChar(): string {
        return mb_chr(mt_rand(self::UTF8_ZALGO_FIRST, self::UTF8_ZALGO_LAST), 'UTF-8');
    }

    /**
     * @param int $size
     * @return string
     */
    function GetRandomZalgoString(int $size): string {
        $rez = '';
        for ($i = 0; $i < $size; $i++) {
            $rez .= $this->GetRandomZalgoChar();
        }
        return $rez;
    }

}