<?php


namespace rollun\amazon\DataStore\Interfaces;


use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

interface ListingsInterface extends DataStoresInterface
{
    /**
     * Amazon Standard Identification Number
     */
    const FIELD_ASIN = "asin";
}