<?php

namespace TheSceneman\SilverStripeGlossary\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Security;
use TheSceneman\SilverStripeGlossary\Model\GlossaryTerm;
use SilverStripe\Versioned\Versioned;

class GlossaryController extends Controller
{
    private static array $allowed_actions = [
        'glossary',
    ];

    public function glossary(HTTPRequest $request): HTTPResponse
    {
        $member = Security::getCurrentUser();

        if ($member === null) {
            return $this->httpError(403, 'Forbidden');
        }

        $result = [];

        /** @var GlossaryTerm $glossaryTerm */
        foreach (GlossaryTerm::get() as $glossaryTerm) {
            // Get the latest version of the term
            $latestVersion = Versioned::get_latest_version(GlossaryTerm::class, $glossaryTerm->ID);

            if ($latestVersion) {
                $result[] = [
                    'text' => $latestVersion->Title,
                    // TinyMCE requires the value should be string
                    'value' => (string)$latestVersion->ID,
                ];
            }
        }

        $json = json_encode($result, JSON_PRETTY_PRINT);

        return HTTPResponse::create($json, 200)
            ->addHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
