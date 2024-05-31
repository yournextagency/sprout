<?php

namespace BarrelStrength\Sprout\mailer\subscriberlists;

use BarrelStrength\Sprout\mailer\components\elements\audience\AudienceElement;
use Craft;
use craft\base\Component;
use Throwable;
use yii\db\Transaction;

class SubscriberLists extends Component
{
    public function add(SubscriptionRecord $subscription): bool
    {
        /** @var Transaction $transaction */
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            $user = null;

            // Prefer User ID over email
            if ($subscription->userId) {
                $user = Craft::$app->getUsers()->getUserById($subscription->userId);

                if (!$user) {
                    $subscription->addErrors([
                        'userId' => [
                            Craft::t('sprout-module-mailer', 'User does not exist.'),
                        ],
                    ]);
                }
            } elseif (filter_var($subscription->email, FILTER_VALIDATE_EMAIL)) {
                $user = Craft::$app->getUsers()->ensureUserByEmail($subscription->email);
                $subscription->userId = $user->id;
            }

            if (!$user || !$subscription->validate()) {
                return false;
            }

            $subscriptionExists = SubscriptionRecord::find()
                ->where(['subscriberListId' => $subscription->subscriberListId])
                ->andWhere(['userId' => $subscription->userId])
                ->exists();

            if (!$subscriptionExists) {
                $subscription->save();

                // Resave user to ensure Element Indexes are updated when refreshed
                Craft::$app->getElements()->saveElement($user, false);
            }

            $transaction->commit();
        } catch (Throwable $throwable) {
            $transaction->rollBack();

            throw $throwable;
        }

        return true;
    }

    public function remove(SubscriptionRecord $subscription): bool
    {
        $user = null;

        if ($subscription->userId) {
            $user = Craft::$app->getUsers()->getUserById($subscription->userId);
            if (!$user) {
                $subscription->addErrors([
                    'userId' => [
                        Craft::t('sprout-module-mailer', 'User does not exist.'),
                    ],
                ]);
            }
        } elseif (filter_var($subscription->email, FILTER_VALIDATE_EMAIL)) {
            if ($user = Craft::$app->getUsers()->getUserByUsernameOrEmail($subscription->email)) {
                $subscription->userId = $user->id;
            } else {
                $subscription->addErrors([
                    'userId' => [
                        Craft::t('sprout-module-mailer', 'User does not exist.'),
                    ],
                ]);
            }
        }

        SubscriptionRecord::findOne([
            'subscriberListId' => $subscription->subscriberListId,
            'userId' => $subscription->userId,
        ])?->delete();

        if ($user) {
            // Resave user to ensure Element Indexes are updated when refreshed
            Craft::$app->getElements()->saveElement($user, false);
        }

        return true;
    }

    public function getSubscriptions(AudienceElement $audience): array
    {
        return SubscriptionRecord::find()
            ->where(['audienceId' => $audience->id])
            ->all();
    }
}
