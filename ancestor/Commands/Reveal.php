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
use Ancestor\ImageTemplate\ImageTemplateApplier;

class Reveal extends Command {

    const TENT_MIN_AMOUNT = 3;
    const TENT_MAX_AMOUNT = 8;

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


    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'reveal', 'Add tentacles to your avatar or image.', ['tentacles']);
        $mapper = new \JsonMapper();
        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . '/data/images/reveal/template.json'));
        $mapper->bExceptionOnMissingData = true;
        $this->defaultTemplate = new ImageTemplate();
        $mapper->map($json, $this->defaultTemplate);
        $this->pathsToImages = glob(dirname(__DIR__, 2) . '/data/images/reveal/tentacles/*.png');
        $this->imageDl = new FileDownloader($handler->client->getLoop());
    }


    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        $commandHelper = new CommandHelper($message);
        $callbackObj = function ($image) use ($commandHelper) {
            $file = $this->reveal($image);
            if ($file === false) {
                $commandHelper->message->reply('***Your image is too powerful even for cosmic powers!***');
                return;
            }
            $commandHelper->RespondWithAttachedFile($file, 'revealed.png');
        };

        try {
            $this->imageDl->DownloadUrlAsync($commandHelper->ImageUrlFromCommandArgs($args), $callbackObj);
        } catch (\Throwable $e){
            echo $e->getMessage();
            $message->reply('***Not today***');
        }
    }


    /**
     * @param resource $imageFile
     * @return bool|string
     */
    function reveal($imageFile) {
        if ($imageFile === false || !($imageToReveal = CommandHelper::ImageFromFileHandler($imageFile))) {
            return false;
        }

        $paths = $this->pathsToImages;
        shuffle($paths);

        $tentNum = mt_rand(self::TENT_MIN_AMOUNT, self::TENT_MAX_AMOUNT);
        $templateApplier = new ImageTemplateApplier($this->defaultTemplate);

        for ($i = 0; $i < $tentNum; $i++) {
            $imageToReveal = $templateApplier->applyTemplate($imageToReveal, imagecreatefrompng($paths[$i]), true);
        }

        ob_start();
        imagepng($imageToReveal);
        $result = ob_get_clean();
        imagedestroy($imageToReveal);

        return $result;

    }
}