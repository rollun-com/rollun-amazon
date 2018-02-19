<?php


namespace rollun\amazon\DataStore\Interfaces;


interface ListingsInfo extends ListingsInterface
{
    /**
     * Require item manufacture part number
     */
    const FIELD_MPN = "manufacture_part_number";

    /**
     * Item part number
     */
    const FIELD_PART_NUMBER = "part_umber";

    /**
     * Non-require
     * Universal part code
     */
    const FIELD_UPC = "upc";

    /**
     * Listing name, make contains any character
     */
    const FIELD_PRODUCT_NAME = "product_name";

    /**
     * Listing item model name
     */
    const FIELD_MODEL = "model";

    /**
     * listing brand, make non accordance with real brand name
     */
    const FIELD_BRAND = "brand";

    /**
     * Rank of sale
     */
    const FIELD_SALES_RANK = "sales_rank";

    /**
     * Price in buybox
     */
    const FIELD_BUYBOX_PRICE = "buybox_price";

    /**
     * Lowest price in amazon
     */
    const FIELD_LOWEST_PRICE = "lowest_price";

    /**
     * Listing in prime
     */
    const FIELD_IS_PRIME = "is_prime";

    /**
     * Listing total offers
     */
    const FIELD_TOTAL_OFFERS  = "total_offers";

    /**
     * Name of buyBox merchant
     */
    const FIELD_BUYBOX_MERCHANT = "buybox_merchant";

    /**
     * Listing category
     */
    const FIELD_CATEGORY = "category";
}