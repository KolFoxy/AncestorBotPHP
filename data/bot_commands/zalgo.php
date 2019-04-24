<?php
/**
 * Zalgo command
 * Adds special characters to the sentence to make it appear like it was cursed.
 */

return (
new class($handler) extends Ancestor\CommandHandler\Command {

    private $MAX_ZALGO_CHARACTERS = 150;
    private $RDP;

    function __construct(Ancestor\CommandHandler\CommandHandler $handler) {
        $this->RDP = Ancestor\RandomData\RandomDataProvider::GetInstance();
        parent::__construct($handler, 'zalgo', 'transforms given sentence into something ' .
            $this->ZalgorizeString('like this', 3), array('cursed'));
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args): void {
        if (empty($args)) {
            return;
        }
        $embedResponse = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
        $strArray = $this->mb_str_split(\CharlotteDunois\Yasmin\Utils\DataHelpers::cleanContent($message, implode(' ', $args))
            , $this->MAX_ZALGO_CHARACTERS);
        foreach ($strArray as $str) {
            $embedResponse->addField('``' .
                $this->ZalgorizeString($this->RDP->GetRandomZalgoTitle(), 2) . '``',
                $this->ZalgorizeString($str, 1));
        };
        $embedResponse->setFooter($this->ZalgorizeString($message->author->username, 1), $message->author->getAvatarURL());
        $message->channel->send('', array('embed' => $embedResponse));
    }

    function ZalgorizeString(string $input, int $zalgoPerChar): string {
        $result = '';
        $strlen = mb_strlen($input);
        if ($strlen > $this->MAX_ZALGO_CHARACTERS) {
            $strlen = $this->MAX_ZALGO_CHARACTERS;
        }
        for ($i = 0; $i < $strlen; $i++) {

            $result .= $this->RDP->GetRandomZalgoString($zalgoPerChar) . mb_substr($input, $i, 1);
        }
        if ($strlen == $this->MAX_ZALGO_CHARACTERS) {
            $result .= '…';
        }
        return $result;
    }

    function mb_str_split(string $str, int $length = 0): array {
        if ($length > 0) {
            $result = array();
            $strLength = mb_strlen($str);
            for ($i = 0; $i < $strLength; $i += $length) {
                $result[] = mb_substr($str, $i, $length);
            }
            return $result;
        }
    }

}
);