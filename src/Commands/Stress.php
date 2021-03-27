<?php
/**
 * Stress command.
 * Makes user drink wine
 */

namespace Ancestor\Commands;

use Ancestor\BotIO\MessageInterface;
use Ancestor\Command\Command as Command;
use Ancestor\Command\CommandHandler as CommandHandler;
use Ancestor\Command\CommandHelper;
use Ancestor\FileDownloader\FileDownloader;
use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\ImageTemplate\ImageTemplateApplier;
use JsonMapper;

class Stress extends Command {
    private $stressURL;
    private string $stressPicPath;
    /**
     * @var ImageTemplate
     */
    private ImageTemplate $defaultTemplate;

    /**
     * @var ImageTemplateApplier
     */
    private ImageTemplateApplier $templateApplier;
    /**
     * @var FileDownloader
     */
    private FileDownloader $fileDownloader;

    const STRESS_ROTATE_ANGLE = 22;

    function __construct(CommandHandler $handler, $stressURL) {
        parent::__construct($handler, 'stress', 'Forces you or a [@user] to drink wine.');
        $this->stressURL = $stressURL;
        $this->stressPicPath = dirname(__DIR__,2).'/data/images/stress_cropped.png';
        $this->fileDownloader = new FileDownloader($this->handler->client->getLoop());

        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . '/data/images/stress_template.json'));
        $mapper = new JsonMapper();
        $this->defaultTemplate = new ImageTemplate();
        $mapper->bExceptionOnMissingData = true;
        $mapper->map($json, $this->defaultTemplate);
        $this->templateApplier = new ImageTemplateApplier($this->defaultTemplate);

    }

    function run(MessageInterface $message, array $args) {
        $callbackObj = function ($image) use ($message) {
            $stressedImage = $this->stressImage($image);
            if ($stressedImage === false) {
                $message->replyWithEmbedImage('','',$this->stressURL);
                return;
            }
            $message->getChannel()->sendWithFile('','stress.png',$stressedImage);
        };

        try {
            $this->fileDownloader->DownloadUrlAsync(CommandHelper::ImageUrlFromCommandArgs($args, $message), $callbackObj);
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
            $message->replyWithEmbedImage('','',$this->stressURL);
        }
    }

    /**
     * Creates stress image from $imageFile handler
     * @param resource $imageFile
     * @return bool|string
     */
    function stressImage($imageFile) {
        if ($imageFile === false || ($imageRes = CommandHelper::ImageFromFileHandler($imageFile)) === false) {
            return false;
        }

        $imageScale = imagescale($imageRes, 250, 250, IMG_NEAREST_NEIGHBOUR);
        imagedestroy($imageRes);

        $rotatedAvatar = imagerotate($imageScale, self::STRESS_ROTATE_ANGLE, 0);
        imagedestroy($imageScale);

        $canvas = $this->templateApplier->applyTemplate($rotatedAvatar, imagecreatefrompng($this->stressPicPath),true);

        ob_start();
        imagepng($canvas);
        $result = ob_get_clean();

        imagedestroy($canvas);

        return $result;
    }


}
