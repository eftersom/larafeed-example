<?php

namespace Eftersom\Larafeed\Feed;

use Config;
use DOMDocument;

class Atom extends Feed
{
    const RSS_CHANNEL_TITLE          = 'string(/namespace:feed/namespace:title)';
    const RSS_CHANNEL_LINK           = '/namespace:feed/namespace:link)';
    const RSS_CHANNEL_DESCRIPTION    = 'string(/namespace:feed/namespace:summary)';
    const RSS_CHANNEL_SUBTITLE       = 'string(/namespace:feed/namespace:subtitle)';
    const RSS_CHANNEL_LANGUAGE       = 'string(/namespace:feed/@xml:lang)';
    const RSS_CHANNEL_IMAGE          = 'string(/namespace:feed/namespace:image)';
    const RSS_CHANNEL_ENCLOSURE      = 'string(/namespace:feed/namespace:enclosure)';
    const RSS_ENTRIES                = '/namespace:feed/namespace:entry';

    public $output;
    public $entries;

    /**
     * Rss constructor.
     * @param string $type
     * @param DOMDocument $domDocument
     */
    public function __construct(string $type, DOMDocument $domDocument)
    {
        parent::__construct($type, $domDocument);

        $acceptedTypes  = Config::get('larafeed.accepted_types');
        $this->xpath->registerNamespace('namespace', $acceptedTypes[$type]['namespace']); 
    }

    /**
     * Get feed title.
     *
     * @return string
     */
    public function title(): string
    {
        $title = $this->xpath->evaluate(self::RSS_CHANNEL_TITLE);

        return $title;
    }

    /**
     * Get link to feed.
     *
     * @return string
     */
    public function link(): string
    {
        $link = $this->xpath->evaluate('/namespace:feed/namespace:link/@href');

        return isset($link[0]) ? $link[0]->nodeValue : '';
    }

    /**
     * Get feed content via description signifier.
     *
     * @return string
     */
    public function description(): string
    {
        $description = $this->xpath->evaluate(self::RSS_CHANNEL_DESCRIPTION);

        if (!$description) {
            $description = $this->xpath->evaluate(self::RSS_CHANNEL_SUBTITLE);
        }



        return $description;
    }

    /**
     * Get the language of the feed.
     *
     * @return string
     */
    public function language(): string
    {
        $language = $this->xpath->evaluate(self::RSS_CHANNEL_LANGUAGE);

        return $language;
    }

    /**
     * Get image if available.
     *
     * @return string|null
     */
    public function image(): ?string
    {
        $items = $this->xpath->evaluate('namespace:feed/image');

        if ($items->length) {
            $imageData = $items->item(0);

            $imageData = explode(" ", trim($imageData->nodeValue));
            $imageData = array_filter($imageData);

            $image     = preg_grep('/(\.jpeg|\.jpg|\.png|\.gif)$/i', $imageData);
        }

        return isset($image[0]) ? rtrim($image[0]) : null;
    }

    /**
     * Return all entries in the provided feed xml.
     *
     * @param int $start
     * @param int $finish
     * @return array|null
     */
    public function entries(int $start = 0, int $finish = 5): ?array
    {
        $entryList = $this->xpath->query(self::RSS_ENTRIES);

        if (!($entryList->length)) {
            return null;
        }

        foreach ($entryList as $key => $entry) {
            if ($key >= 5) {
                return $entries ?? null;
            }
            $entries[$key] = $entry;
        }

        return $entries ?? null;
    }
}
