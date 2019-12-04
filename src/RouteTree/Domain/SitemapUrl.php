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
     * Is this node (and all children) excluded from sitemap?
     *
     * @var bool
     */
    private $isExcluded;

    /**
     * The date of last modification of the page.
     *
     * @var string
     */
    private $lastmod;

    /**
     * How frequently the page is likely to change. Valid values are:
     *  - always
     *  - hourly
     *  - daily
     *  - weekly
     *  - monthly
     *  - yearly
     *  - never
     *
     * @var string
     */
    private $changefreq;

    /**
     * The priority of this URL relative to other URLs on your site.
     * Valid values range from 0.0 to 1.0.
     *
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

    /**
     * Sets the date of last modification of the page.
     *
     * @param Carbon $date
     * @return $this
     */
    public function lastmod(Carbon $date)
    {
        $this->lastmod = $date->toW3cString();
        return $this;
    }

    /**
     * Is a lastmod date set?
     *
     * @param array|null $parameters
     * @param string|null $locale
     * @return bool
     */
    public function hasLastmod(?array $parameters = null, ?string $locale = null)
    {
        return $this->getLastmod($parameters, $locale) !== null;
    }

    /**
     * Get the lastmod date -
     * either from this object,
     * or via RoutePayload.
     *
     * @param array|null $parameters
     * @param string|null $locale
     * @return mixed|string
     */
    public function getLastmod(?array $parameters = null, ?string $locale = null)
    {
        return $this->lastmod ?? $this->routeNode->payload->get('lastmod', $parameters, $locale);
    }

    /**
     * State how frequently the page is likely to change. Valid values are:
     *  - always
     *  - hourly
     *  - daily
     *  - weekly
     *  - monthly
     *  - yearly
     *  - never
     *
     * @param string $value
     * @return $this
     * @throws InvalidChangefreqValueException
     */
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

    /**
     * Is a changefreq date set?
     *
     * @param array|null $parameters
     * @param string|null $locale
     * @return bool
     */
    public function hasChangefreq(?array $parameters = null, ?string $locale = null)
    {
        return $this->getChangefreq($parameters, $locale) !== null;
    }

    /**
     * Get the changefreq value -
     * either from this object,
     * or via RoutePayload.
     *
     * @param array|null $parameters
     * @param string|null $locale
     * @return mixed|string
     */
    public function getChangefreq(?array $parameters = null, ?string $locale = null)
    {
        return $this->changefreq ?? $this->routeNode->payload->get('changefreq', $parameters, $locale);
    }

    /**
     * Set the priority of this URL relative to other URLs on your site.
     * Valid values range from 0.0 to 1.0.
     *
     * @param float $value
     * @return $this
     * @throws InvalidPriorityValueException
     */
    public function priority(float $value)
    {
        if ($value > 1.0 || $value < 0.0) {
            throw new InvalidPriorityValueException("Value of 'priority' tag of a sitemap url must be between 0.0 and 1.0. '$value' is not.'");
        }
        $this->priority = $value;
        return $this;
    }

    /**
     * Is a priority set?
     *
     * @param array|null $parameters
     * @param string|null $locale
     * @return bool
     */
    public function hasPriority(?array $parameters = null, ?string $locale = null)
    {
        return $this->getPriority($parameters, $locale) !== null;
    }

    /**
     * Get the priority value -
     * either from this object,
     * or via RoutePayload.
     *
     * @param array|null $parameters
     * @param string|null $locale
     * @return float|mixed|string
     */
    public function getPriority(?array $parameters = null, ?string $locale = null)
    {
        $priority = $this->priority ?? $this->routeNode->payload->get('priority', $parameters, $locale);
        if (!is_null($priority)) {
            $priority = number_format($this->priority, 1);
        }
        return $priority;
    }

    /**
     * Is this RouteNode excluded -
     * either directly or
     * indirectly via its parents.
     *
     * @return bool
     */
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

    /**
     * Exclude this RouteNode
     * (and all it's children)
     * from sitemap.
     *
     * @param bool $exclude
     */
    public function exclude(bool $exclude = true)
    {
        $this->isExcluded = $exclude;
    }

}
