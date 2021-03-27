<?php

namespace Ancestor\BotIO;

interface EmbedInterface {
    public function addField (string $title, string $body);
    public function getFields() : array;
    public function setFooter(string $footerText, ?string $footerIconUrl, ?string $footerProxyIcon);
}