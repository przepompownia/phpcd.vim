<?php

namespace PHPCD\Element\ObjectElement;

interface PropertyInfo extends ObjectElement
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
