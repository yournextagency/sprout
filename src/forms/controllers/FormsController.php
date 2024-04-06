<?php

namespace BarrelStrength\Sprout\forms\controllers;

use BarrelStrength\Sprout\forms\components\elements\FormElement;
use BarrelStrength\Sprout\forms\components\elements\SubmissionElement;
use BarrelStrength\Sprout\forms\formfields\CustomFormField;
use BarrelStrength\Sprout\forms\forms\FormBuilderHelper;
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
use craft\helpers\UrlHelper;
use craft\models\FieldLayoutTab;
use craft\models\Site;
use craft\web\assets\conditionbuilder\ConditionBuilderAsset;
use craft\web\Controller as BaseController;
use craft\web\View;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class FormsController extends BaseController
{
    public function actionFormsIndexTemplate(): Response
    {
        $this->requirePermission(FormsModule::p('editForms'));

        $formTypes = FormTypeHelper::getFormTypes();

        return $this->renderTemplate('sprout-module-forms/forms/index', [
            'title' => FormElement::pluralDisplayName(),
            'elementType' => FormElement::class,
            'formTypes' => $formTypes,
            'selectedSubnavItem' => 'forms',
        ]);
    }

    //public function actionDuplicateForm()
    //{
    //    $this->requirePermission(FormsModule::p('editForms'));
    //
    //    return $this->runAction('save-form', ['duplicate' => true]);
    //}

    public function actionNewForm(): Response
    {
        $site = Cp::requestedSite();

        if (!$site instanceof Site) {
            throw new ForbiddenHttpException('User not authorized to edit content in any sites.');
        }

        if (!FormsModule::getInstance()->forms->canCreateForm()) {
            throw new WrongEditionException('Please upgrade to Sprout Forms Pro Edition to create unlimited forms.');
        }

        $user = Craft::$app->getUser()->getIdentity();
        $form = Craft::createObject(FormElement::class);

        if (!$form->canSave($user)) {
            throw new ForbiddenHttpException('User not authorized to create a form.');
        }

        $formTypeUid = Craft::$app->getRequest()->getRequiredParam('formTypeUid');
        $formType = FormTypeHelper::getFormTypeByUid($formTypeUid);

        return $this->renderTemplate('sprout-module-forms/forms/_new', [
            'formType' => $formType,
        ]);
    }

    public function actionCreateForm(): Response
    {
        $site = Cp::requestedSite();

        if (!$site instanceof Site) {
            throw new ForbiddenHttpException('User not authorized to edit content in any sites.');
        }

        if (!FormsModule::getInstance()->forms->canCreateForm()) {
            throw new WrongEditionException('Please upgrade to Sprout Forms Pro Edition to create unlimited forms.');
        }

        $user = Craft::$app->getUser()->getIdentity();
        $form = Craft::createObject(FormElement::class);

        if (!$form->canSave($user)) {
            throw new ForbiddenHttpException('User not authorized to create a form.');
        }

        $form->name = '';
        $form->handle = '';
        $form->titleFormat = "{dateCreated|date('D, d M Y H:i:s')}";
        $form->name = Craft::$app->getRequest()->getRequiredParam('name');
        $form->handle = StringHelper::toHandle($form->name) . '_' . StringHelper::randomString(6);
        $form->formTypeUid = Craft::$app->getRequest()->getRequiredParam('formTypeUid');

        $form->setScenario(Element::SCENARIO_ESSENTIALS);
        if (!Craft::$app->getDrafts()->saveElementAsDraft($form, Craft::$app->getUser()->getId(), null, null, false)) {
            throw new ServerErrorHttpException(sprintf('Unable to save report as a draft: %s', implode(', ', $form->getErrorSummary(true))));
        }

        $contentTableName = FormContentTableHelper::getContentTable($form->id);
        FormContentTableHelper::createContentTable($contentTableName);

        return $this->redirect($form->getCpEditUrl());
    }

    public function actionDeleteForm(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission(FormsModule::p('editForms'));

        $request = Craft::$app->getRequest();

        // Get the Form these fields are related to
        $formId = $request->getRequiredBodyParam('formId');

        Craft::$app->getElements()->deleteElementById($formId, FormElement::class);

        return $this->redirectToPostedUrl();
    }
}
