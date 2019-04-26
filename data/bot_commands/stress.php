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

    function __construct(Ancestor\CommandHandler\CommandHandler $handler, $stressURL, $croppedStressPic) {
        parent::__construct($handler, 'stress', 'Makes you drink wine');
        $this->stressURL = $stressURL;
        $this->croppedStressPic = $croppedStressPic;
        $this->CSPicX = imagesx($croppedStressPic);
        $this->CSPicY = imagesy($croppedStressPic);
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args): void {
        $file = $this->addAvatarToStress(\Ancestor\CommandHandler\CommandHelper::ImageUrlFromCommandArgs($args, $message));
        if ($file === false) {
            $embedResponse = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
            $embedResponse->setImage($this->stressURL);
            $message->channel->send('', array('embed' => $embedResponse));
            return;
        }
        //Had to double-array, due to bug in the Yasmin\DataHelpers spamming warnings when dealing with binary data
        $message->channel->send('', array('files' => array(array('data' => $file, 'name' => 'stress.png'))));
    }

    function addAvatarToStress(string $avatarUrl) {
        $avatar = \Ancestor\CommandHandler\CommandHelper::ImageFromURL($avatarUrl);
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