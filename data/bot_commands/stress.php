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
     * @var \Ancestor\ImageDownloader\ImageDownloader
     */
    private $imageDl;

    function __construct(Ancestor\CommandHandler\CommandHandler $handler, $stressURL, $croppedStressPic) {
        parent::__construct($handler, 'stress', 'Forces you or a [@user] to drink wine.');
        $this->stressURL = $stressURL;
        $this->croppedStressPic = $croppedStressPic;
        $this->CSPicX = imagesx($croppedStressPic);
        $this->CSPicY = imagesy($croppedStressPic);
        $this->imageDl = new \Ancestor\ImageDownloader\ImageDownloader();
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args): void {
        $commandHelper = new \Ancestor\CommandHandler\CommandHelper($message);
        $file = $this->addAvatarToStress($commandHelper->ImageUrlFromCommandArgs($args));
        if ($file === false) {
            $commandHelper->RespondWithEmbedImage($this->stressURL);
            return;
        }
        $commandHelper->RespondWithAttachedFile($file,'stress.png');
    }

    function addAvatarToStress(string $avatarUrl) {
        $avatar = $this->imageDl->GetImageFromURL($avatarUrl);
        if ($avatar === false) {
            return $avatar;
        }

        $canvas = imagecreatetruecolor($this->CSPicX, $this->CSPicY);

        $rotateAngle = 22;
        $rotatedAvatar = imagerotate($avatar, $rotateAngle, 0);

        $rAvatarX = 189;
        $rotatedAvatar = imagescale($rotatedAvatar, $rAvatarX, -1, IMG_NEAREST_NEIGHBOUR);
        $rAvatarY = imagesy($rotatedAvatar);

        imagecopy($canvas, $rotatedAvatar, -43, 61, 0, 0, $rAvatarX, $rAvatarY);
        imagecopy($canvas, $this->croppedStressPic, 0, 0, 0, 0, $this->CSPicX, $this->CSPicY);

        ob_start();
        imagepng($canvas);
        $result = ob_get_clean();

        imagedestroy($rotatedAvatar);
        imagedestroy($avatar);
        imagedestroy($canvas);

        return $result;

    }


}
);