<?php
/**
 * Spin command.
 * Spins the Tide
 */

$args = ['tideURL' => json_decode(file_get_contents(dirname(__DIR__, 2) . '/config.json',
    true), true)['tideURL'],
    'ancestorPNG' => imagecreatefrompng(dirname(__DIR__, 2) . '/data/images/spin_gif/ancestor.png'),
    'tideCroppedPNG' => imagecreatefrompng(dirname(__DIR__, 2) . '/data/images/spin_gif/tide_empty.png'),
];

return (
new class($handler, $args) extends Ancestor\CommandHandler\Command {
    private $tideURL;
    private $ancestorPNG;
    private $tideCroppedPNG;
    private $spinPicX;
    private $spinPicY;
    private $spinGifFrameTime = 11;
    /**
     * @var \Ancestor\FileDownloader\FileDownloader
     */
    private $imageDl;

    function __construct(Ancestor\CommandHandler\CommandHandler $handler, $args) {
        parent::__construct($handler, 'spin', 'Spins a [@user] inside of Tide™.');
        $this->tideURL = $args['tideURL'];
        $this->ancestorPNG = $args['ancestorPNG'];
        $this->tideCroppedPNG = $args['tideCroppedPNG'];
        $this->spinPicX = imagesx($this->tideCroppedPNG);
        $this->spinPicY = imagesy($this->tideCroppedPNG);
        $this->imageDl = new \Ancestor\FileDownloader\FileDownloader($this->client->getLoop());
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
        $this->imageDl->DownloadUrlToStringAsync($commandHelper->ImageUrlFromCommandArgs($args), $callbackObj);
    }

    /**
     * Spins image from $imageFile handler
     * @param resource $imageFile
     * @return bool|string
     */
    function SpinImage($imageFile) {
        if ($imageFile === false) {
            return false;
        }
        if (($imageToSpin = imagecreatefromstring(fread($imageFile, filesize(stream_get_meta_data($imageFile)['uri'])))) === false) {
            return false;
        }
        fclose($imageFile);

        $imageToSpin = $this->AddImageToTide($imageToSpin);
        $frames = $this->GetImageRotationsWithAncestor($imageToSpin);

        $animation = new GifCreator\AnimGif();
        $animation->create($frames, [$this->spinGifFrameTime]);

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
        for ($i = 1; $i < $rotations; $i++) {
            $rotated_image = imagerotate($image, $rotationAngle * $i, 0);
            imagecopy($rotated_image, $this->ancestorPNG, 0, 0, 0, 0, $this->spinPicX, $this->spinPicY);
            $res[] = $rotated_image;
        }
        imagecopy($image, $this->ancestorPNG, 0, 0, 0, 0, $this->spinPicX, $this->spinPicY);
        $res[] = $image;
        return array_reverse($res);
    }

    function AddImageToTide($image) {
        $canvas = imagecreatetruecolor($this->spinPicX, $this->spinPicY);
        $scaledImage = imagescale($image, 128, 128, IMG_NEAREST_NEIGHBOUR);
        imagecopy($canvas, $scaledImage, 129, 174, 0, 0, 128, 128);
        imagecopy($canvas, $this->tideCroppedPNG, 0, 0, 0, 0, $this->spinPicX, $this->spinPicY);
        imagedestroy($scaledImage);
        return $canvas;
    }


}
);