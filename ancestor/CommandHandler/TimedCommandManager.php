<?php

namespace Ancestor\CommandHandler;

use CharlotteDunois\Collect\Collection;
use CharlotteDunois\Yasmin\Client;
use CharlotteDunois\Yasmin\Models\Message;
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

    public function userIsInteracting(int $userId){
        return $this->interactingUsers->has($userId);
    }

    /**
     * @param Message $message
     * @param int $timeout
     * @param $data
     * @param int|null $userId
     */
    public function addInteraction(Message $message, int $timeout, $data, int $userId = null){
        if ($userId === null){
           $userId = $message->author->id;
        }
        $this->interactingUsers->set($userId, [
            'data' => $data,
            'timer' => $this->client->addTimer($timeout,
                function () use ($userId) {
                    $this->interactingUsers->delete($userId);
                }
            ),
            'channelId' => $message->channel->getId()
        ]);
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function getUserData(int $userId){
        return $this->interactingUsers->get($userId)['data'];
    }

    /**
     * @param int $userId
     * @return string
     */
    public function getUserChannelId(int $userId) : string {
        return $this->interactingUsers->get($userId)['channelId'];
    }

    public function deleteInteraction(int $userId){
        $this->client->cancelTimer($this->getTimerFromUserId($userId));
        $this->interactingUsers->delete($userId);
    }

    /**
     * @param int $userId
     * @return Timer
     */
    function getTimerFromUserId(int $userId) {
        return $this->interactingUsers->get($userId)['timer'];
    }

    /**
     * @param Message $message
     * @return bool
     */
    public function channelIsValid(Message $message){
        return $this->getUserChannelId($message->author->id) === $message->channel->getId();
    }
}