<?php

namespace BarrelStrength\Sprout\mailer\components\elements\email\actions;

use BarrelStrength\Sprout\forms\FormsModule;
use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;

class ChangeEmailType extends ElementAction
{
    public function getTriggerLabel(): string
    {
        return Craft::t('sprout-module-mailer', 'Change Email Type');
    }

    public static function isDestructive(): bool
    {
        return true;
    }

    public function getTriggerHtml(): ?string
    {
        Craft::$app->getView()->registerJsWithVars(fn($type) => <<<JS
(() => {
    new Craft.ElementActionTrigger({
        type: $type,
        validateSelection: \$selectedItems => Garnish.hasAttr(\$selectedItems.find('.element'), 'data-savable'),
        activate: \$selectedItems => {
            const elementIds = \$selectedItems.map((index, element) => {
                return $(element).data('id');
            }).get();
            const slideout = new  Craft.CpScreenSlideout('sprout-module-mailer/email-types/change-email-type-slideout', {
                params: {
                    elementIds: elementIds
                }
            });
            
        },
    });
})();
JS, [static::class]);

        return null;
    }
}
