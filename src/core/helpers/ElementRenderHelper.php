<?php

namespace BarrelStrength\Sprout\core\helpers;

use BarrelStrength\Sprout\core\twig\TemplateHelper;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\web\View;
use Twig\Error\LoaderError as TwigLoaderError;
use Twig\Markup;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use Craft;

class ElementRenderHelper
{
    public static function isSproutTemplateRoot(string $template): bool
    {
        $sproutSiteTemplateRoot = TemplateHelper::getSproutSiteTemplateRoot();

        return
            StringHelper::startsWith($template, '@Sprout/TemplateRoot') ||
            strpos($template, $sproutSiteTemplateRoot) === 0;
    }

    public static function getSproutTemplatePath(string $template): string
    {
        $sproutSiteTemplateRoot = TemplateHelper::getSproutSiteTemplateRoot();

        if (StringHelper::startsWith($template, '@Sprout/TemplateRoot') || StringHelper::startsWith($template, $sproutSiteTemplateRoot)) {
            return $sproutSiteTemplateRoot;
        }

        return $template;
    }
}
