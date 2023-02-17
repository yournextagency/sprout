<?php

namespace BarrelStrength\Sprout\redirects;

use BarrelStrength\Sprout\core\modules\SettingsRecord;
use BarrelStrength\Sprout\redirects\components\elements\RedirectElement;
use BarrelStrength\Sprout\redirects\redirects\MatchDefinition;
use BarrelStrength\Sprout\redirects\redirects\QueryStringStrategy;
use Craft;
use craft\config\BaseConfig;
use craft\models\FieldLayout;
use craft\records\Structure;

/**
 * @property int $structureUid
 * @property string|null $excludedUrlPatterns
 */
class RedirectsSettings extends BaseConfig
{
    // Project Config Settings

    public bool $enable404RedirectLog = false;

    public int $total404Redirects = 250;

    public bool $trackRemoteIp = false;

    public string $matchDefinition = MatchDefinition::URL_WITHOUT_QUERY_STRINGS;

    public string $queryStringStrategy = QueryStringStrategy::REMOVE_QUERY_STRINGS;

    public int $cleanupProbability = 1000;

    // The Field Layout Config that will be saved to Project Config
    public array $fieldLayouts = [];

    // DB Settings

    /**
     * We have a single structure for all Sites as the structure is
     * only used behind the scenes and queries will always be limited
     * to a single Site.
     */
    public ?string $structureUid = null;

    /**
     * Excluded URLs are stored as a string with individual values
     * separated by a new line character
     */
    private ?string $_excludedUrlPatterns = null;

    public function enable404RedirectLog(bool $value): self
    {
        $this->enable404RedirectLog = $value;

        return $this;
    }

    public function total404Redirects(int $value): self
    {
        $this->total404Redirects = $value;

        return $this;
    }

    public function trackRemoteIp(bool $value): self
    {
        $this->trackRemoteIp = $value;

        return $this;
    }

    public function matchDefinition(string $value): self
    {
        $this->matchDefinition = $value;

        return $this;
    }

    public function queryStringStrategy(string $value): self
    {
        $this->queryStringStrategy = $value;

        return $this;
    }

    public function cleanupProbability(int $value): self
    {
        $this->cleanupProbability = $value;

        return $this;
    }

    public function getStructureId(): int
    {
        if (!$this->structureUid) {
            $this->structureUid = Craft::$app->getProjectConfig()->get(RedirectsModule::projectConfigPath('structureUid'));
        }

        $structureId = (int)Structure::find()
            ->select('id')
            ->where([
                'uid' => $this->structureUid,
            ])
            ->scalar();

        return $structureId;
    }

    public function setStructureUid(?int $value): void
    {
        $this->structureUid = $value;
    }

    public function getExcludedUrlPatterns(int $siteId): ?string
    {
        if (isset($this->_excludedUrlPatterns)) {
            return $this->_excludedUrlPatterns;
        }

        $this->_excludedUrlPatterns = SettingsRecord::find()
            ->select('settings')
            ->where([
                'siteId' => $siteId,
                'moduleId' => RedirectsModule::getModuleId(),
                'name' => 'excludedUrlPatterns',
            ])
            ->scalar();

        return $this->_excludedUrlPatterns;
    }

    public function getFieldLayout(): FieldLayout
    {
        $fieldLayouts = Craft::$app->getProjectConfig()->get(RedirectsModule::projectConfigPath('fieldLayouts')) ?? [];

        // If there is a field layout, it's saved with a UID key and we just need the first value
        if ($fieldLayout = reset($fieldLayouts)) {
            return FieldLayout::createFromConfig($fieldLayout);
        }

        return new FieldLayout([
            'type' => RedirectElement::class,
        ]);
    }

    public function setExcludedUrlPatterns(string $value = null): void
    {
        $this->_excludedUrlPatterns = $value;
    }
}

