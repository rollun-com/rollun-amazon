<?php


namespace rollun\amazon\DataStore\Interfaces;


interface InventoryInterface extends ConcreteListingsInterface
{
    /**
     * Active listing status
     */
    const FIELD_IS_ACTIVE = "is_active";

    /**
     * Close listing status
     * Only if active status is false
     */
    const FIELD_IS_CLOSE = "is_close";

    /**
     * Listing price
     */
    const FIELD_PRICE = "price";

    /**
     * Count of item ready for sale
     */
    const FIELD_QUANTITY = "quantity";
}