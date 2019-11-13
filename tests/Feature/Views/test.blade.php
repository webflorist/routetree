{!!
    json_encode([
            'id' => route_tree()->getCurrentNode()->getId(),
            'view' => 'test',
            'method' => \Request::getMethod(),
            'path' => trim(\Request::getPathInfo(),'/'),
            'locale' => app()->getLocale(),
            'payload' => route_tree()->getCurrentNode()->payload,
            'title' => route_tree()->getCurrentNode()->payload->getTitle(),
            'navTitle' => route_tree()->getCurrentNode()->payload->getNavTitle(),
            'h1Title' => route_tree()->getCurrentNode()->payload->getH1Title(),
            'foo' => $foo ?? null
        ])
 !!}