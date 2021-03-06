<?php

namespace Crew\Unsplash;

/**
 * Class Photo
 * @package Crew\Unsplash
 * @property int $id
 * @property array $user
 */
class Photo extends Endpoint
{
    private $photographer;
    private $parameters;

    public function __construct(array $parameters = [])
    {
        parent::__construct($parameters);
        $this->parameters = $parameters;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Retrieve the a photo object from the ID specified
     *
     * @param  string $id ID of the photo
     * @return Photo
     */
    public static function find($id)
    {
        $photo = json_decode(self::get("/photos/{$id}")->getBody(), true);

        return new self($photo);
    }

    /**
     * Retrieve all the photos on a specific page.
     *
     * @param  array $filters Filters.
     *
     * @return ArrayObject|PageResult of Photos
     */
    public static function all($filters = [], $returnArrayObject = true)
    {
        $photos = self::get("/photos", ['query' => $filters]);

        $photosArray = self::getArray($photos->getBody(), Photo::class);
        $arrayObjects = new ArrayObject($photosArray, $photos->getHeaders());
        if ($returnArrayObject) {
            return $arrayObjects;
        }
        $pageResults['results'] = [];
        foreach ($photosArray as $photo) {
            $pageResults['results'][] = $photo->getParameters();
        }
        $pageResults['total_pages'] = $arrayObjects->totalPages();
        $pageResults['total'] = $arrayObjects->count();

        return self::getPageResult(json_encode($pageResults), $photos->getHeaders(), Photo::class);
    }


    /**
     * Retrieve a single page from the list of the curated photos (front-page’s photos).
     *
     * @param  array $filters Filters.
     *
     * @return ArrayObject|PageResult of Photos
     */
    public static function curated($filters = [], $returnArrayObject = true)
    {
        $photos = self::get("/photos/curated", ['query' => $filters]);

        $photosArray = self::getArray($photos->getBody(), get_called_class());
        $arrayObjects = new ArrayObject($photosArray, $photos->getHeaders());
        if ($returnArrayObject) {
            return $arrayObjects;
        }
        $pageResults['results'] = [];
        foreach ($photosArray as $photo) {
            $pageResults['results'][] = $photo->getParameters();
        }
        $pageResults['total_pages'] = $arrayObjects->totalPages();
        $pageResults['total'] = $arrayObjects->count();

        return self::getPageResult(json_encode($pageResults), $photos->getHeaders(), Photo::class);
    }

    /**
     * Retrieve all the photos on a specific page depending on search results
     *
     * @param  array $filters Filters.
     *
     * @return ArrayObject of Photos
     */
    public static function search($filters = [])
    {
        $photos = self::get("/photos/search", ['query' => $filters]);

        $photosArray = self::getArray($photos->getBody(), get_called_class());

        return new ArrayObject($photosArray, $photos->getHeaders());
    }

    /**
     * Create a new photo. The user needs to connect their account and authorize the write_photo permission scope.
     *
     * @param  string $filePath Path of the file to upload
     * @throws Exception - if filePath does not exist
     * @return Photo
     */
    public static function create($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception(["{$filePath} has not been found"]);
        }

        $file = fopen($filePath, 'r');

        $photo = json_decode(
            self::post(
                "photos",
                [
                    'multipart' => [['name' => 'photo', 'contents' => $file]],
                    'headers' => ['Content-Length' => filesize($filePath)]
                ]
            )->getBody(),
            true
        );

        return new self($photo);
    }

    /**
     * Retrieve the user that uploaded the photo
     *
     * @return User
     */
    public function photographer()
    {
        if (!isset($this->photographer)) {
            $this->photographer = User::find($this->user['username']);
        }

        return $this->photographer;
    }

    /**
     * Retrieve a single random photo, given optional filters.
     *
     * @param $filters array Apply optional filters.
     * @return Photo
     */
    public static function random($filters = [])
    {
        $filters['featured'] = (isset($filters['featured']) && $filters['featured']) ? 'true' : null;

        $photo = json_decode(self::get('photos/random', ['query' => $filters])->getBody(), true);

        return new self($photo);
    }

    /**
     * Like the photo for the current user
     *
     * @return boolean
     */
    public function like()
    {
        self::post("/photos/{$this->id}/like");
        return true;
    }

    /**
     * Unlike the photo for the current user
     *
     * @return boolean
     */
    public function unlike()
    {
        self::delete("photos/{$this->id}/like");
        return true;
    }

    /**
     * Retrieve statistics for a photo
     *
     * @param string $resolution
     * @param int $quantity
     * @return ArrayObject
     */
    public function statistics($resolution = 'days', $quantity = 30)
    {
        $statistics = self::get("photos/{$this->id}/statistics", ['query' => ['resolution' => $resolution, 'quantity' => $quantity]]);
        $statisticsArray = self::getArray($statistics->getBody(), Stat::class);
        return new ArrayObject($statisticsArray, $statistics->getHeaders());
    }

    /**
     * Triggers a download for a photo
     * Required under API Guidelines
     * @return string - full-res photo URL for downloading
     */
    public function download()
    {
        $download_path = parse_url($this->links['download_location'], PHP_URL_PATH);
        $download_query = parse_url($this->links['download_location'], PHP_URL_QUERY);
        $link = self::get($download_path . "?" . $download_query);
        $linkClass = \GuzzleHttp\json_decode($link->getBody());
        return $linkClass->url;
    }

    /**
     * Update an existing photo
     * @param array $parameters
     * @return Photo
     */
    public function update(array $parameters = [])
    {
        json_decode(self::put("/photos/{$this->id}", ['query' => $parameters])->getBody(), true);
        parent::update($parameters);
    }
}
