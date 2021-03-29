<?php

namespace Ancestor\Commands;

use Ancestor\BotIO\MessageInterface;
use Ancestor\Command\Command;
use Ancestor\Command\CommandHandler;
use Ancestor\Command\CommandHelper;
use Ancestor\Command\TimedCommandManager;
use Ancestor\FileDownloader\FileDownloader;
use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\ImageTemplate\ImageTemplateApplier;

use Ancestor\Interaction\Curio;
use Ancestor\Interaction\Effect;
use Ancestor\Interaction\Stats\StressStateFactory;
use JsonMapper;

class Read extends Command {

    /**
     * Array of writing curios.
     * @var Curio[]
     */
    private array $curios;
    /**
     * @var TimedCommandManager
     */
    private TimedCommandManager $manager;
    /**
     * @var FileDownloader
     */
    private FileDownloader $fileDl;

    const TIMEOUT = 60.0;

    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'read',
            'Interact with writing curios.',
            ['book', 'heckbooks', 'knowledge']);

        $mapper = new JsonMapper();
        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . '/data/writings.json'));
        $mapper->bExceptionOnMissingData = true;
        $this->curios = $mapper->mapArray($json, [], Curio::class);

        $this->manager = new TimedCommandManager($this->handler->client);
        $this->fileDl = new FileDownloader($this->handler->client->getLoop());
    }

    function run(MessageInterface $message, array $args) {
        if (empty($args) && !$this->manager->userIsInteracting($message)) {
            $curio = $this->curios[mt_rand(0, sizeof($this->curios) - 1)];
            $this->manager->addInteraction($message, self::TIMEOUT, $curio);
            $message->reply('', $curio->getEmbedResponse($this->handler->prefix . $this->name));
            return;
        }
        if (!empty($args) && $this->manager->userIsInteracting($message)) {
            $actionName = implode(' ', $args);
            $curio = $this->getCurio($message);
            $action = $curio->getActionIfValid($actionName);
            if ($action === null) {
                return;
            }

            $this->manager->deleteInteraction($message);
            $effect = $action->getRandomEffect();
            $extraEmbedFields = null;
            if ($effect->isNegativeStressEffect() && $effect->stress_value >= 100) {
                $stressState = StressStateFactory::create();
                $extraEmbedFields = [
                    [
                        'title' => $message->getAuthor()->getUsername() . '\'s resolve is tested... **' . $stressState->name . '**',
                        'value' => '***' . $stressState->quote . '***',
                    ],
                ];
            }

            if (!$effect->hasImage()) {
                $message->reply('', $effect->getEmbedResponse($extraEmbedFields));
                return;
            }

            $callbackObj = function ($imageHandler) use ($effect, $message, $extraEmbedFields) {
                $this->onImageDownloadResponse($imageHandler, $effect, $message, $extraEmbedFields);
            };

            $this->fileDl->downloadUrlAsync($message->getAuthor()->getAvatarUrl(), $callbackObj);

        }
    }

    /**
     * @param $imageHandler
     * @param Effect $effect
     * @param MessageInterface $message
     * @param array|null $extraEmbedFields
     */
    function onImageDownloadResponse($imageHandler, Effect $effect, MessageInterface $message, array $extraEmbedFields = null) {
        $mapper = new JsonMapper();
        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . $effect->imageTemplate));
        try {
            $template = new ImageTemplate();
            $mapper->map($json, $template);
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
            return;
        }

        $file = $this->getImageOnTemplate($imageHandler,
            dirname(__DIR__, 2) . $effect->image,
            $template);

        if ($file === false) {
            $message->reply('', $effect->getEmbedResponse($extraEmbedFields));
            return;
        }

        $message->getChannel()->sendWithFile($message->getAuthor()->getMention(),'book_effect.png',$file, $effect->getEmbedResponse($extraEmbedFields));

    }

    function getCurio(MessageInterface $message): Curio {
        return $this->manager->getUserData($message);
    }


    /**
     * @param resource $imageSrcFileHandler
     * @param string $imageForTemplatePath
     * @param ImageTemplate $template
     * @return string
     */
    function getImageOnTemplate($imageSrcFileHandler, string $imageForTemplatePath, ImageTemplate $template): string {
        $imageSrc = CommandHelper::imageFromFileHandler($imageSrcFileHandler);
        $imageTemplate = imagecreatefrompng($imageForTemplatePath);
        $tA = new ImageTemplateApplier($template);
        $canvas = $tA->applyTemplate($imageSrc, $imageTemplate, true);

        ob_start();
        imagepng($canvas);
        $result = ob_get_clean();
        imagedestroy($canvas);

        return $result;
    }

}