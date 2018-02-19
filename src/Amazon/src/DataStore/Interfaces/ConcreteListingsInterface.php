<?php


namespace rollun\amazon\DataStore\Interfaces;

use rollun\datastore\DataStore\Interfaces\DateTimeInterface;

interface ConcreteListingsInterface extends ListingsInterface, DateTimeInterface
{
    /**
     * Stock keeping unit(SKU)
     * Unique identify seller listing.
     */
    const FIELD_SKU = "sku";
}