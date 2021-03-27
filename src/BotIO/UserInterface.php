<?php

namespace Ancestor\BotIO;

interface UserInterface {
    public function getUsername() : string;
    public function getAvatarUrl() : string;
}