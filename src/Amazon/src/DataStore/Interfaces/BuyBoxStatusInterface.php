<?php

namespace rollun\amazon\DataStore\Interfaces;

use rollun\datastore\DataStore\Interfaces\DateTimeInterface;

interface BuyBoxStatusInterface extends ListingsInterface, DateTimeInterface
{
    /**
     * Status of BuyBox
     */
    const FIELD_IN_BUYBOX = "in_buybox";
}