<?php
declare(strict_types=1);

namespace Bratiask\Autotranslate\SiteConfig;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBText;

class AutotranslateSiteConfig extends DataExtension
{
    const GOOGLE_TRANSLATE_API_KEY = 'GoogleTranslateApiKey';

    private static $db = array(
        self::GOOGLE_TRANSLATE_API_KEY => DBText::class
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Autotranslate', new TextField(self::GOOGLE_TRANSLATE_API_KEY, 'Google Translate API key'));
    }
}