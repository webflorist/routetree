{!!
    json_encode([
            'id' => route_tree()->getCurrentNode()->getId(),
            'view' => 'test',
            'method' => \Request::getMethod(),
            'path' => \Request::getPathInfo(),
            'title' => route_tree()->getCurrentNode()->getTitle()
        ])
 !!}