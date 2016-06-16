<?php

namespace RouteTreeTests;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteTreeMiddlewareTest extends RouteTreeTestCase
{

    protected $standardClosure = null;
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
            return json_encode([
                'id' => route_tree()->getCurrentNode()->getId(),
                'path' => \Request::getPathInfo(),
                'language' => \App::getLocale(),
            ]);
        };

        // Set root-node.
        $this->rootNode = [
            'index' => ['closure' => $this->standardClosure]
        ];

        // Set expected default result.
        $this->expectedResult = [
            "id" => "",
            "path" => "/de",
            "language" => "de",
        ];

        parent::__construct($name, $data, $dataName);
    }


    public function testDefaultLanguage()
    {
        $this->performSingleUriTest();
    }


    public function testLanguageDe()
    {
        $this->performSingleUriTest('/de');
    }


    public function testLanguageEn()
    {

        $this->expectedResult["language"] = 'en';
        $this->expectedResult["path"] = '/en';

        $this->performSingleUriTest('/en');
    }

    /**
     * @expectedException           \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function test404()
    {

        $this->performSingleUriTest('/foobar');
    }

    public function testAutoRedirectParentGerman()
    {

        $this->nodeTree = [
            'parent' => [
                'index' => ['closure' => $this->standardClosure],
                'segment' => [
                    'de' => 'eltern',
                    'en' => 'parent'
                ],
                'children' => [
                    'child' => [
                        'index' => ['closure' => $this->standardClosure],
                    ]
                ]

            ]
        ];

        $this->expectedResult = [
            "id" => "parent",
            "path" => "/de/eltern",
            "language" => "de",
        ];

        $this->performSingleUriTest('/eltern');
    }

    public function testAutoRedirectParentEnglish()
    {

        $this->nodeTree = [
            'parent' => [
                'index' => ['closure' => $this->standardClosure],
                'segment' => [
                    'de' => 'eltern',
                    'en' => 'parent'
                ],
                'children' => [
                    'child' => [
                        'index' => ['closure' => $this->standardClosure],
                    ]
                ]

            ]
        ];

        $this->expectedResult = [
            "id" => "parent",
            "path" => "/en/parent",
            "language" => "en",
        ];

        $this->performSingleUriTest('/parent');
    }

    public function testAutoRedirectChildGerman()
    {

        $this->nodeTree = [
            'parent' => [
                'index' => ['closure' => $this->standardClosure],
                'segment' => [
                    'de' => 'eltern',
                    'en' => 'parent'
                ],
                'children' => [
                    'child' => [
                        'index' => ['closure' => $this->standardClosure],
                        'segment' => [
                            'de' => 'kind',
                            'en' => 'child'
                        ],
                    ]
                ]

            ]
        ];

        $this->expectedResult = [
            "id" => "parent.child",
            "path" => "/de/eltern/kind",
            "language" => "de",
        ];

        $this->performSingleUriTest('/eltern/kind');
    }

    public function testAutoRedirectChildEnglish()
    {

        $this->nodeTree = [
            'parent' => [
                'index' => ['closure' => $this->standardClosure],
                'segment' => [
                    'de' => 'eltern',
                    'en' => 'parent'
                ],
                'children' => [
                    'child' => [
                        'index' => ['closure' => $this->standardClosure],
                        'segment' => [
                            'de' => 'kind',
                            'en' => 'child'
                        ],
                    ]
                ]

            ]
        ];

        $this->expectedResult = [
            "id" => "parent.child",
            "path" => "/en/parent/child",
            "language" => "en",
        ];

        $this->performSingleUriTest('/parent/child');
    }


}
