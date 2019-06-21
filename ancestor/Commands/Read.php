<?php

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;
use Ancestor\CommandHandler\CommandHelper;
use Ancestor\CommandHandler\TimedCommandManager;
use Ancestor\FileDownloader\FileDownloader;
use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\ImageTemplate\ImageTemplateApplier;
use Ancestor\Interaction\Action;
use Ancestor\Interaction\Curio;
use Ancestor\Interaction\Effect;
use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Models\Message;

class Read extends Command {

    /**
     * Array of writing curios.
     * @var Curio[]
     */
    private $curios;
    /**
     * @var TimedCommandManager
     */
    private $manager;
    /**
     * @var FileDownloader
     */
    private $fileDl;

    const TIMEOUT = 60.0;

    function __construct(CommandHandler $handler) {
        parent::__construct($handler, 'read',
            'Interact with writing curios.',
            ['book', 'heckbooks', 'knowledge']);

        $mapper = new \JsonMapper();
        $json = json_decode(file_get_contents(dirname(__DIR__, 2) . '/data/writings.json'));
        $mapper->bExceptionOnMissingData = true;
        $this->curios = $mapper->mapArray(
            $json, [], Curio::class
        );

        $this->manager = new TimedCommandManager($this->client);
        $this->fileDl = new FileDownloader($this->client->getLoop());
    }

    function run(Message $message, array $args) {
        if (empty($args) && !$this->manager->userIsInteracting($message)) {
            $curio = $this->curios[mt_rand(0, sizeof($this->curios) - 1)];
            $this->manager->addInteraction($message, self::TIMEOUT, $curio);
            $message->reply('', ['embed' => $curio->getEmbedResponse($this->handler->prefix . $this->name)]);
            return;
        }
        if (!empty($args) && $this->manager->userIsInteracting($message)) {
            $actionName = implode(' ', $args);
            $curio = $this->getCurio($message);
            $action = $curio->getActionIfValid($actionName);

            if ($action === false) {
                return;
            }

            $this->manager->deleteInteraction($message);

            //Action is the default action.
            if ($action === true) {
                $message->reply('', ['embed' => $curio->defaultAction()->effects[0]->getEmbedResponse()]);
                return;
            }

            $effect = $this->getRandomEffectFromAction($action);
            $extraEmbedFields = null;
            if ($effect->isNegativeStressEffect() && $effect->stress_value >= 100) {
                $resolve = RandomDataProvider::GetInstance()->GetRandomResolve();
                $extraEmbedFields = [
                    [
                        'title' => $message->author->username . '\'s resolve is tested... **' . $resolve['name'] . '**',
                        'value' => '***' . $resolve['quote'] . '***'
                    ]
                ];
            }

            if (!$effect->hasImage()) {
                $message->reply('', ['embed' => $effect->getEmbedResponse($extraEmbedFields)]);
                return;
            }

            $callbackObj = function ($imageHandler) use ($effect, $message, $extraEmbedFields) {
                $this->onImageDownloadResponse($imageHandler, $effect, $message, $extraEmbedFields);
            };

            $this->fileDl->DownloadUrlAsync($message->author->getDisplayAvatarURL(null, 'png'), $callbackObj);

        }
    }

    /**
     * @param $imageHandler
     * @param Effect $effect
     * @param Message $message
     * @param array|null $extraEmbedFields
     */
    function onImageDownloadResponse($imageHandler, Effect $effect, Message $message, array $extraEmbedFields = null) {
        $mapper = new \JsonMapper();
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
            $message->reply('', ['embed' => $effect->getEmbedResponse($extraEmbedFields)]);
            return;
        }

        $ch = new CommandHelper($message);
        $ch->RespondWithAttachedFile($file, 'book_effect.png', $effect->getEmbedResponse($extraEmbedFields),
            $message->author->__toString());

    }

    function getCurio(Message $message): Curio {
        return $this->manager->getUserData($message);
    }

    function getRandomEffectFromAction(Action $action): Effect {
        return RandomDataProvider::GetInstance()->GetRandomData($action->effects);
    }


    /**
     * @param resource $imageSrcFileHandler
     * @param string $imageTemplatePath
     * @param ImageTemplate $template
     * @return string
     */
    function getImageOnTemplate($imageSrcFileHandler, string $imageTemplatePath, ImageTemplate $template): string {
        $imageSrc = CommandHelper::ImageFromFileHandler($imageSrcFileHandler);
        $imageTemplate = imagecreatefrompng($imageTemplatePath);
        $tA = new ImageTemplateApplier($template);
        $canvas = $tA->applyTemplate($imageSrc, $imageTemplate, true);

        ob_start();
        imagepng($canvas);
        $result = ob_get_clean();
        imagedestroy($canvas);

        return $result;
    }

}