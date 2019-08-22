<?php

namespace Ancestor\CommandHandler;

use CharlotteDunois\Collect\Collection;
use CharlotteDunois\Yasmin\Client;
use CharlotteDunois\Yasmin\Models\Message;
use React\EventLoop\TimerInterface;

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
     * @param Message|string $idSource
     * @return bool
     */
    public function userIsInteracting($idSource) {
        return $this->interactingUsers->has($this->idSourceToId($idSource));
    }

    /**
     * @param $idSource
     * @return string|Message
     */
    protected function idSourceToId($idSource) {
        return is_string($idSource) ? $idSource : $this->generateId($idSource->channel->getId(), $idSource->author->id);
    }

    /**
     * @param string|Message $idSource
     * @param int $timeout
     * @param $data
     * @param callable|null $onTimeout
     */
    public function addInteraction($idSource, int $timeout, $data, callable $onTimeout = null) {
        $id = $this->idSourceToId($idSource);
        $this->interactingUsers->set($id, [
            'data' => $data,
            'timer' => $this->client->addTimer($timeout,
                function () use ($id, $onTimeout) {
                    if ($onTimeout !== null) {
                        $onTimeout();
                    }
                    $this->interactingUsers->delete($id);
                }
            ),
        ]);
    }

    /**
     * @param Message|string $idSource
     * @return mixed
     */
    public function getUserData($idSource) {
        return $this->interactingUsers->get($this->idSourceToId($idSource))['data'];
    }

    /**
     * @param Message|string $idSource
     */
    public function deleteInteraction($idSource) {
        $id = $this->idSourceToId($idSource);
        if (!$this->interactingUsers->has($id)) {
            return;
        }
        $this->client->cancelTimer($this->getTimer($id));
        $this->interactingUsers->delete($id);
    }

    /**
     * @param string $id
     * @return TimerInterface
     */
    function getTimer(string $id) {
        return $this->interactingUsers->get($id)['timer'];
    }

    /**
     * Generates id string from channel id and user id.
     * @param $channelId
     * @param $userId
     * @return string
     */
    public function generateId($channelId, $userId): string {
        return (string)$channelId . (string)$userId;
    }

    /**
     * @param Message|string $idSource
     * @param int $timerTimeout
     */
    public function refreshTimer($idSource, int $timerTimeout) {
        $id = is_string($idSource) ? $idSource : $this->generateId($idSource->channel->getId(), $idSource->author->id);
        $this->client->cancelTimer($this->getTimer($id));
        $value = $this->interactingUsers->get($id);
        $value['timer'] = $this->client->addTimer($timerTimeout,
            function () use ($id) {
                $this->interactingUsers->delete($id);
            }
        );
        $this->interactingUsers->set($id, $value);

    }

    /**
     * @param Message|string $idSource
     * @param $data
     */
    public function updateData($idSource, $data) {
        $id = $this->idSourceToId($idSource);
        $value = $this->interactingUsers->get($id);
        $value['data'] = $data;
        $this->interactingUsers->set($id, $value);
    }

}