<?php

namespace Eftersom\Larafeed\Feed\Entry;

use Carbon\Carbon;
use Config;
use DOMElement;

class AtomEntry extends Entry
{
    /**
     * RssEntry constructor.
     * @param string $key
     * @param DOMElement $domElement
     */
    public function __construct(string $key, DOMElement $domElement)
    {
        parent::__construct($key, $domElement);

        $acceptedTypes  = Config::get('larafeed.accepted_types');
        $this->xpath->registerNamespace('namespace', $acceptedTypes['atom']['namespace']); 
    }

    /**
     * Get entry title.
     *
     * @return string|null
     */
    public function title(): ?string
    {
        $title = $this->xpath->evaluate('string(namespace:title)', $this->domElement);

        if (is_null($title)) {
            return null;
        }

        return $title;
    }

        /**
     * Get content of this entry, or a description of summary content.
     *
     * @return null|string
     */
    public function description(): ?string
    {
        $description = $this->entryDescriptionOutput();

        $description = preg_replace("/<img[^>]+\>/i", "", $description);

        return $description ?? null;
    }

    /**
     * Get specific link provided by the entry.
     *
     * @param int $index
     * @return string|null
     */
    public function link(int $index = 0): ?string
    {
        $links = $this->allLinks();
        return $links[$index] ?? null;
    }

    /**
     * Get all links associated with this entry.
     *
     * @return array|null
     */
    public function allLinks(): ?array
    {
        $links      = [];
        $entryLinks = $this->xpath->evaluate('namespace:link/@href', $this->domElement);

        foreach ($entryLinks as $link) {
            $links[] = $link->nodeValue;
        }

        return $links ?? null;
    }

    /**
     * Get publish date.
     *
     * @return string
     */
    public function datePublished(): string
    {
        $datePublished = $this->xpath->evaluate('string(namespace:published)', $this->domElement);

        $datePublished = Carbon::parse($datePublished)->toDayDateTimeString();

        return $datePublished;
    }

        /**
     * Get content of this entry, or a description of summary content.
     *
     * @return null|string
     */
    public function image(): ?string
    {
        $description = $this->entryDescriptionOutput();

        if (preg_match('/src="(.*?)"/', $description, $matches)) {
            $src = $matches[1];
        }

        return $src ?? null;
    }

    /**
     * Get raw content of this entry, or a description of summary content.
     *
     * @return null|string
     */
    private function entryDescriptionOutput(): ?string
    {
        $description = $this->xpath->evaluate('string(namespace:description)', $this->domElement);
       
        if (!$description) {
            $description = $this->xpath->evaluate('string(namespace:content)', $this->domElement);
        }

        if (!$description) {
            $description = $this->xpath->evaluate('string(namespace:summary)', $this->domElement);
        }

        return $description ?? null;
    }
}
