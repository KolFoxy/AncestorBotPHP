<?php

namespace Ancestor\Command;

use Ancestor\BotIO\BotIoInterface;
use Ancestor\BotIO\MessageInterface;
use Ancestor\BotIO\UserInterface;
use React\EventLoop\TimerInterface;

class TimedCommandManager {
    /**
     * @var UserInterface[]
     */
    private array $interactingUsers = [];
    /**
     * @var BotIoInterface
     */
    private BotIoInterface $client;

    public function __construct(BotIoInterface $client) {
        $this->client = $client;
    }

    /**
     * @param MessageInterface $message
     * @param int|null $userId
     * @return bool
     */
    public function userIsInteracting(MessageInterface $message, int $userId = null): bool {
        return array_key_exists($this->generateId($message, $userId), $this->interactingUsers);
    }

    /**
     * @param MessageInterface $message
     * @param int $timeout
     * @param $data
     * @param int|null $userId
     * @param callable|null $onTimeout
     */
    public function addInteraction(MessageInterface $message, int $timeout, $data, int $userId = null, callable $onTimeout = null) {
        $id = $this->generateId($message, $userId);
        $this->interactingUsers[$id] = [
            'data' => $data,
            'timer' => $this->client->addTimer($timeout,
                function () use ($id, $onTimeout) {
                    if ($onTimeout !== null) {
                        $onTimeout();
                    }
                    unset($this->interactingUsers[$id]);
                }
            ),
        ];
    }

    /**
     * @param MessageInterface $message
     * @param int|null $userId ;
     * @return mixed
     */
    public function getUserData(MessageInterface $message, int $userId = null) {
        return $this->interactingUsers[$this->generateId($message, $userId)]['data'];
    }

    public function deleteInteraction(MessageInterface $message, int $userId = null) {
        $id = $this->generateId($message, $userId);
        if (!array_key_exists($id, $this->interactingUsers)) {
            return;
        }

        $this->client->cancelTimer($this->getTimer($id));
        unset($this->interactingUsers[$id]);
    }

    /**
     * @param string $id
     * @return TimerInterface
     */
    function getTimer(string $id) {
        return $this->interactingUsers[$id]['timer'];
    }

    /**
     * Generates id string from channel id and user id.
     * @param MessageInterface $message
     * @param int|null $userId
     * @return string
     */
    private function generateId(MessageInterface $message, int $userId = null): string {
        if ($userId === null) {
            $userId = $message->getAuthor()->getId();
        }
        return $message->getChannel()->getId() . $userId;
    }

    public function refreshTimer(MessageInterface $message, int $timerTimeout) {
        $id = $this->generateId($message);
        $this->client->cancelTimer($this->getTimer($id));
        $value = $this->interactingUsers[$id];
        $value['timer'] = $this->client->addTimer($timerTimeout,
            function () use ($id) {
                unset($this->interactingUsers[$id]);
            }
        );
        $this->interactingUsers[$id] = $value;
    }

    public function updateData(MessageInterface $message, $data) {
        $id = $this->generateId($message);
        $value = $this->interactingUsers[$id];
        $value['data'] = $data;
        $this->interactingUsers[$id] = $value;
    }

}