<?php

namespace Ancestor\Command;

use Ancestor\BotIO\BotIoInterface;
use Ancestor\BotIO\MessageInterface;
use Ancestor\BotIO\UserInterface;
use React\EventLoop\TimerInterface;

class TimedCommandManager {
    /**
     * @var array
     */
    protected array $interactingUsers = [];
    /**
     * @var BotIoInterface
     */
    protected BotIoInterface $client;

    public function __construct(BotIoInterface $client) {
        $this->client = $client;
    }

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function userIsInteracting(MessageInterface $message): bool {
        return array_key_exists($this->generateId($message), $this->interactingUsers);
    }

    /**
     * @param MessageInterface $message
     * @param int $timeout
     * @param $data
     * @param callable|null $callback
     */
    public function addInteraction(MessageInterface $message, int $timeout, $data, $callback = null) {
        $id = $this->generateId($message);
        $this->interactingUsers[$id] = [
            'data' => $data,
            'callback' => $callback,
        ];
        $this->interactingUsers[$id]['timer'] = $this->client->addTimer($timeout,
            function () use ($id, $callback) {
                if ($callback !== null) {
                    $callback();
                }
                unset($this->interactingUsers[$id]);
            }
        );
    }

    /**
     * @param MessageInterface $message
     * @return mixed
     */
    public function getUserData(MessageInterface $message) {
        return $this->interactingUsers[$this->generateId($message)]['data'];
    }

    public function deleteInteraction(MessageInterface $message) {
        $id = $this->generateId($message);
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
     * @return string
     */
    private function generateId(MessageInterface $message): string {
        $userId = $message->getAuthor()->getId();
        return $message->getChannel()->getId() . $userId;
    }

    public function refreshTimer(MessageInterface $message, int $timerTimeout) {
        $id = $this->generateId($message);
        $data = $this->interactingUsers[$id]['data'];
        $callback = $this->interactingUsers[$id]['callback'];
        $this->deleteInteraction($message);
        $this->addInteraction($message, $timerTimeout, $data, $callback);
    }

    public function updateData(MessageInterface $message, $data) {
        $this->interactingUsers[$this->generateId($message)]['data'] = $data;
    }

}