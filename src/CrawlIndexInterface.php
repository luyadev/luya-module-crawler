<?php

namespace luya\crawler;

/**
 * CrawlIndexInterface
 * 
 * An interface which holds the information about crawler links.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.0
 */
interface CrawlIndexInterface
{
    /**
     * Return an array with absolute links to crawl.
     * 
     * The crawler will pass all links to the CrawlContainer object.
     *
     * ```php
     * public static function indexLinks()
     * {
     *     return [
     *         'https://luya.io' => 'The LUYA Homepage',
     *     ];
     * }
     * ```
     * 
     * @return array An array with absolute links paths as key and the page title as value
     */
    public static function indexLinks();
}