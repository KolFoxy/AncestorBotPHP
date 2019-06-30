<?php

namespace Ancestor\CommandHandler;

use CharlotteDunois\Collect\Collection;
use CharlotteDunois\Yasmin\Client;
use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use React\EventLoop\Timer\Timer;

class TimedCommandManager {
    /**
     * @var Collection
     */
    private $interactingUsers;
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client) {
        $this->interactingUsers = new Collection();
        $this->client = $client;
    }

    /**
     * @param Message $message
     * @param int|null $userId
     * @return bool
     */
    public function userIsInteracting(Message $message, int $userId = null) {
        return $this->interactingUsers->has($this->generateId($message, $userId));
    }

    /**
     * @param Message $message
     * @param int $timeout
     * @param $data
     * @param int|null $userId
     */
    public function addInteraction(Message $message, int $timeout, $data, int $userId = null) {
        $id = $this->generateId($message, $userId);
        $this->interactingUsers->set($id, [
            'data' => $data,
            'timer' => $this->client->addTimer($timeout,
                function () use ($id) {
                    $this->interactingUsers->delete($id);
                }
            ),
            'channelId' => $message->channel->getId()
        ]);
    }

    /**
     * @param Message $message
     * @param int|null $userId ;
     * @return mixed
     */
    public function getUserData(Message $message, int $userId = null) {
        return $this->interactingUsers->get($this->generateId($message, $userId))['data'];
    }


    /**
     * @param Message $message
     * @param int|null $userId
     * @return string
     */
    public function getUserChannelId(Message $message, int $userId = null): string {
        return $this->interactingUsers->get($this->generateId($message, $userId))['channelId'];
    }

    public function deleteInteraction(Message $message, int $userId = null) {
        $id = $this->generateId($message, $userId);
        $this->client->cancelTimer($this->getTimer($id));
        $this->interactingUsers->delete($id);
    }

    /**
     * @param string $id
     * @return Timer
     */
    function getTimer(string $id) {
        return $this->interactingUsers->get($id)['timer'];
    }

    /**
     * Generates id string from channel id and user id.
     * @param Message $message
     * @param int|null $userId
     * @return string
     */
    private function generateId(Message $message, int $userId = null): string {
        if ($userId === null) {
            $userId = $message->author->id;
        }
        return $message->channel->getId() . $userId;
    }

    public function refreshTimer(Message $message, int $timerTimeout) {
        $id = $this->generateId($message);
        $this->client->cancelTimer($this->getTimer($id));
        $value = $this->interactingUsers->get($id);
        $value['timer'] = $this->client->addTimer($timerTimeout,
            function () use ($id) {
                $this->interactingUsers->delete($id);
            }
        );
        $this->interactingUsers->set($id, $value);

    }
}