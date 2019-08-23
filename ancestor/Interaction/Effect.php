<?php

namespace Ancestor\Interaction;

use Ancestor\ImageTemplate\ImageTemplate;
use Ancestor\Interaction\Stats\Stats;
use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use function GuzzleHttp\Psr7\str;

class Effect extends AbstractEffect {

    /**
     * @var string|null
     */
    public $name = null;

    /**
     * Indicates whether or not effect gives hero positive(TRUE) or negative(FALSE) quirk.
     * @var bool|null
     */
    public $quirk_positive = null;

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

    /**
     * @param array|null $extraFields
     * @return MessageEmbed
     */
    public function getEmbedResponse(array $extraFields = null): MessageEmbed {
        $messageEmbed = new MessageEmbed();
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
            return ': ' . RandomDataProvider::GetInstance()->GetRandomPositiveQuirk();
        }
        if ($this->isNegativeQuirkEffect()) {
            return ': ' . RandomDataProvider::GetInstance()->GetRandomNegativeQuirk();
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