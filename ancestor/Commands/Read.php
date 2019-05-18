<?php

namespace Ancestor\Commands;

use Ancestor\CommandHandler\Command;
use Ancestor\CommandHandler\CommandHandler;
use Ancestor\CommandHandler\CommandHelper;
use Ancestor\Curio\Action;
use Ancestor\Curio\Curio;
use const Ancestor\Curio\DEFAULT_EMBED_COLOR;
use Ancestor\Curio\Effect;
use Ancestor\FileDownloader\FileDownloader;
use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\ImageTemplate\ImageTemplateApplier;
use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Collect\Collection;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use CharlotteDunois\Yasmin\Models\Message;

class Read extends Command {

    /**
     * Array of writing curios.
     * @var Curio[]
     */
    private $curios;

    /**
     * @var Effect
     */
    private $defaultEffect;

    /**
     * @var Collection
     */
    private $interactingUsers;

    /**
     * @var FileDownloader
     */
    private $fileDl;

    const INTERACT_TIMEOUT = 60.0;

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

        $this->defaultEffect = new Effect();
        $this->defaultEffect->name = "Nothing happened.";
        $this->defaultEffect->description = "You choose to walk away in peace.";

        $this->interactingUsers = new Collection();
        $this->fileDl = new FileDownloader($this->client->getLoop());
    }

    function run(Message $message, array $args) {
        if (empty($args) && !$this->interactingUsers->has($message->author->id)) {
            $curio = $this->curios[mt_rand(0, sizeof($this->curios) - 1)];
            $this->addNewInteraction($curio, $message->author->id, $message->channel->getId());
            $message->reply('', ['embed' => $curio->getEmbedResponse($this->handler->prefix . $this->name)]);
            return;
        }
        if (!empty($args) && $this->interactingUsers->has($message->author->id)
            && $message->channel->getId() === $this->getChannelIdFromUserId($message->author->id)) {
            $actionName = implode(' ', $args);
            $curio = $this->getCurioFromUserId($message->author->id);
            $action = $curio->getActionIfValid($actionName);
            if ($action === false) {
                return;
            }
            $this->client->cancelTimer($this->getTimerFromUserId($message->author->id));
            $this->interactingUsers->delete($message->author->id);

            if ($action === true) {
                $message->reply('', ['embed' => $this->defaultEffect->getEmbedResponse()]);
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
            $template = $mapper->map($json, new ImageTemplate());
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

    function getCurioFromUserId(int $userId): Curio {
        return $this->interactingUsers->get($userId)['curio'];
    }

    function getChannelIdFromUserId(int $userId): string {
        return $this->interactingUsers->get($userId)['channelId'];
    }


    /**
     * @param int $userId
     * @return \React\EventLoop\Timer\Timer
     */
    function getTimerFromUserId(int $userId) {
        return $this->interactingUsers->get($userId)['timer'];
    }

    function getRandomEffectFromAction(Action $action): Effect {
        return RandomDataProvider::GetInstance()->GetRandomData($action->effects);
    }

    function addNewInteraction(Curio $curio, int $userId, string $channelId) {
        $this->interactingUsers->set($userId, [
            'curio' => $curio,
            'timer' => $this->client->addTimer(self::INTERACT_TIMEOUT,
                function () use ($userId) {
                    $this->interactingUsers->delete($userId);
                }
            ),
            'channelId' => $channelId
        ]);
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
        $canvas = $tA->applyTemplate($imageSrc, $imageTemplate);

        ob_start();
        imagepng($canvas);
        $result = ob_get_clean();
        imagedestroy($canvas);

        return $result;
    }

}