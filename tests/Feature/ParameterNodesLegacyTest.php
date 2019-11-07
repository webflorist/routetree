<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\LegacyTestCase;

class ParameterNodesLegacyTest extends LegacyTestCase
{

    protected $rootNode = [];

    protected $nodeTree = [];

    protected $standardClosure = null;

    protected $expectedResult = [];

    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        // Set standard closure.
        $this->standardClosure = function () {
            $translationLocale = ((\App::getLocale() === 'de') ? 'en':'de');
            return json_encode([
                'id' => route_tree()->getCurrentNode()->getId(),
                'path' => trim(\Request::getPathInfo(),'/'),
                'language' => \App::getLocale(),
                'activeValue' => route_tree()->getCurrentNode()->getActiveValue(),
                'translationUri' => route_tree()->getCurrentAction()->getUrl(null,$translationLocale),
                'title' => route_tree()->getCurrentNode()->getTitle(),
                'translationTitle' => route_tree()->getCurrentNode()->getTitle(null, $translationLocale),
            ]);
        };

        // Set root-node.
        $this->rootNode = [
            'index' => ['closure' => $this->standardClosure]
        ];

        parent::__construct($name, $data, $dataName);
    }

    public function testParameterNodeWithValueTranslation()
    {

        $this->nodeTree = [
            'page' => [
                'index' => ['closure' => $this->standardClosure],
                'segment' => '{parameter}',
                'values' => [
                    'de' => [
                        'value1' => 'uebersetzt1'
                    ],
                    'en' => [
                        'value1' => 'translate1'
                    ]
                ]
            ]
        ];

        $this->expectedResult = [
            "id" => "page",
            "path" => "de/uebersetzt1",
            "language" => "de",
            "activeValue" => 'value1',
            "translationUri" => 'http://localhost/en/translate1',
            'title' => 'Page',
            'translationTitle' => 'Page'
        ];

        $this->performSingleUriTest('/de/uebersetzt1');
    }

    public function testParameterNodeWithAutomaticValueAndTitleTranslation()
    {

        $this->nodeTree = [
            'parameter' => [
                'index' => ['closure' => $this->standardClosure],
                'segment' => '{parameter}',
            ]
        ];

        $this->expectedResult = [
            "id" => "parameter",
            "path" => "de/wert_1",
            "language" => "de",
            "activeValue" => 'value1',
            "translationUri" => 'http://localhost/en/value_1',
            'title' => 'Wert 1',
            'translationTitle' => 'Value 1'
        ];

        $this->performSingleUriTest('/de/wert_1');
    }



}
