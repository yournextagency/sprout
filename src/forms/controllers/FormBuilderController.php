<?php

namespace BarrelStrength\Sprout\forms\controllers;

use BarrelStrength\Sprout\forms\components\elements\FormElement;
use BarrelStrength\Sprout\forms\formfields\CustomFormField;
use BarrelStrength\Sprout\forms\formfields\FormFieldLayoutTab;
use BarrelStrength\Sprout\forms\FormsModule;
use BarrelStrength\Sprout\forms\formtypes\FormTypeHelper;
use BarrelStrength\Sprout\forms\migrations\helpers\FormContentTableHelper;
use Craft;
use craft\base\Element;
use craft\elements\conditions\users\UserCondition;
use craft\errors\WrongEditionException;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\models\FieldLayoutTab;
use craft\models\Site;
use craft\web\Controller as BaseController;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class FormBuilderController extends BaseController
{
    public function actionGetSubmissionFieldLayout(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $formId = Craft::$app->getRequest()->getRequiredBodyParam('formId');

        /** @var FormElement $form */
        $form = Craft::$app->getElements()->getElementById($formId, FormElement::class);
        $layout = $form->getFormBuilderSubmissionFieldLayout();

        return $this->asJson([
            'success' => true,
            'formId' => $formId,
            'layout' => $layout,
        ]);
    }

    public function actionGetFormTabSettingsHtml(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $formId = Craft::$app->getRequest()->getRequiredBodyParam('formId');
        $form = Craft::$app->getElements()->getElementById($formId, FormElement::class);

        if (!$form) {
            return $this->asJson([
                'success' => false,
                'errors' => 'Form not found.',
            ]);
        }

        $tabSettings = Craft::$app->getRequest()->getRequiredBodyParam('tab');

        $fieldLayout = $form->getSubmissionFieldLayout();

        $tab = new FormFieldLayoutTab();
        $tab->setLayout($fieldLayout);

        $tab->name = $tabSettings['name'] ?? null;
        //$tab->setUserCondition($tabSettings['userCondition']);
        //$tab->setElementCondition($tabSettings['elementCondition']);
        $tab->uid = $tabSettings['uid'];

        $fieldLayout->setTabs([$tab]);

        $view = Craft::$app->getView();
        $view->startJsBuffer();
        $settingsHtml = $tab->getSettingsHtml();
        $tabSettingsJs = $view->clearJsBuffer();

        return $this->asJson([
            'success' => true,
            'tabUid' => $tabSettings['uid'],
            'settingsHtml' => $settingsHtml,
            'tabSettingsJs' => $tabSettingsJs,
        ]);
    }

    public function actionGetFormTabObject(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $name = Craft::$app->getRequest()->getRequiredBodyParam('name');
        //$userCondition = Craft::$app->getRequest()->getRequiredBodyParam('userCondition');
        //$elementCondition = Craft::$app->getRequest()->getRequiredBodyParam('elementCondition');

        return $this->asJson([
            'name' => $name,
            //'userCondition' => $userCondition,
            //'elementCondition' => $elementCondition,
        ]);
    }

    public function actionEditFormFieldSlideoutViaCpScreen(): Response
    {
        $this->requireAcceptsJson();

        $currentUser = Craft::$app->getUser()->getIdentity();

        if (!$currentUser->can(FormsModule::p('editForms'))) {
            throw new ForbiddenHttpException('User is not authorized to perform this action.');
        }

        $formId = Craft::$app->getRequest()->getRequiredParam('formId');
        $form = Craft::$app->getElements()->getElementById($formId, FormElement::class);

        if (!$form) {
            return $this->asJson([
                'success' => false,
                'errors' => 'Form not found.',
            ]);
        }

        $layoutElementConfig = Craft::$app->getRequest()->getRequiredParam('layoutElement');
        $layoutElementConfig = Json::decode($layoutElementConfig);
        $fieldConfig = $layoutElementConfig['field'];

        $class = $fieldConfig['type'] ?? null;
        $fieldSettings = $fieldConfig['settings'] ?? [];

        unset(
            $fieldConfig['type'],
            $fieldConfig['tabUid'],
            $fieldConfig['settings'],
        );

        $field = new $class($fieldConfig);
        $field->setAttributes($fieldSettings, false);

        $fieldLayoutElement = new CustomFormField($field);
        $fieldLayoutElement->layout = $form->getSubmissionFieldLayout();

        $fieldLayoutElement->required = $layoutElementConfig['required'] ?? false;
        $fieldLayoutElement->width = $layoutElementConfig['width'];
        $fieldLayoutElement->uid = $layoutElementConfig['uid'];

        // Remove CustomFormField::conditional() method when ready to implement conditional logic
        //$fieldLayoutElement->setUserCondition($layoutElementConfig['userCondition']);
        //$fieldLayoutElement->setElementCondition($layoutElementConfig['elementCondition']);

        $view = Craft::$app->getView();
        $view->startJsBuffer();

        // Render Field Settings
        // Render Condition Builders
        // Render JS for condition builders
        // we used to do this in the JS after the response but asCpScreen doesn't
        // allow this in the same way. So we have to try to do it in the buffer.

        $settingsHtml = $fieldLayoutElement->getSettingsHtml();

        // Just get the Field Settings, without Condition builder stuff
        $field = $fieldLayoutElement->getField();
        $settingsHtml = $field->getSettingsHtml();

        //Craft::$app->getView()->registerAssetBundle(ConditionBuilderAsset::class);

        /** @var UserCondition $userCondition */
        //$userCondition = !empty($fieldLayoutElement->userCondition)
        //    ? Craft::$app->conditions->createCondition($fieldLayoutElement->userCondition)
        //    : Craft::createObject(UserCondition::class);
        //$userCondition->elementType = SubmissionElement::class;
        //$userCondition->sortable = true;
        //$userCondition->mainTag = 'div';
        //$userCondition->name = 'userConditionRules';
        //$userCondition->id = 'userConditionRules';

        //$conditionHtml = self::swapPlaceholders($userCondition->getBuilderHtml(), $layoutElementConfig['fieldUid']);
        //let settingsHtml = self.swapPlaceholders(response.data.settingsHtml, response.data.fieldUid);

        // @featureRequest
        // Setting fieldUid throws an error if the field is just created in the layout
        // and isn't yet created in the DB, so we work around that by not setting it here
        //$fieldLayoutElement->fieldUid = $layoutElementConfig['fieldUid'];

        // merge old field and fieldLayoutElement['field'] prioritizing fieldLayoutElement['field']
        //$field = array_merge($oldField, $fieldLayoutElement['field']);

        //$field = FormBuilderHelper::getFieldData($layoutElementConfig['fieldUid']);
        //$field = $fieldLayoutElement['field'];

        $contentHtml = $view->renderTemplate('sprout-module-forms/forms/_formbuilder/editFormFieldContent.twig', [
            'field' => $field,
            'fieldLayoutElement' => $fieldLayoutElement,
            'fieldUid' => $layoutElementConfig['fieldUid'],
            'settingsHtml' => $settingsHtml,
            //'conditionHtml' => $conditionHtml,
        ]);

        $fieldSettingsJs = $view->clearJsBuffer();

        $tabs = [
            'form-field-general' => [
                // FieldLayoutForm
                //'tabId' => 'form-field',
                'label' => Craft::t('sprout-module-forms', 'Form Field'),
                'url' => '#form-field-general',
                'visible' => true,
                //'class' => $tab->hasErrors ? 'error' : null,
            ],
            //'form-field-rules' => [
                 //FieldLayoutForm
                //'tabId' => 'form-field-conditions',
                //'label' => Craft::t('sprout-module-forms', 'Field Rules'),
                //'url' => '#form-field-rules',
                //'visible' => false,
                //'class' => $tab->hasErrors ? 'error' : null,
            //],
        ];

        $html =
            Template::raw($contentHtml) .
            Template::raw($fieldSettingsJs);

        return $this->asCpScreen()
            ->tabs($tabs)
            ->submitButtonLabel('Apply')
            ->action('sprout-module-forms/form-builder/edit-form-field-slideout-response')
            ->content($html);
    }

    public function actionEditFormFieldSlideoutResponse(): Response
    {
        // get field from params
        $name = $this->request->getBodyParam('name');
        $handle = $this->request->getBodyParam('handle');
        $instructions = $this->request->getBodyParam('instructions');
        $required = $this->request->getBodyParam('fieldLayoutElement.required');
        $settings = $this->request->getBodyParam('settings');

        // Return params and let JS update field model in layout
        return $this->asJson([
            'success' => true,
            'message' => Craft::t('sprout-module-forms', 'Field updated.'),
            'layoutElement' => [
                'required' => $required,
                'field' => [
                    'name' => $name,
                    'handle' => $handle,
                    'instructions' => $instructions,
                    'settings' => $settings,
                ],
            ],
            'params' => $this->request->getBodyParams(),
        ]);
    }

    public static function swapPlaceholders($str, $sourceKey): ?string
    {
        $random = (string)floor(random_int(0, 1) * 1000000);
        $defaultId = 'condition' . $random;

        //return str
        //    . replace(/__ID__ /g, defaultId)
        //    .replace(/__SOURCE_KEY__(?=-)/g, Craft . formatInputId('"' + sourceKey + '"'))
        //    .replace(/__SOURCE_KEY__ / g, sourceKey);
        $formatInputId = Html::id('"' . $sourceKey . '"');
        $str = str_replace('__ID__', $defaultId, $str);
        $str = preg_replace('/__SOURCE_KEY__(?=-)/', $formatInputId, $str);
        $str = str_replace('__SOURCE_KEY__', $sourceKey, $str);

        return $str;
    }
}
