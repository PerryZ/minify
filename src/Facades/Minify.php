<?php

namespace PerryvanderMeer\Minify\Facades;

use Illuminate\Support\Facades\Facade;

class Minify extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor ()
    {
        return 'minify';
    }
}
