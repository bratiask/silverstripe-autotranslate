<?php
declare(strict_types=1);

namespace Bratiask\Autotranslate\Controller;

use Bratiask\Autotranslate\SiteConfig\AutotranslateSiteConfig;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\SiteConfig\SiteConfig;

class GoogleTranslateController extends ContentController
{
    const BASE_URI = 'https://translation.googleapis.com/language/translate/v2';

    private static $allowed_actions = [
        'translate'
    ];

    public function translate(HTTPRequest $request): HTTPResponse
    {
        $result = file_get_contents(self::BASE_URI, false, stream_context_create([
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query([
                    'key' => SiteConfig::current_site_config()->{AutotranslateSiteConfig::GOOGLE_TRANSLATE_API_KEY},
                    'source' => $request->postVar('source'),
                    'target' => $request->postVar('target'),
                    'q' => $request->postVar('query'),
                ])
            ]
        ]));

        if ($result) {
            return new HTTPResponse(json_decode($result, true)['data']['translations'][0]['translatedText']);
        }

        return new HTTPResponse('Cannot translate', 500);
    }
}