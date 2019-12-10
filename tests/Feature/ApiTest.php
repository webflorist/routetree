<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\Feature\Traits\UsesTestRoutes;
use RouteTreeTests\TestCase;

class ApiTest extends TestCase
{
    use UsesTestRoutes;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $this->config->set('routetree.api.enabled', true);
    }

    public function test_routes_show()
    {
        $this->generateComplexTestRoutes($this->routeTree);

        $response = $this->get('api/routetree/routes/de.get')->decodeResponseJson();

        $this->assertEquals(
            array (
                'data' =>
                    array (
                        'type' => 'routes',
                        'id' => 'de.get',
                        'attributes' =>
                            array (
                                'node' => '',
                                'action' => 'get',
                                'uri' => 'de',
                                'locale' => 'de',
                                'methods' =>
                                    array (
                                        0 => 'GET',
                                        1 => 'HEAD',
                                    ),
                                'title' => 'Startseite',
                                'navTitle' => 'Startseite',
                                'payload' => Array(
                                    'translatedPayload' => 'Übersetzter Payload',
                                    'booleanPayload' => true
                                ),
                            ),
                    ),
            ),
            $response
        );
    }

    public function test_routes_index()
    {
        $this->generateComplexTestRoutes($this->routeTree);

        $response = $this->get('api/routetree/routes')->decodeResponseJson();

        $this->assertEquals(
            array (
                'data' =>
                    array (
                        0 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.get',
                                'attributes' =>
                                    array (
                                        'node' => '',
                                        'action' => 'get',
                                        'uri' => 'de',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Startseite',
                                        'navTitle' => 'Startseite',
                                        'payload' => Array(
                                            'translatedPayload' => 'Übersetzter Payload',
                                            'booleanPayload' => true
                                        ),
                                    ),
                            ),
                        1 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.auth.get',
                                'attributes' =>
                                    array (
                                        'node' => 'auth',
                                        'action' => 'get',
                                        'uri' => 'de/auth',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Auth',
                                        'navTitle' => 'Auth',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        2 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.auth.auth-child.get',
                                'attributes' =>
                                    array (
                                        'node' => 'auth.auth-child',
                                        'action' => 'get',
                                        'uri' => 'de/auth/auth-child',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Auth-child',
                                        'navTitle' => 'Auth-child',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        3 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-parameters.category.show:blumen',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-parameters/blumen',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Artikel über Blumen',
                                        'navTitle' => 'Artikel über Blumen',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        4 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-parameters.category.show:baeume',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-parameters/baeume',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Artikel über Bäume',
                                        'navTitle' => 'Artikel über Bäume',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        5 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-parameters.category.article.show:blumen,die-rose',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-parameters/blumen/die-rose',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Die Rose - Blume im Wandel der Zeit',
                                        'navTitle' => 'Die Rose - Blume im Wandel der Zeit',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        6 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-parameters.category.article.show:blumen,die-tulpe',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-parameters/blumen/die-tulpe',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Dit Tulpe im weltgeschichtlichen Finanzsystem',
                                        'navTitle' => 'Dit Tulpe im weltgeschichtlichen Finanzsystem',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        7 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-parameters.category.article.show:blumen,die-lilie',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-parameters/blumen/die-lilie',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Sehet die Lilien!',
                                        'navTitle' => 'Sehet die Lilien!',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        8 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-parameters.category.article.show:baeume,die-laerche',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-parameters/baeume/die-laerche',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Und jetzt... Die Lärche',
                                        'navTitle' => 'Und jetzt... Die Lärche',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        9 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-parameters.category.article.show:baeume,die-laerche',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-parameters/baeume/die-laerche',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Und jetzt... Die Lärche',
                                        'navTitle' => 'Und jetzt... Die Lärche',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        10 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-parameters.category.article.show:baeume,die-kastanie',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-parameters/baeume/die-kastanie',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Und jetzt... Der Kastanienbaum',
                                        'navTitle' => 'Und jetzt... Der Kastanienbaum',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        11 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-resources.index',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources',
                                        'action' => 'index',
                                        'uri' => 'de/blog-using-resources',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Blog-using-resources',
                                        'navTitle' => 'Blog-using-resources',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        12 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-resources.show:blumen',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-resources/blumen',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Artikel über Blumen',
                                        'navTitle' => 'Artikel über Blumen',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        13 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-resources.show:baeume',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-resources/baeume',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Artikel über Bäume',
                                        'navTitle' => 'Artikel über Bäume',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        14 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-resources.articles.index:blumen',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'index',
                                        'uri' => 'de/blog-using-resources/blumen/articles',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles',
                                        'navTitle' => 'Articles',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        15 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-resources.articles.index:baeume',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'index',
                                        'uri' => 'de/blog-using-resources/baeume/articles',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles',
                                        'navTitle' => 'Articles',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        16 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-resources.articles.show:blumen,die-rose',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-resources/blumen/articles/die-rose',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Die Rose - Blume im Wandel der Zeit',
                                        'navTitle' => 'Die Rose - Blume im Wandel der Zeit',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        17 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-resources.articles.show:blumen,die-tulpe',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-resources/blumen/articles/die-tulpe',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Dit Tulpe im weltgeschichtlichen Finanzsystem',
                                        'navTitle' => 'Dit Tulpe im weltgeschichtlichen Finanzsystem',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        18 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-resources.articles.show:blumen,die-lilie',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-resources/blumen/articles/die-lilie',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Sehet die Lilien!',
                                        'navTitle' => 'Sehet die Lilien!',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        19 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-resources.articles.show:baeume,die-laerche',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-resources/baeume/articles/die-laerche',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Und jetzt... Die Lärche',
                                        'navTitle' => 'Und jetzt... Die Lärche',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        20 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-resources.articles.show:baeume,die-laerche',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-resources/baeume/articles/die-laerche',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Und jetzt... Die Lärche',
                                        'navTitle' => 'Und jetzt... Die Lärche',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        21 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.blog-using-resources.articles.show:baeume,die-kastanie',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'de/blog-using-resources/baeume/articles/die-kastanie',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Und jetzt... Der Kastanienbaum',
                                        'navTitle' => 'Und jetzt... Der Kastanienbaum',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        22 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.excluded.get',
                                'attributes' =>
                                    array (
                                        'node' => 'excluded',
                                        'action' => 'get',
                                        'uri' => 'de/excluded',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Excluded',
                                        'navTitle' => 'Excluded',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        23 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.excluded.excluded-child.get',
                                'attributes' =>
                                    array (
                                        'node' => 'excluded.excluded-child',
                                        'action' => 'get',
                                        'uri' => 'de/excluded/excluded-child',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Excluded-child',
                                        'navTitle' => 'Excluded-child',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        24 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.excluded.non-excluded-child.get',
                                'attributes' =>
                                    array (
                                        'node' => 'excluded.non-excluded-child',
                                        'action' => 'get',
                                        'uri' => 'de/excluded/non-excluded-child',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Non-excluded-child',
                                        'navTitle' => 'Non-excluded-child',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        25 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.parameter-with-translated-values.parameter-with-translated-values.get:parameter-array-wert1',
                                'attributes' =>
                                    array (
                                        'node' => 'parameter-with-translated-values.parameter-with-translated-values',
                                        'action' => 'get',
                                        'uri' => 'de/parameter-with-translated-values/parameter-array-wert1',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Parameter-with-translated-values',
                                        'navTitle' => 'Parameter-with-translated-values',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        26 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.parameter-with-translated-values.parameter-with-translated-values.get:parameter-array-wert2',
                                'attributes' =>
                                    array (
                                        'node' => 'parameter-with-translated-values.parameter-with-translated-values',
                                        'action' => 'get',
                                        'uri' => 'de/parameter-with-translated-values/parameter-array-wert2',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Parameter-with-translated-values',
                                        'navTitle' => 'Parameter-with-translated-values',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        27 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.parameter-with-values.parameter-with-values.get:parameter-array-value1',
                                'attributes' =>
                                    array (
                                        'node' => 'parameter-with-values.parameter-with-values',
                                        'action' => 'get',
                                        'uri' => 'de/parameter-with-values/parameter-array-value1',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Parameter-with-values',
                                        'navTitle' => 'Parameter-with-values',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        28 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.parameter-with-values.parameter-with-values.get:parameter-array-value2',
                                'attributes' =>
                                    array (
                                        'node' => 'parameter-with-values.parameter-with-values',
                                        'action' => 'get',
                                        'uri' => 'de/parameter-with-values/parameter-array-value2',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Parameter-with-values',
                                        'navTitle' => 'Parameter-with-values',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        29 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.permanent-redirect.get',
                                'attributes' =>
                                    array (
                                        'node' => 'permanent-redirect',
                                        'action' => 'get',
                                        'uri' => 'de/permanent-redirect',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                                2 => 'POST',
                                                3 => 'PUT',
                                                4 => 'PATCH',
                                                5 => 'DELETE',
                                                6 => 'OPTIONS',
                                            ),
                                        'title' => 'Permanent-redirect',
                                        'navTitle' => 'Permanent-redirect',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        30 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.redirect.get',
                                'attributes' =>
                                    array (
                                        'node' => 'redirect',
                                        'action' => 'get',
                                        'uri' => 'de/redirect',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                                2 => 'POST',
                                                3 => 'PUT',
                                                4 => 'PATCH',
                                                5 => 'DELETE',
                                                6 => 'OPTIONS',
                                            ),
                                        'title' => 'Redirect',
                                        'navTitle' => 'Redirect',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        31 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.resource.index',
                                'attributes' =>
                                    array (
                                        'node' => 'resource',
                                        'action' => 'index',
                                        'uri' => 'de/resource',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Resource',
                                        'navTitle' => 'Resource',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        32 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.resource.store',
                                'attributes' =>
                                    array (
                                        'node' => 'resource',
                                        'action' => 'store',
                                        'uri' => 'de/resource',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'POST',
                                            ),
                                        'title' => 'Resource',
                                        'navTitle' => 'Resource',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        33 =>
                            array (
                                'type' => 'routes',
                                'id' => 'de.resource.create',
                                'attributes' =>
                                    array (
                                        'node' => 'resource',
                                        'action' => 'create',
                                        'uri' => 'de/resource/erstellen',
                                        'locale' => 'de',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Resource erstellen',
                                        'navTitle' => 'Erstellen',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        34 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.get',
                                'attributes' =>
                                    array (
                                        'node' => '',
                                        'action' => 'get',
                                        'uri' => 'en',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Startpage',
                                        'navTitle' => 'Startpage',
                                        'payload' => Array(
                                            'translatedPayload' => 'Translated Payload',
                                            'booleanPayload' => true
                                        ),
                                    ),
                            ),
                        35 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.auth.get',
                                'attributes' =>
                                    array (
                                        'node' => 'auth',
                                        'action' => 'get',
                                        'uri' => 'en/auth',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Auth',
                                        'navTitle' => 'Auth',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        36 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.auth.auth-child.get',
                                'attributes' =>
                                    array (
                                        'node' => 'auth.auth-child',
                                        'action' => 'get',
                                        'uri' => 'en/auth/auth-child',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Auth-child',
                                        'navTitle' => 'Auth-child',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        37 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-parameters.category.show:flowers',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-parameters/flowers',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles about flowers',
                                        'navTitle' => 'Articles about flowers',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        38 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-parameters.category.show:trees',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-parameters/trees',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles about trees',
                                        'navTitle' => 'Articles about trees',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        39 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-parameters.category.article.show:flowers,the-rose',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-parameters/flowers/the-rose',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Article',
                                        'navTitle' => 'Article',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        40 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-parameters.category.article.show:flowers,the-tulip',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-parameters/flowers/the-tulip',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Article',
                                        'navTitle' => 'Article',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        41 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-parameters.category.article.show:flowers,the-lily',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-parameters/flowers/the-lily',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Article',
                                        'navTitle' => 'Article',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        42 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-parameters.category.article.show:trees,the-larch',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-parameters/trees/the-larch',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Article',
                                        'navTitle' => 'Article',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        43 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-parameters.category.article.show:trees,the-larch',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-parameters/trees/the-larch',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Article',
                                        'navTitle' => 'Article',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        44 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-parameters.category.article.show:trees,the-chestnut',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-parameters.category.article',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-parameters/trees/the-chestnut',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Article',
                                        'navTitle' => 'Article',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        45 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-resources.index',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources',
                                        'action' => 'index',
                                        'uri' => 'en/blog-using-resources',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Blog-using-resources',
                                        'navTitle' => 'Blog-using-resources',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        46 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-resources.show:flowers',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-resources/flowers',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles about flowers',
                                        'navTitle' => 'Articles about flowers',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        47 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-resources.show:trees',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-resources/trees',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles about trees',
                                        'navTitle' => 'Articles about trees',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        48 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-resources.articles.index:flowers',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'index',
                                        'uri' => 'en/blog-using-resources/flowers/articles',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles',
                                        'navTitle' => 'Articles',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        49 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-resources.articles.index:trees',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'index',
                                        'uri' => 'en/blog-using-resources/trees/articles',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles',
                                        'navTitle' => 'Articles',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        50 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-resources.articles.show:flowers,the-rose',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-resources/flowers/articles/the-rose',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles: the-rose',
                                        'navTitle' => 'the-rose',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        51 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-resources.articles.show:flowers,the-tulip',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-resources/flowers/articles/the-tulip',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles: the-tulip',
                                        'navTitle' => 'the-tulip',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        52 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-resources.articles.show:flowers,the-lily',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-resources/flowers/articles/the-lily',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles: the-lily',
                                        'navTitle' => 'the-lily',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        53 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-resources.articles.show:trees,the-larch',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-resources/trees/articles/the-larch',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles: the-larch',
                                        'navTitle' => 'the-larch',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        54 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-resources.articles.show:trees,the-larch',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-resources/trees/articles/the-larch',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles: the-larch',
                                        'navTitle' => 'the-larch',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        55 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.blog-using-resources.articles.show:trees,the-chestnut',
                                'attributes' =>
                                    array (
                                        'node' => 'blog-using-resources.articles',
                                        'action' => 'show',
                                        'uri' => 'en/blog-using-resources/trees/articles/the-chestnut',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Articles: the-chestnut',
                                        'navTitle' => 'the-chestnut',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        56 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.excluded.get',
                                'attributes' =>
                                    array (
                                        'node' => 'excluded',
                                        'action' => 'get',
                                        'uri' => 'en/excluded',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Excluded',
                                        'navTitle' => 'Excluded',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        57 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.excluded.excluded-child.get',
                                'attributes' =>
                                    array (
                                        'node' => 'excluded.excluded-child',
                                        'action' => 'get',
                                        'uri' => 'en/excluded/excluded-child',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Excluded-child',
                                        'navTitle' => 'Excluded-child',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        58 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.excluded.non-excluded-child.get',
                                'attributes' =>
                                    array (
                                        'node' => 'excluded.non-excluded-child',
                                        'action' => 'get',
                                        'uri' => 'en/excluded/non-excluded-child',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Non-excluded-child',
                                        'navTitle' => 'Non-excluded-child',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        59 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.parameter-with-translated-values.parameter-with-translated-values.get:parameter-array-value1',
                                'attributes' =>
                                    array (
                                        'node' => 'parameter-with-translated-values.parameter-with-translated-values',
                                        'action' => 'get',
                                        'uri' => 'en/parameter-with-translated-values/parameter-array-value1',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Parameter-with-translated-values',
                                        'navTitle' => 'Parameter-with-translated-values',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        60 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.parameter-with-translated-values.parameter-with-translated-values.get:parameter-array-value2',
                                'attributes' =>
                                    array (
                                        'node' => 'parameter-with-translated-values.parameter-with-translated-values',
                                        'action' => 'get',
                                        'uri' => 'en/parameter-with-translated-values/parameter-array-value2',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Parameter-with-translated-values',
                                        'navTitle' => 'Parameter-with-translated-values',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        61 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.parameter-with-values.parameter-with-values.get:parameter-array-value1',
                                'attributes' =>
                                    array (
                                        'node' => 'parameter-with-values.parameter-with-values',
                                        'action' => 'get',
                                        'uri' => 'en/parameter-with-values/parameter-array-value1',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Parameter-with-values',
                                        'navTitle' => 'Parameter-with-values',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        62 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.parameter-with-values.parameter-with-values.get:parameter-array-value2',
                                'attributes' =>
                                    array (
                                        'node' => 'parameter-with-values.parameter-with-values',
                                        'action' => 'get',
                                        'uri' => 'en/parameter-with-values/parameter-array-value2',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Parameter-with-values',
                                        'navTitle' => 'Parameter-with-values',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        63 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.permanent-redirect.get',
                                'attributes' =>
                                    array (
                                        'node' => 'permanent-redirect',
                                        'action' => 'get',
                                        'uri' => 'en/permanent-redirect',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                                2 => 'POST',
                                                3 => 'PUT',
                                                4 => 'PATCH',
                                                5 => 'DELETE',
                                                6 => 'OPTIONS',
                                            ),
                                        'title' => 'Permanent-redirect',
                                        'navTitle' => 'Permanent-redirect',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        64 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.redirect.get',
                                'attributes' =>
                                    array (
                                        'node' => 'redirect',
                                        'action' => 'get',
                                        'uri' => 'en/redirect',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                                2 => 'POST',
                                                3 => 'PUT',
                                                4 => 'PATCH',
                                                5 => 'DELETE',
                                                6 => 'OPTIONS',
                                            ),
                                        'title' => 'Redirect',
                                        'navTitle' => 'Redirect',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        65 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.resource.index',
                                'attributes' =>
                                    array (
                                        'node' => 'resource',
                                        'action' => 'index',
                                        'uri' => 'en/resource',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Resource',
                                        'navTitle' => 'Resource',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        66 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.resource.store',
                                'attributes' =>
                                    array (
                                        'node' => 'resource',
                                        'action' => 'store',
                                        'uri' => 'en/resource',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'POST',
                                            ),
                                        'title' => 'Resource',
                                        'navTitle' => 'Resource',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                        67 =>
                            array (
                                'type' => 'routes',
                                'id' => 'en.resource.create',
                                'attributes' =>
                                    array (
                                        'node' => 'resource',
                                        'action' => 'create',
                                        'uri' => 'en/resource/create',
                                        'locale' => 'en',
                                        'methods' =>
                                            array (
                                                0 => 'GET',
                                                1 => 'HEAD',
                                            ),
                                        'title' => 'Create Resource',
                                        'navTitle' => 'Create',
                                        'payload' =>
                                            array (
                                            ),
                                    ),
                            ),
                    ),
            ),
            $response
        );

    }



}