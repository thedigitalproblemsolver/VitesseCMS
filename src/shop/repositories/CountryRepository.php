<?php

namespace VitesseCms\Shop\Repositories;

/**
 * Class CountryRepository
 */
class CountryRepository extends AbstractCollectionRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new Country;
    }
}
