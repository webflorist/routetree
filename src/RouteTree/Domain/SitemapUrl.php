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

    public function hasLastmod(?array $parameters = null, ?string $locale = null)
    {
        return $this->getLastmod($parameters, $locale) !== null;
    }

    public function getLastmod(?array $parameters = null, ?string $locale = null)
    {
        return $this->lastmod ?? $this->routeNode->payload->get('lastmod', $parameters, $locale);
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

    public function hasChangefreq(?array $parameters = null, ?string $locale = null)
    {
        return $this->getChangefreq($parameters, $locale) !== null;
    }

    public function getChangefreq(?array $parameters = null, ?string $locale = null)
    {
        return $this->changefreq ?? $this->routeNode->payload->get('changefreq', $parameters, $locale);
    }

    public function priority(float $value)
    {
        if ($value > 1.0 || $value < 0.0) {
            throw new InvalidPriorityValueException("Value of 'priority' tag of a sitemap url must be between 0.0 and 1.0. '$value' is not.'");
        }
        $this->priority = $value;
        return $this;
    }

    public function hasPriority(?array $parameters = null, ?string $locale = null)
    {
        return $this->getPriority($parameters, $locale) !== null;
    }

    public function getPriority(?array $parameters = null, ?string $locale = null)
    {
        $priority = $this->priority ?? $this->routeNode->payload->get('priority', $parameters, $locale);
        if (!is_null($priority)) {
            $priority = number_format($this->priority, 1);
        }
        return $priority;
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

    public function exclude(bool $exclude = true)
    {
        $this->isExcluded = $exclude;
    }

}
