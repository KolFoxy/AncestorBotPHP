<?php
/**
 * Stress command.
 * Makes user drink wine
 */

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command as Command;
use Ancestor\CommandHandler\CommandHandler as CommandHandler;
use Ancestor\CommandHandler\CommandHelper;
use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\ImageTemplate\ImageTemplateApplier;

class Stress extends Command {
    private $stressURL;
    private $stressPicPath;
    private $CSPicX = 226;
    private $CSPicY = 223;
    /**
     * @var ImageTemplate
     */
    private $defaultTemplate;

    /**
     * @var ImageTemplateApplier
     */
    private $templateApplier;
    /**
     * @var \Ancestor\FileDownloader\FileDownloader
     */
    private $imageDl;

    const STRESS_ROTATE_ANGLE = 22;

    function __construct(CommandHandler $handler, $stressURL) {
        parent::__construct($handler, 'stress', 'Forces you or a [@user] to drink wine.');
        $this->stressURL = $stressURL;
        $this->stressPicPath = dirname(__DIR__,2).'/data/images/stress_cropped.png';
        $this->imageDl = new \Ancestor\FileDownloader\FileDownloader($this->client->getLoop());

        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . '/data/images/stress_template.json'));
        $mapper = new \JsonMapper();
        $this->defaultTemplate = new ImageTemplate();
        $mapper->bExceptionOnMissingData = true;
        $mapper->map($json, $this->defaultTemplate);
        $this->templateApplier = new ImageTemplateApplier($this->defaultTemplate);

    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        $commandHelper = new CommandHelper($message);
        $callbackObj = function ($image) use ($commandHelper) {
            $file = $this->addImageToStress($image);
            if ($file === false) {
                $commandHelper->RespondWithEmbedImage($this->stressURL);
                return;
            }
            $commandHelper->RespondWithAttachedFile($file, 'stress.png');
        };

        try {
            $this->imageDl->DownloadUrlAsync($commandHelper->ImageUrlFromCommandArgs($args), $callbackObj);
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
            $commandHelper->RespondWithEmbedImage($this->stressURL);
        }
    }

    /**
     * Creates stress image from $imageFile handler
     * @param resource $imageFile
     * @return bool|string
     */
    function addImageToStress($imageFile) {
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
