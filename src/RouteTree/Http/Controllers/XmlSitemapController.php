<?php

namespace Webflorist\RouteTree\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webflorist\RouteTree\Http\Resources\RouteCollection;
use Webflorist\RouteTree\Services\XmlSitemapGenerator;

class XmlSitemapController extends Controller
{

    public function get(Request $request)
    {

        return response((new XmlSitemapGenerator())->generate(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }


}
