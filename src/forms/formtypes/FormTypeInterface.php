<?php

namespace BarrelStrength\Sprout\forms\formtypes;

interface FormTypeInterface
{
    public function getIncludeTemplates(): array;

    public function getCustomTemplatesFolder(): ?string;

    public function getRenderTemplatesFolder(): ?string;

    public function getDefaultTemplatesFolder(): ?string;

    //public function getFieldLayout(): FieldLayout;
}
