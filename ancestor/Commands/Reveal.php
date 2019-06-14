<?php
/**
 * Created by PhpStorm.
 * User: KolBrony
 * Date: 14.06.2019
 * Time: 17:28
 */

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;
use Ancestor\CommandHandler\CommandHelper;
use Ancestor\FileDownloader\FileDownloader;
use Ancestor\ImageTemplate\ImageTemplate;

class Reveal extends Command {


    /**
     * Array of paths to tentacle images
     * @var string[]
     */
    private $pathsToImages;

    /**
     * @var ImageTemplate
     */
    private $defaultTemplate;

    /**
     * @var FileDownloader
     */
    private $imageDl;

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        $commandHelper = new CommandHelper($message);
        $callbackObj = function ($image) use ($commandHelper) {
            $file = $this->Reveal($image);
            if ($file === false) {
                //TODO: Add EmbedResponse to CommandHelper
            }
        $commandHelper->RespondWithAttachedFile($file,'revealed.png');
        };
    }

    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'reveal', 'Add tentacles to your avatar or image.', ['tentacles']);
        $mapper = new \JsonMapper();
        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . '/data/images/reveal/template.json'));
        $mapper->bExceptionOnMissingData = true;
        $mapper->map($json, $this->defaultTemplate);
        $this->pathsToImages = glob(dirname(__DIR__, 2) . '/data/images/reveal/tentacles/*.png');
        $this->imageDl = new FileDownloader($handler->client->getLoop());
    }
}