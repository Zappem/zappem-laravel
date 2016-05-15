<?php namespace Zappem\ZappemLaravel;

use Illuminate\Support\Facades\Facade;

class ZappemFacade extends Facade {

    protected static function getFacadeAccessor() { return 'zappem'; }

}