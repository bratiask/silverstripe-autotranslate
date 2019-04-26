<?php
declare(strict_types=1);

namespace Bratiask\Autotranslate\Extension;

use Bratiask\Autotranslate\SiteConfig\AutotranslateSiteConfig;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\SiteConfig\SiteConfig;
use TractorCow\Fluent\Model\Locale;
use TractorCow\Fluent\State\FluentState;

class AutotranslateExtension extends DataExtension
{
    const FLUENT_LOCALISED_FIELD_CSS_CLASS = 'fluent__localised-field';
    const TRANSLATABLE_FIELD_SCHEMA_DATA_TYPES = [
        FormField::SCHEMA_DATA_TYPE_STRING,
        FormField::SCHEMA_DATA_TYPE_TEXT,
        FormField::SCHEMA_DATA_TYPE_HTML
    ];

    private static $apiKey;
    private static $sourceLocale;
    private static $targetLocale;

    public function updateCMSFields(FieldList $fields): void
    {
        if (null !== $this->apiKey()) {
            $localisedDataObject = $this->localisedDataObject($this->owner, $this->sourceLocale());
            foreach ($fields->dataFields() as $field) {
                if ($this->isFieldAutotranslatable($field)) {
                    $field->setTitle(DBField::create_field('HTMLFragment', '
                        <div class="autotranslate js-autotranslate" 
                            data-source-lang="' . substr($this->sourceLocale(), 0, 2) . '" 
                            data-target-lang="' . substr($this->currentLocale(), 0, 2) . '"
                            data-value="' . $this->htmlEncode($this->owner->getField($field->getName())) . '"
                            data-source-value="' . (null === $localisedDataObject ? '' : $this->htmlEncode($localisedDataObject->{$field->getName()})) . '">
                            <span class="font-icon-translatable" title="Translatable field"></span>' . $field->getName() . '<span class="caret">&#9662;</span>
                            <ul>
                                ' . (null === $localisedDataObject ? '' : '<li class="js-autotranslate-translate">Translate</li><li class="js-autotranslate-revert">Revert to ' . substr($this->sourceLocale(), 3, 2) . '</li>') . '
                                <li class="js-autotranslate-reset">Reset changes</li>
                            </ul>
                        </div>
                    '));
                }
            }
        }
    }

    private function localisedDataObject(DataObject $dataObject, string $locale): ?DataObject
    {
        if ($this->sourceLocale() === $this->currentLocale()) {
            return null;
        }

        $originalLocale = $this->currentLocale();
        FluentState::singleton()->setLocale($locale);

        $localisedDataObject = DataObject::get($dataObject->ClassName)->byID($dataObject->ID);
        FluentState::singleton()->setLocale($originalLocale);

        return $localisedDataObject;
    }

    private function isFieldAutotranslatable(FormField $field): bool
    {
        return
            $field->hasClass(self::FLUENT_LOCALISED_FIELD_CSS_CLASS) &&
            in_array($field->getSchemaDataType(), self::TRANSLATABLE_FIELD_SCHEMA_DATA_TYPES) &&
            false === $field->isReadOnly() &&
            'URLSegment' !== $field->getName();
    }

    private function htmlEncode(?string $string): string
    {
        if (null === $string) {
            return '';
        }

        return htmlspecialchars($string);
    }

    private function sourceLocale(): string
    {
        if (null === self::$sourceLocale) {
            self::$sourceLocale = Locale::singleton()->getChain()->first()->Locale;
        }

        return self::$sourceLocale;
    }

    private function currentLocale(): string
    {
        if (null === self::$targetLocale) {
            self::$targetLocale = Locale::singleton()->getCurrentLocale()->Locale;
        }

        return self::$targetLocale;
    }

    private function apiKey(): ?string
    {
        if (null === self::$apiKey) {
            self::$apiKey = SiteConfig::current_site_config()->{AutotranslateSiteConfig::GOOGLE_TRANSLATE_API_KEY};
        }

        return self::$apiKey;
    }
}