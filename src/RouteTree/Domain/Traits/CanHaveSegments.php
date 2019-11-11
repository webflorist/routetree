<?php

namespace Webflorist\RouteTree\Domain\Traits;

trait CanHaveSegments
{

    /**
     * An associative array with the languages as keys and the path-segments to be used for this node as values.
     *
     * @var array
     */
    protected $segments = [];

    /**
     * Sets the path-segment(s) to be used for this node.
     * Can either be an array of [locale => string] pairs,
     * or a string (to be used for all locales).
     *
     * @param array|string $segment
     * @return CanHaveSegments
     */
    public function segment($segment)
    {

        // Iterate through configured languages.
        foreach ($this->getLocales() as $locale) {

            // If $segments is an array and contains an entry for this locale, we use that.
            if (is_array($segment) && isset($segment[$locale])) {
                $this->setSegmentForLanguage($segment[$locale], $locale);
            } // If $segments is a string, we use that.
            else if (is_string($segment)) {
                $this->setSegmentForLanguage($segment, $locale);
            }

        }

        return $this;
    }

    protected function hasSegment($locale)
    {
        return $this->getSegment($locale) !== null;
    }

    protected function getSegment($locale)
    {
        if (is_string($this->segments)) {
            return $this->segments;
        }

        if (is_array($this->segments) && isset($this->segments[$locale])) {
            return $this->segments[$locale];
        }

        return null;
    }

    /**
     * Sets the path-segment to be used for this node in the specified languages.
     *
     * @param $segment
     * @param $language
     */
    protected function setSegmentForLanguage($segment, $language)
    {

        $this->segments[$language] = $segment;

        // If the path segment is a parameter, we also store it in $this->parameter.
        if ((substr($segment, 0, 1) === '{') && (substr($segment, -1) === '}')) {
            $this->parameter = str_replace('{', '', str_replace('}', '', $segment));
        }

    }

}