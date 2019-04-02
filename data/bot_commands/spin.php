<?php
/**
 * Spin command.
 * Spins the Tide
 */
$tideURL = json_decode(file_get_contents(dirname(__DIR__ ,2).'/config.json', true), true)['tideURL'];

return(
    new class($handler, $tideURL) extends Ancestor\CommandHandler\Command {
        private $tideURL;
        function __construct(Ancestor\CommandHandler\CommandHandler $handler, $tideURL)
        {
            parent::__construct($handler, 'spin', 'Spins tide');
            $this->tideURL = $tideURL;
        }
        function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args): void
        {
            $embedResponse = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
            $embedResponse->setTitle('How quickly the tide turns?');
            $embedResponse->setImage($this->tideURL);
            $message->channel->send('', array('embed' => $embedResponse));
        }
    }
);