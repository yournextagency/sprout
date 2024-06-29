<?php

namespace BarrelStrength\Sprout\meta\components\meta;

use BarrelStrength\Sprout\meta\metadata\MetaType;
use Craft;

class CanonicalUrlMetaType extends MetaType
{
    protected ?string $canonical = null;

    public static function displayName(): string
    {
        return Craft::t('sprout-module-meta', 'Canonical URL');
    }

    public function attributes(): array
    {
        $attributes = parent::attributes();
        $attributes[] = 'canonical';

        return $attributes;
    }

    public function getCanonical(): ?string
    {
        if ($this->canonical || $this->metadata->getRawDataOnly()) {
            return $this->canonical ?? $this->metadata->getCanonical();
        }

        return $this->metadata->getCanonical();
    }

    public function setCanonical(?string $value): void
    {
        $this->canonical = $value;
    }

    public function getHandle(): string
    {
        return 'canonical';
    }
}
