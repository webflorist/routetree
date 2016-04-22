<?php
/**
 * Created by PhpStorm.
 * User: geraldb
 * Date: 22.04.2016
 * Time: 17:20
 */

namespace Nicat\RouteTree;


class ListGenerator
{
    /**
     * @var RouteTree
     */
    protected $routeTree;

    /**
     * ListGenerator constructor.
     * @param RouteTree $routeTree
     */
    public function __construct(RouteTree $routeTree)
    {
        $this->routeTree = $routeTree;
    }

    public function generateListOfSubPages($view='', $nodeId='') {
        return $this->generateList(
            $view,
            $this->routeTree->getNode($nodeId)->getChildNodes()
        );
        
    }

    public function generateListOfSubPagesOfCurrentPage($view='') {
        return $this->generateListOfSubPages(
            $view,
            $this->routeTree->getIdOfCurrentNode()
        );
    }

    /**
     * @param string $view
     * @param RouteNode[] $routeNodes
     * @return mixed
     */
    public function generateList($view='', $routeNodes=[]) {
        return view(
            $view,
            [
                'routeNodes' => $routeNodes
            ]
        )->render();
    }


}