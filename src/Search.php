<?php

namespace Crew\Unsplash;

/**
 * Class Search
 * @package Crew\Unsplash
 */
class Search extends Endpoint
{
    /**
     * Retrieve a single page of photo results depending on search results
     *
     * @param  array $filters Filters.
     *
     * @return PageResult
     */
    public static function photos($filters = [])
    {
        $photos = self::get("/search/photos", ['query' => $filters]);

        return self::getPageResult($photos->getBody(), $photos->getHeaders(), Photo::class);
    }

    /**
     * Retrieve a single page of photo results depending on search results
     * Returns ArrayObject that contain PageResult object.
     *
     * @param  array $filters Filters.
     *
     * @return PageResult
     */
    public static function random($filters = [])
    {
        $photos = self::get("/photos/random", ['query' => $filters]);

        return self::getPageResult($photos->getBody(), $photos->getHeaders(), Photo::class);
    }

    /**
     * Retrieve a single page of collection results depending on search results
     *
     * @param  array $filters Filters.
     *
     * @return PageResult
     */
    public static function collections($filters = [])
    {
        $collections = self::get("/search/collections", ['query' => $filters]);

        return self::getPageResult($collections->getBody(), $collections->getHeaders(), Collection::class);
    }

    /**
     * Retrieve a single page of user results depending on search results
     *
     * @param  array $filters Filters.
     *
     * @return PageResult
     */
    public static function users($filters = [])
    {
        $users = self::get("/search/users", ['query' => $filters]);

        return self::getPageResult($users->getBody(), $users->getHeaders(), User::class);
    }
}
