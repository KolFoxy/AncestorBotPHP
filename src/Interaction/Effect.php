<?php

namespace Ancestor\Interaction;

use Ancestor\BotIO\EmbedInterface;
use Ancestor\BotIO\EmbedObject;
use Ancestor\Interaction\Stats\Stats;
use Ancestor\RandomData\RandomDataProvider;

class Effect extends AbstractEffect {

    /**
     * @var string|null
     */
    public ?string $name = null;

    /**
     * Indicates whether or not effect gives hero positive(TRUE) or negative(FALSE) quirk.
     * @var bool|null
     */
    public ?bool $quirkIsPositive = null;

    /**
     * @return bool
     */
    public function isPositiveQuirkEffect(): bool {
        return isset($this->quirkIsPositive) && $this->quirkIsPositive;
    }

    /**
     * @return bool
     */
    public function isNegativeQuirkEffect(): bool {
        return isset($this->quirkIsPositive) && !$this->quirkIsPositive;
    }

    /**
     * @param array|null $extraFields
     * @return EmbedInterface
     */
    public function getEmbedResponse(array $extraFields = null): EmbedInterface {
        $messageEmbed = new EmbedObject();
        $messageEmbed->setColor(DEFAULT_EMBED_COLOR);
        $messageEmbed->setTitle('***' . $this->name . $this->getTitleExtra() . '***');
        $messageEmbed->setDescription(self::parseRandomNum($this->getDescription()));
        if (!empty($extraFields)) {
            foreach ($extraFields as $field) {
                $messageEmbed->addField($field['title'], $field['value']);
            }
        }
        return $messageEmbed;
    }

    protected function getTitleExtra(): string {
        if ($this->isPositiveQuirkEffect()) {
            return ': ' . RandomDataProvider::getInstance()->getRandomPositiveQuirk();
        }
        if ($this->isNegativeQuirkEffect()) {
            return ': ' . RandomDataProvider::getInstance()->getRandomNegativeQuirk();
        }
        return '';
    }


    public function getApplicationResult(AbstractLivingBeing $target, int &$healthResult, int &$stressResult) {
        $healthResult = (int)($this->getHealthValue() *
            ($this->isHealEffect()
                ? $target->statManager->getValueMod(Stats::HEAL_RECEIVED_MOD)
                : $target->statManager->getValueMod(Stats::DAMAGE_TAKEN_MOD)));
        $stressResult = (int)($this->getStressValue() *
            ($this->isPositiveStressEffect()
                ? $target->statManager->getValueMod(Stats::STRESS_HEAL_MOD)
                : $target->statManager->getValueMod(Stats::STRESS_MOD)));
    }
}