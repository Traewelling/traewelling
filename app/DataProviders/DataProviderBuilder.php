<?php

namespace App\DataProviders;

class DataProviderBuilder
{
    public function build(?bool $cache = null): DataProviderInterface {
        return new Bahn();


        if ($cache === true || ($cache === null && config('trwl.cache.hafas'))) {
            return new CachedHafas();
        }

        return new Hafas();
    }
}
