<?php

namespace Crew\Unsplash;

/**
 * Class User
 * @package Crew\Unsplash
 * @property string $username
 */
class User extends Endpoint
{
    private $photos;

    private $likes;

    private $collections;

    /**
     * Retrieve a User object from the username specified
     *
     * @param string $username Username of the user
     * @return User
     */
    public static function find($username)
    {
        $user = json_decode(self::get("/users/{$username}")->getBody(), true);
        
        return new self($user);
    }

    /**
     * Retrieve all the photos for a specific user on a given page.
     * Returns an ArrayObject that contains Photo objects.
     *
     * @param  array $filters Filters.
     *
     * @return ArrayObject of Photos
     */
    public function photos($filters = [])
    {
        if (! isset($this->photos["{$filters['page']}-{$filters['per_page']}-{$filters['order_by']}"])) {
            $photos = self::get("/users/{$this->username}/photos", [
                'query' => ['page' => $filters['page'], 'per_page' => $filters['per_page'], 'order_by' => $filters['order_by']]
            ]);

            $this->photos["{$filters['page']}-{$filters['per_page']}-{$filters['order_by']}"] = [
                'body' => self::getArray($photos->getBody(), __NAMESPACE__.'\\Photo'),
                'headers' => $photos->getHeaders()
            ];
        }

        return new ArrayObject(
            $this->photos["{$filters['page']}-{$filters['per_page']}-{$filters['order_by']}"]['body'],
            $this->photos["{$filters['page']}-{$filters['per_page']}-{$filters['order_by']}"]['headers']
        );
    }

    /**
     * Retrieve all the collections for a specific user on a given page.
     * Returns an ArrayObject that contains Collection objects.
     *
     * Include private collection if it's the user bearer token
     *
     * @param  array $filters Filters.
     *
     * @return   ArrayObject of Collections
     */
    public function collections($filters = [])
    {
        if (! isset($this->collections["{$filters['page']}-{$filters['per_page']}"])) {
            $collections = self::get(
                "/users/{$this->username}/collections",
                ['query' => ['page' => $filters['page'], 'per_page' => $filters['per_page']]]
            );
        
            $this->collections["{$filters['page']}-{$filters['per_page']}"] = [
                'body' => self::getArray($collections->getBody(), __NAMESPACE__.'\\Collection'),
                'headers' => $collections->getHeaders()
            ];
        }

        return new ArrayObject(
            $this->collections["{$filters['page']}-{$filters['per_page']}"]['body'],
            $this->collections["{$filters['page']}-{$filters['per_page']}"]['headers']
        );
    }

    /**
     * Retrieve all the photos liked by a specific user on a given page.
     * Returns an ArrayObject that contains Photo object
     *
     * @param  array $filters Filters.
     *
     * @return ArrayObject of Photos
     */
    public function likes($filters = [])
    {
        if (! isset($this->likes["{$filters['page']}-{$filters['per_page']}-{$filters['order_by']}"])) {
            $likes = self::get("/users/{$this->username}/likes", [
                'query' => ['page' => $filters['page'], 'per_page' => $filters['per_page'], 'order_by' => $filters['order_by']]
            ]);
        
            $this->likes["{$filters['page']}-{$filters['per_page']}-{$filters['order_by']}"] = [
                'body' => self::getArray($likes->getBody(), __NAMESPACE__.'\\Photo'),
                'headers' => $likes->getHeaders()
            ];
        }

        return new ArrayObject(
            $this->likes["{$filters['page']}-{$filters['per_page']}-{$filters['order_by']}"]['body'],
            $this->likes["{$filters['page']}-{$filters['per_page']}-{$filters['order_by']}"]['headers']
        );
    }

     /**
     * Retrieve a User object of the logged-in user.
     *
     * @return User
     */
    public static function current()
    {
        $user = json_decode(self::get("/me")->getBody(), true);
        
        return new self($user);
    }

    /**
     * Update specific parameters on the logged-in user
     *
     * @param    array $parameters Array containing the parameters to update
     * @return void
     */
    public function update(array $parameters)
    {
        json_decode(self::put("/me", ['query' => $parameters])->getBody(), true);
        parent::update($parameters);
    }

    /**
     * Return url for user's portfolio page
     * @param $username
     * @return string
     */
    public static function portfolio($username)
    {
        $user = json_decode(self::get("/users/{$username}/portfolio")->getBody(), true);
        return $user['url'];
    }

    /**
     * Return statistics for user
     * @param string $resolution
     * @param int $quantity
     * @return ArrayObject
     */
    public function statistics($resolution = 'days', $quantity = 30)
    {
        $statistics = self::get("users/{$this->username}/statistics", ['query' => ['resolution' => $resolution, 'quantity' => $quantity]]);
        $statisticsArray = self::getArray($statistics->getBody(), Stat::class);
        return new ArrayObject($statisticsArray, $statistics->getHeaders());
    }
}
