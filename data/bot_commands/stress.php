<?php
/**
 * Stress command.
 * Makes user drink wine
 */
$stressURL = json_decode(file_get_contents(dirname(__DIR__, 2) . '/config.json', true), true)['stressURL'];
$croppedStressPic = imagecreatefrompng(dirname(__DIR__, 2) . '/data/images/stress_cropped.png');

return (
new class($handler, $stressURL, $croppedStressPic) extends Ancestor\CommandHandler\Command {
    private $stressURL;
    private $croppedStressPic;
    private $CSPicX;
    private $CSPicY;
    /**
     * @var \Ancestor\FileDownloader\FileDownloader
     */
    private $imageDl;

    function __construct(Ancestor\CommandHandler\CommandHandler $handler, $stressURL, $croppedStressPic) {
        parent::__construct($handler, 'stress', 'Forces you or a [@user] to drink wine.');
        $this->stressURL = $stressURL;
        $this->croppedStressPic = $croppedStressPic;
        $this->CSPicX = imagesx($croppedStressPic);
        $this->CSPicY = imagesy($croppedStressPic);
        $this->imageDl = new \Ancestor\FileDownloader\FileDownloader($this->client->getLoop());
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        $commandHelper = new \Ancestor\CommandHandler\CommandHelper($message);
        $callbackObj = function ($image) use ($commandHelper) {
            $file = $this->addImageToStress($image);
            if ($file === false) {
                $commandHelper->RespondWithEmbedImage($this->stressURL);
                return;
            }
            $commandHelper->RespondWithAttachedFile($file, 'stress.png');
        };

        $this->imageDl->DownloadUrlToStringAsync($commandHelper->ImageUrlFromCommandArgs($args), $callbackObj);
    }

    /**
     * Creates stress image from $imageFile handler
     * @param resource $imageFile
     * @return bool|string
     */
    function addImageToStress($imageFile) {
        if ($imageFile === false) {
            return false;
        }
        if (($imageRes = imagecreatefromstring(fread($imageFile, filesize(stream_get_meta_data($imageFile)['uri'])))) === false) {
            return false;
        }
        fclose($imageFile);

        $canvas = imagecreatetruecolor($this->CSPicX, $this->CSPicY);

        $rotateAngle = 22;
        $rotatedAvatar = imagerotate($imageRes, $rotateAngle, 0);

        $rAvatarX = 189;
        $rotatedAvatar = imagescale($rotatedAvatar, $rAvatarX, -1, IMG_NEAREST_NEIGHBOUR);
        $rAvatarY = imagesy($rotatedAvatar);

        imagecopy($canvas, $rotatedAvatar, -43, 61, 0, 0, $rAvatarX, $rAvatarY);
        imagecopy($canvas, $this->croppedStressPic, 0, 0, 0, 0, $this->CSPicX, $this->CSPicY);

        ob_start();
        imagepng($canvas);
        $result = ob_get_clean();

        imagedestroy($rotatedAvatar);
        imagedestroy($imageRes);
        imagedestroy($canvas);

        return $result;

    }


}
);