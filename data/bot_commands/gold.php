<?php
/**
 * "Give random reward" command
 */

return (
new class($handler) extends Ancestor\CommandHandler\Command {


    function __construct(Ancestor\CommandHandler\CommandHandler $handler) {
        parent::__construct($handler, 'gold', 'Gives a random reward.');
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args): void {
        $RDP = Ancestor\RandomData\RandomDataProvider::GetInstance();
        $CH = new \Ancestor\CommandHandler\CommandHelper($message);
        $CH->RespondWithEmbedImage($RDP->GetRandomReward(),$RDP->GetRandomRewardQuote());
    }
}
);