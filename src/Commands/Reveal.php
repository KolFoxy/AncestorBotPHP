<?php

namespace Ancestor\Commands;

use Ancestor\BotIO\MessageInterface;
use Ancestor\Command\Command;
use Ancestor\Command\CommandHandler;
use Ancestor\Command\CommandHelper;
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
    private ImageTemplate $defaultTemplate;

    /**
     * @var FileDownloader
     */
    private FileDownloader $downloader;


    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'reveal', 'Add tentacles to your avatar or image.', ['tentacles']);
        $mapper = new \JsonMapper();
        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . '/data/images/reveal/template.json'));
        $mapper->bExceptionOnMissingData = true;
        $this->defaultTemplate = new ImageTemplate();
        $mapper->map($json, $this->defaultTemplate);
        $this->pathsToImages = glob(dirname(__DIR__, 2) . '/data/images/reveal/tentacles/*.png');
        $this->downloader = new FileDownloader($handler->client->getLoop());
    }


    function run(MessageInterface $message, array $args) {
        $callbackObj = function ($image) use ($message) {
            $revealedImage = $this->reveal($image);
            if ($revealedImage === false) {
                $message->reply('***Your image is too powerful even for cosmic powers!***');
                return;
            }
            $message->getChannel()->sendWithFile('','revealed.png',$revealedImage);
        };

        try {
            $this->downloader->DownloadUrlAsync(CommandHelper::ImageUrlFromCommandArgs($args, $message), $callbackObj);
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