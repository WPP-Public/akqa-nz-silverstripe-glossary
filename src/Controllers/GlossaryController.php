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

        // Get only Live versions of the terms with non-null titles
        $terms = Versioned::get_by_stage(GlossaryTerm::class, Versioned::LIVE)
            ->filter('Title:not', null);

        // Create a map to store only the most recent version of each term
        $uniqueTerms = [];
        foreach ($terms as $term) {
            if (!isset($uniqueTerms[$term->Title]) || $term->ID > $uniqueTerms[$term->Title]->ID) {
                $uniqueTerms[$term->Title] = $term;
            }
        }

        // Convert the map to an array
        foreach ($uniqueTerms as $glossaryTerm) {
            $result[] = [
                'text' => $glossaryTerm->Title,
                // TinyMCE requires the value should be string
                'value' => (string)$glossaryTerm->ID,
            ];
        }

        $json = json_encode($result, JSON_PRETTY_PRINT);

        return HTTPResponse::create($json, 200)
            ->addHeader('Content-Type', 'application/json; charset=utf-8');
    }
}