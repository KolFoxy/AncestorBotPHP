<?php
/**
 * Spin command.
 * Spins the Tide
 */

namespace Ancestor\Commands;

use Ancestor\BotIO\MessageInterface;
use Ancestor\Command\Command as Command;
use Ancestor\Command\CommandHandler as CommandHandler;
use Ancestor\Command\CommandHelper;
use Ancestor\FileDownloader\FileDownloader;
use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\ImageTemplate\ImageTemplateApplier;
use GifCreator;


class Spin extends Command {

    const HOW_QUICKLY_THE_TIDE_TURNS = 'How quickly the tide turns?';
    private string $tidePath;
    private string $ancestorPath;
    private $tideURL;
    const FRAME_TIME = 11;
    /**
     * @var FileDownloader
     */
    private FileDownloader $downloader;

    /**
     * @var ImageTemplate
     */
    private ImageTemplate $tideTemplate;
    /**
     * @var ImageTemplateApplier
     */
    private ImageTemplateApplier $tideTemplateApplier;


    function __construct(CommandHandler $handler, $tideURL) {
        parent::__construct($handler, 'spin', 'Spins a [@user] inside of Tide™.');
        $this->tideURL = $tideURL;
        $this->tidePath = dirname(__DIR__, 2) . '/data/images/spin_gif/tide_empty.png';
        $this->ancestorPath = dirname(__DIR__, 2) . '/data/images/spin_gif/ancestor.png';

        $this->downloader = new FileDownloader($this->handler->client->getLoop());

        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;

        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . '/data/images/spin_gif/tide_template.json'));
        $this->tideTemplate = new ImageTemplate();
        $mapper->map($json, $this->tideTemplate);
        $this->tideTemplateApplier = new ImageTemplateApplier($this->tideTemplate);

    }

    function run(MessageInterface $message, array $args) {
        $callbackObj = function ($image) use ($message) {
            $spinnedImage = $this->spinImage($image);
            if ($spinnedImage === false) {
                $message->replyWithEmbedImage('',self::HOW_QUICKLY_THE_TIDE_TURNS,$this->tideURL);
                return;
            }
            $message->getChannel()->sendWithFile('','spin.gif', $spinnedImage);
        };
        try {
            $this->downloader->DownloadUrlAsync(CommandHelper::ImageUrlFromCommandArgs($args, $message), $callbackObj);
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
            $message->replyWithEmbedImage('',self::HOW_QUICKLY_THE_TIDE_TURNS,$this->tideURL);
        }
    }

    /**
     * Spins image from $imageFile handler
     * @param resource $imageFile
     * @return bool|string
     * @throws \Exception
     */
    function spinImage($imageFile) {
        if ($imageFile === false || ($imageToSpin = CommandHelper::ImageFromFileHandler($imageFile)) === false) {
            return false;
        }

        $imageToSpin = $this->tideTemplateApplier->applyTemplate($imageToSpin, imagecreatefrompng($this->tidePath), true);
        $frames = $this->getImageRotationsWithAncestor($imageToSpin);

        $animation = new GifCreator\AnimGif();
        $animation->create($frames, [self::FRAME_TIME]);

        ob_start();
        echo $animation->get();
        $result = ob_get_clean();

        foreach ($frames as $frame) {
            imagedestroy($frame);
        }
        return $result;

    }

    function getImageRotationsWithAncestor($image, int $rotations = 4, float $rotationAngle = 90) {
        $res = [];
        $ancestorPng = imagecreatefrompng($this->ancestorPath);
        for ($i = 1; $i < $rotations; $i++) {
            $rotated_image = imagerotate($image, $rotationAngle * $i, 0);
            imagecopy($rotated_image, $ancestorPng,
                0, 0,
                0, 0,
                $this->tideTemplate->templateW, $this->tideTemplate->templateH);
            $res[] = $rotated_image;
        }
        imagecopy($image, $ancestorPng, 0, 0, 0, 0,
            $this->tideTemplate->templateW, $this->tideTemplate->templateH);
        $res[] = $image;
        imagedestroy($ancestorPng);
        return array_reverse($res);
    }


}