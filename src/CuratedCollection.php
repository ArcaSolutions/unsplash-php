<?php

namespace Crew\Unsplash;

/**
 * Class CuratedCollection
 * @package Crew\Unsplash
 * @property int $id
 */
class CuratedCollection extends Endpoint
{
    private $photos;
    private $parameters;

    public function __construct(array $parameters = [])
    {
        parent::__construct($parameters);
        $this->parameters = $parameters;
    }

    public function getParameters() {
        return $this->parameters;
    }

    /**
     * Retrieve a specific curated batch
     *
     * @param  int $id Id of the curated batch
     * @return CuratedCollection
     */
    public static function find($id)
    {
        $curatedBatch = json_decode(self::get("/collections/curated/{$id}")->getBody(), true);

        return new self($curatedBatch);
    }

    /**
     * Retrieve all curated batches for a given page
     *
     * @param  array $filters Filters.
     *
     * @param bool $returnArrayObject Does function should return collections as ArrayObject (backward compatibility)
     * @return ArrayObject|PageResult of CuratedBatch
     */
    public static function all($filters = [], $returnArrayObject = true)
    {
        $curatedBatches = self::get("/collections/curated", ['query' => $filters]);

        $curatedBatchesArray = self::getArray($curatedBatches->getBody(), get_called_class());
        $arrayObjects = new ArrayObject($curatedBatchesArray, $curatedBatches->getHeaders());
        if($returnArrayObject) {
            return $arrayObjects;
        }
        $pageResults['results'] = [];
        foreach($curatedBatchesArray as $collection){
            $pageResults['results'][] = $collection->getParameters();
        }
        $pageResults['total_pages'] = $arrayObjects->totalPages();
        $pageResults['total'] = $arrayObjects->count();

        return self::getPageResult(json_encode($pageResults), $curatedBatches->getHeaders(), CuratedCollection::class);
    }

    /**
     * Retrieve all the photos for a specific curated batch
     * Returns an ArrayObject that contains Photo objects.
     *
     * @param bool $returnArrayObject Does function should return collections as ArrayObject (backward compatibility)
     * @return ArrayObject|PageResult of Photo
     */
    public function photos($returnArrayObject = true)
    {
        if (! isset($this->photos)) {
            $photos = self::get("/collections/curated/{$this->id}/photos");

            $this->photos = [
                'body' => self::getArray($photos->getBody(), __NAMESPACE__.'\\Photo'),
                'headers' => $photos->getHeaders()
            ];
        }

        $arrayObjects = new ArrayObject($this->photos['body'], $this->photos['headers']);
        if($returnArrayObject) {
            return $arrayObjects;
        }

        $pageResults['results'] = [];
        foreach($arrayObjects as $photo){
            if(is_array($photo)) {
                $photo = new Photo($photo);
            }
            $pageResults['results'][] = $photo->getParameters();
        }
        $pageResults['total_pages'] = $arrayObjects->totalPages();
        $pageResults['total'] = $arrayObjects->count();

        return self::getPageResult(json_encode($pageResults), $photos->getHeaders(), Photo::class);
    }
}
