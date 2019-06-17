<?php

namespace Ancestor\Curio;

use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class Effect {

    const INVALID_EFFECT_DESCRIPTION_MSG = 'Invalid description type for an Effect.';

    /**
     * @var string
     * @required
     */
    public $name;

    /**
     * @var string|string[]
     */
    private $_description;

    /**
     * Indicates the amount of stress that effect gives hero.
     * @var int|null
     */
    public $stress_value = null;

    /**
     * Indicates whether or not effect gives hero positive(TRUE) or negative(FALSE) quirk.
     * @var bool|null
     */
    public $quirk_positive = null;

    /**
     * Path to the template.
     * @var string|null
     */
    public $imageTemplate = null;

    /**
     * Path to the image.
     * @var string|null
     */
    public $image = null;


    /**
     * @return bool
     */
    public function isPositiveStressEffect(): bool {
        return isset($this->stress_value) && $this->stress_value < 0;
    }

    /**
     * @return bool
     */
    public function isNegativeStressEffect(): bool {
        return isset($this->stress_value) && $this->stress_value > 0;
    }

    /**
     * @return bool
     */
    public function isPositiveQuirkEffect(): bool {
        return isset($this->quirk_positive) && $this->quirk_positive;
    }

    /**
     * @return bool
     */
    public function isNegativeQuirkEffect(): bool {
        return isset($this->quirk_positive) && !$this->quirk_positive;
    }

    public function hasImage() {
        return isset($this->image) && isset($this->imageTemplate);
    }

    /**
     * @param array|null $extraFields
     * @return MessageEmbed
     */
    public function getEmbedResponse(array $extraFields = null): MessageEmbed {
        $messageEmbed = new MessageEmbed();
        $messageEmbed->setColor(DEFAULT_EMBED_COLOR);
        $messageEmbed->setTitle('***' . $this->name . $this->getTitleExtra() . '***');
        $messageEmbed->setDescription($this->getDescription());
        if (!empty($extraFields)) {
            foreach ($extraFields as $field) {
                $messageEmbed->addField($field['title'], $field['value']);
            }
        }
        return $messageEmbed;
    }

    private function getTitleExtra(): string {
        if ($this->isPositiveQuirkEffect()) {
            return ': ' . RandomDataProvider::GetInstance()->GetRandomPositiveQuirk();
        }
        if ($this->isNegativeQuirkEffect()) {
            return ': ' . RandomDataProvider::GetInstance()->GetRandomNegativeQuirk();
        }
        return '';
    }

    /**
     * @return string Returns description param or a random one from its array.
     */
    public function getDescription(): string {
        if (!is_array($this->_description)) {
            return $this->_description;
        }
        return $this->_description[mt_rand(0, sizeof($this->_description) - 1)];
    }

    /**
     * @param mixed $description Accepts either an array or a string
     * @throws \Exception
     */
    public function setDescription($description) {
        if (!is_string($description)) {
            if (!is_array($description)) {
                throw new \Exception(self::INVALID_EFFECT_DESCRIPTION_MSG);
            }
            foreach ($description as $item) {
                if (!is_string($item)) {
                    throw new \Exception(self::INVALID_EFFECT_DESCRIPTION_MSG);
                }
            }
        }
        $this->_description = $description;
    }
}
