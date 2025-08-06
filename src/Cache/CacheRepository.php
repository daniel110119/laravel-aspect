<?php

namespace Lugege\LaravelAspect\Cache;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
class CacheRepository extends Repository
{
    public function __construct(Store $store)
    {
        parent::__construct($store);
    }
}
