<?php
/**
 * Spin command.
 * Spins the Tide
 */

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command as Command;
use Ancestor\CommandHandler\CommandHandler as CommandHandler;
use Ancestor\CommandHandler\CommandHelper;
use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\ImageTemplate\ImageTemplateApplier;
use GifCreator;

class Spin extends Command {

    private $tidePath;
    private $ancestorPath;
    private $tideURL;
    const FRAME_TIME = 11;
    /**
     * @var \Ancestor\FileDownloader\FileDownloader
     */
    private $imageDl;

    /**
     * @var ImageTemplate
     */
    private $tideTemplate;
    /**
     * @var ImageTemplateApplier
     */
    private $tideTemplateApplier;


    function __construct(CommandHandler $handler, $tideURL) {
        parent::__construct($handler, 'spin', 'Spins a [@user] inside of Tide™.');
        $this->tideURL = $tideURL;
        $this->tidePath = dirname(__DIR__, 2) . '/data/images/spin_gif/tide_empty.png';
        $this->ancestorPath = dirname(__DIR__, 2) . '/data/images/spin_gif/ancestor.png';

        $this->imageDl = new \Ancestor\FileDownloader\FileDownloader($this->client->getLoop());

        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;

        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . '/data/images/spin_gif/tide_template.json'));
        $this->tideTemplate = new ImageTemplate();
        $mapper->map($json, $this->tideTemplate);
        $this->tideTemplateApplier = new ImageTemplateApplier($this->tideTemplate);

    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        $commandHelper = new \Ancestor\CommandHandler\CommandHelper($message);
        $callbackObj = function ($image) use ($commandHelper) {
            $file = $this->SpinImage($image);
            if ($file === false) {
                $commandHelper->RespondWithEmbedImage($this->tideURL, 'How quickly the tide turns?');
                return;
            }
            $commandHelper->RespondWithAttachedFile($file, 'spin.gif');
        };
        try {
            $this->imageDl->DownloadUrlAsync($commandHelper->ImageUrlFromCommandArgs($args), $callbackObj);
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
            $commandHelper->RespondWithEmbedImage($this->tideURL, 'How quickly the tide turns?');
        }
    }

    /**
     * Spins image from $imageFile handler
     * @param resource $imageFile
     * @return bool|string
     */
    function SpinImage($imageFile) {
        if ($imageFile === false || ($imageToSpin = CommandHelper::ImageFromFileHandler($imageFile)) === false) {
            return false;
        }

        $imageToSpin = $this->tideTemplateApplier->applyTemplate($imageToSpin, imagecreatefrompng($this->tidePath), true);
        $frames = $this->GetImageRotationsWithAncestor($imageToSpin);

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

    function GetImageRotationsWithAncestor($image, int $rotations = 4, float $rotationAngle = 90) {
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