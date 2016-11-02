<?php

namespace PHPCD\ObjectElementInfo;

interface PropertyInfo extends ObjectElementInfo
{
    /**
     * @return array
     */
    public function getAllowedTypes();

    /**
     * @return array
     */
    public function getAllowedNonTrivialTypes();
}
