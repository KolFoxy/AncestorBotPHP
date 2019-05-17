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
use Ratchet\RFC6455\Messaging\Message;

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
            'You encounter a peace of writing! The consequences can be unforeseen...',
            ['book', 'fuckbooks', 'knowledge']);

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
    }

    function run(\CharlotteDunois\Yasmin\Models\Message $message, array $args) {
        if (empty($args) && !$this->interactingUsers->has($message->author->id)) {
            $curio = $this->curios[mt_rand(0, sizeof($this->curios) - 1)];
            $this->addNewInteraction($curio, $message->author->id);
            $message->reply('', ['embed' => $curio->getEmbedResponse($this->handler->prefix . $this->name)]);
            return;
        }
        if (!empty($args) && $this->interactingUsers->has($message->author->id)) {
            $actionName = implode(' ', $args);
            $curio = $this->getCurioFromUserId($message->author->id);
            $action = $curio->getActionIfValid($actionName);
            if ($action === false) {
                return;
            }
            $this->interactingUsers->delete($message->author->id);
            if ($action === true) {
                $message->reply('', ['embed' => $this->defaultEffect->getEmbedResponse()]);
                return;
            }

            $effect = $this->getRandomEffectFromAction($action);
            if (!$effect->hasImage()) {
                $message->reply('', ['embed' => $effect->getEmbedResponse()]);
                return;
            }

            $callbackObj = function ($imageHandler) use ($effect, $message) {
                $file = $this->getImageOnTemplate($imageHandler,
                    dirname(__DIR__, 2) . $effect->image,
                    $effect->imageTemplate);
                if ($file === false) {
                    $message->reply('', ['embed' => $effect->getEmbedResponse()]);
                    return;
                }
                $ch = new CommandHelper($message);
                $ch->RespondWithAttachedFile($file, 'book_effect.png', $effect->getEmbedResponse());
            };

            $this->fileDl->DownloadUrlAsync($message->author->getDisplayAvatarURL(null, 'png'), $callbackObj);

        }
    }

    function getCurioFromUserId(int $userId): Curio {
        return $this->interactingUsers->get($userId)['curio'];
    }

    function getRandomEffectFromAction(Action $action): Effect {
        return RandomDataProvider::GetInstance()->GetRandomData($action->effects);
    }

    function addNewInteraction(Curio $curio, int $userId) {
        $this->interactingUsers->set($userId, [
            'curio' => $curio,
            'timer' => $this->client->addTimer(self::INTERACT_TIMEOUT,
                function () use ($userId) {
                    $this->interactingUsers->delete($userId);
                }
            )
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