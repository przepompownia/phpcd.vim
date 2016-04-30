<?php

namespace PHPCD\Fixtures\ClassNamesAndAliases\Services;

use PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Image;
use PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Category as Cat;
use A\B\{C as X, D, E as F};

class Image
{
    /**
     * @return string 'PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Image'
     */
    public function whichClassIsImage()
    {
        return get_class(new Image);
    }
}
