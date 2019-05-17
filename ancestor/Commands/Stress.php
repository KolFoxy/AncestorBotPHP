<?php
/**
 * Stress command.
 * Makes user drink wine
 */

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command as Command;
use Ancestor\CommandHandler\CommandHandler as CommandHandler;
use Ancestor\CommandHandler\CommandHelper;

class Stress extends Command {
    private $stressURL;
    private $croppedStressPic;
    private $CSPicX;
    private $CSPicY;
    /**
     * @var \Ancestor\FileDownloader\FileDownloader
     */
    private $imageDl;

    function __construct(CommandHandler $handler, $stressURL, $croppedStressPNG) {
        parent::__construct($handler, 'stress', 'Forces you or a [@user] to drink wine.');
        $this->stressURL = $stressURL;
        $this->croppedStressPic = imagecreatefrompng($croppedStressPNG);
        $this->CSPicX = imagesx($this->croppedStressPic);
        $this->CSPicY = imagesy($this->croppedStressPic);
        $this->imageDl = new \Ancestor\FileDownloader\FileDownloader($this->client->getLoop());
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

        $canvas = imagecreatetruecolor($this->CSPicX, $this->CSPicY);

        $rotateAngle = 22;
        $rotatedAvatar = imagerotate($imageRes, $rotateAngle, 0);
        imagedestroy($imageRes);

        $rAvatarX = 189;
        $rotatedAvatar = imagescale($rotatedAvatar, $rAvatarX, -1, IMG_NEAREST_NEIGHBOUR);
        $rAvatarY = imagesy($rotatedAvatar);

        imagecopy($canvas, $rotatedAvatar, -43, 61, 0, 0, $rAvatarX, $rAvatarY);
        imagedestroy($rotatedAvatar);

        imagecopy($canvas, $this->croppedStressPic, 0, 0, 0, 0, $this->CSPicX, $this->CSPicY);

        ob_start();
        imagepng($canvas);
        $result = ob_get_clean();

        imagedestroy($canvas);

        return $result;
    }


}
