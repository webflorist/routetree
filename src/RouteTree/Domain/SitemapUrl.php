<?php

namespace Webflorist\RouteTree\Domain;

use Carbon\Carbon;
use Webflorist\RouteTree\Exceptions\InvalidChangefreqValueException;
use Webflorist\RouteTree\Exceptions\InvalidPriorityValueException;

/**
 * XML-Sitemap related data.
 *
 * Class RegisteredRoute
 * @package Webflorist\RouteTree
 */
class SitemapUrl
{

    /**
     * The RouteNode this SitemapUrl belongs to.
     *
     * @var RouteNode
     */
    public $routeNode;

    /**
     * @var string
     */
    private $lastmod;

    /**
     * @var bool
     */
    private $isExcluded;

    /**
     * @var string
     */
    private $changefreq;

    /**
     * @var float
     */
    private $priority;

    /**
     * SitemapUrl constructor.
     *
     * @param RouteNode $routeNode
     */
    public function __construct(RouteNode $routeNode)
    {
        $this->routeNode = $routeNode;
    }

    public function routeNode(RouteNode $routeNode)
    {
        $this->routeNode = $routeNode;
        return $this;
    }

    public function lastmod(Carbon $date)
    {
        $this->lastmod = $date->toW3cString();
        return $this;
    }

    public function hasLastmod()
    {
        return $this->lastmod !== null;
    }

    public function getLastmod()
    {
        return $this->lastmod;
    }

    public function changefreq(string $value)
    {
        switch ($value) {
            case 'always':
            case 'hourly':
            case 'daily':
            case 'weekly':
            case 'monthly':
            case 'yearly':
            case 'never':
                $this->changefreq = $value;
                break;
            default:
                throw new InvalidChangefreqValueException("'$value' is not a valid value for the 'changefreq' tag of a sitemap url.");
        }
        return $this;
    }

    public function hasChangefreq()
    {
        return $this->changefreq !== null;
    }

    public function getChangefreq()
    {
        return $this->changefreq;
    }

    public function priority(float $value)
    {
        if ($value > 1.0 || $value < 0.0) {
            throw new InvalidPriorityValueException("Value of 'priority' tag of a sitemap url must be between 0.0 and 1.0. '$value' is not.'");
        }
        $this->priority = $value;
        return $this;
    }

    public function hasPriority()
    {
        return $this->priority !== null;
    }

    public function getPriority()
    {
        return number_format($this->priority,1);
    }

    public function isExcluded()
    {
        if (is_bool($this->isExcluded)) {
            return $this->isExcluded;
        }

        if ($this->routeNode->hasParentNode()) {
            return $this->routeNode->getParentNode()->sitemap->isExcluded();
        }

        return false;
    }

    public function exclude(bool $exclude=true)
    {
        $this->isExcluded = $exclude;
    }

}
