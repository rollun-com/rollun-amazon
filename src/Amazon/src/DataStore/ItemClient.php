<?php


namespace rollun\amazon\DataStore;

use ApaiIO\ApaiIO;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\Search;
use rollun\amazon\DataStore\Interfaces\ListingsInfoInterface;
use rollun\datastore\DataStore\DataStoreException;
use rollun\datastore\DataStore\Traits\NoSupportCountTrait;
use rollun\datastore\DataStore\Traits\NoSupportCreateTrait;
use rollun\datastore\DataStore\Traits\NoSupportDeleteAllTrait;
use rollun\datastore\DataStore\Traits\NoSupportDeleteTrait;
use rollun\datastore\DataStore\Traits\NoSupportGetIdentifier;
use rollun\datastore\DataStore\Traits\NoSupportHasTrait;
use rollun\datastore\DataStore\Traits\NoSupportIteratorTrait;
use rollun\datastore\DataStore\Traits\NoSupportUpdateTrait;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Query;

/**
 * Class ItemClient
 * Client for amazon Product advertising api (ItemLookup and ItemSearchMethod)
 * @package rollun\amazon\ProductAdverstising\Client
 */
class ItemClient implements ListingsInfoInterface
{
    use NoSupportDeleteAllTrait;
    use NoSupportDeleteTrait;
    use NoSupportUpdateTrait;
    use NoSupportCreateTrait;
    use NoSupportHasTrait;
    use NoSupportIteratorTrait;
    use NoSupportGetIdentifier;
    use NoSupportCountTrait;

    /**
     * @var ApaiIO
     */
    private $apaiIO;

    /**
     * ItemSearch constructor.
     * @param ApaiIO $apaiIO
     */
    public function __construct(ApaiIO $apaiIO)
    {
        $this->apaiIO = $apaiIO;
    }

    /**
     * @return Search
     */
    protected function initItemSearchRequest()
    {
        $search = new Search();
        $search->setResponseGroup(['SalesRank', 'OfferFull', 'Large']);
        $search->setCategory("Automotive");
        return $search;
    }

    /**
     * @return Lookup
     */
    protected function initItemLookupRequest()
    {
        $lookup = new Lookup();
        $lookup->setResponseGroup(['SalesRank', 'OfferFull', 'Large']);
        $lookup->setCondition("New");
        return $lookup;
    }


    /**
     * Handle query
     * And(Eq(brand,...),Eq(category,...)) - ItemSearch
     * Or(Eq(asin,...), ...) - ItemLookup (10 ids max)
     * Or(Eq(mpn,...), ...) - ItemSearch
     * Or(Eq(brand,...), ...) - ItemSearch
     * Eq(asin,...) - ItemLookup
     * Eq(mpn,...) - ItemSearch
     * Eq(brand,...) - ItemSearch
     * @param AbstractQueryNode $queryNode
     * @return array [AbstractOperation, array["param" => [...]]]
     * @throws DataStoreException
     */
    protected function analyzeQuery(AbstractQueryNode $queryNode)
    {
        $filters = [];
        //TODO: rewrite this...
        switch ($queryNode) {
            case $queryNode instanceof AndNode:
                //
                //And(Eq(brand,...),Eq(category,...)) - ItemSearch
                //
                $queries = $queryNode->getQueries();
                if (count($queries) > 2) {
                    throw new DataStoreException("Query not valid.");
                }
                $request = $this->initItemSearchRequest();
                foreach ($queries as $queryNode) {
                    if (!$queryNode instanceof EqNode) {
                        throw new DataStoreException("Query not valid.");
                    }
                    if ($queryNode->getField() == static::FIELD_BRAND) {
                        $request->setKeywords($queryNode->getValue());
                        $filters[$queryNode->getField()][] = $queryNode->getValue();
                    } elseif ($queryNode->getField() == static::FIELD_CATEGORY) {
                        $request->setCategory($queryNode->getValue());
                        $filters[$queryNode->getField()][] = $queryNode->getValue();
                    } else {
                        throw new DataStoreException("Query not valid.");
                    }
                }

                break;
            case $queryNode instanceof OrNode:
                //
                //Or(Eq(asin,...), ...) - ItemLookup (10 ids max)
                //Or(Eq(mpn,...), ...) - ItemSearch
                //Or(Eq(brand,...), ...) - ItemSearch
                //
                $queries = $queryNode->getQueries();
                $values = "";
                $fieldName = null;
                foreach ($queries as $queryNode) {
                    if (!$queryNode instanceof EqNode) {
                        throw new DataStoreException("Query not valid.");
                    }
                    if (is_null($fieldName)) {
                        $fieldName = $queryNode->getField();
                    }
                    if ($queryNode->getField() != $fieldName) {
                        throw new DataStoreException("Query not valid.");
                    }
                    $values .= "{$queryNode->getValue()},";
                    $filters[$queryNode->getField()][] = $queryNode->getValue();
                }
                if ($fieldName == static::FIELD_ASIN) {
                    $request = $this->initItemLookupRequest();
                    $request->setItemId(trim($values, ","));
                } elseif ($fieldName == static::FIELD_MPN || $fieldName == static::FIELD_BRAND) {
                    $request = $this->initItemSearchRequest();
                    $request->setKeywords(trim($values, ","));

                } else {
                    throw new DataStoreException("Query not valid.");
                }
                break;
            case $queryNode instanceof EqNode:
                //
                //Eq(asin,...) - ItemLookup
                //Eq(mpn,...) - ItemSearch
                //Eq(brand,...) - ItemSearch
                //
                switch ($queryNode->getField()) {
                    case static::FIELD_ASIN:
                        $request = $this->initItemLookupRequest();
                        $request->setItemId($queryNode->getValue());
                        break;
                    case static::FIELD_BRAND:
                        $request = $this->initItemSearchRequest();
                        $request->setKeywords($queryNode->getValue());
                        break;
                    case static::FIELD_MPN:
                        $request = $this->initItemSearchRequest();
                        $request->setKeywords($queryNode->getValue());
                        break;
                    default:
                        throw new DataStoreException("Query not valid.");
                }
                $filters[$queryNode->getField()][] = $queryNode->getValue();
                break;
            default:
                throw new DataStoreException("Query not valid.");
        }
        return [$request, $filters];
    }


    /**
     * {@inheritdoc}
     */
    public function query(Query $query)
    {
        list($request, $params) = $this->analyzeQuery($query->getQuery());
        $response = $this->apaiIO->runOperation($request);
        $items = $this->processResponse($response, $params);
        return $items;
    }

    /**
     * Check how much items is in response and call parseItemData accordingly
     * @param array $response
     * @param array $filters ["param" => ["expected values"...]]
     * @return array|null
     */
    protected function processResponse($response, array $filters)
    {
        $result = [];
        //if request is valid and there is no errors in response
        if ((isset($response["Items"]["Request"]["IsValid"]) && $response["Items"]["Request"]["IsValid"] == "False")
            || isset($response["Items"]["Request"]["Errors"])) {
            //TODO: Logged error
            return $result;
        }
        $items = isset($response['Items']['Item'][0]) ? $response['Items']['Item'] : [$response['Items']['Item']];
        foreach ($items as $item) {
            foreach ($filters as $param => $values) {
                if ($this->confirmItem($item, $param, $values)) {
                    $result[] = $this->parseItemData($item);
                }
            }
        }

        return $result;
    }

    /**
     * Confirm that item matches the exact request
     * @param array $item
     * @param string $param
     * @param array $values
     * @return bool
     */
    protected function confirmItem(array $item, $param, array $values)
    {
        return isset($item['ItemAttributes'][$param]) &&  in_array($item['ItemAttributes'][$param], $values);
    }

    /**
     * Return Item by 'id'
     *
     * Method return null if item with that id is absent.
     * Format of Item - Array("id"=>123, "field1"=value1, ...)
     *
     * @param int|string $id PrimaryKey
     * @return array|null
     */
    public function read($id)
    {
        $query = new Query();
        $query->setQuery(new EqNode(static::FIELD_ASIN, $id));
        $result = $this->query($query);
        return empty($result) ? null : current($result);
    }

    /**
     * Get data needed from an item
     * @param array $item
     * @return array|null
     */
    protected function parseItemData($item)
    {
        $itemData = [
            static::FIELD_ASIN => $item['ASIN'],
            static::FIELD_SALES_RANK => (isset($item['SalesRank']) ? $item['SalesRank'] : 0),
            static::FIELD_BRAND => (isset($item['ItemAttributes']['Brand']) ? $item['ItemAttributes']['Brand']
                : (isset($item['ItemAttributes']['Feature']) ? $item['ItemAttributes']['Feature'] : null)),
            static::FIELD_PRODUCT_NAME => $item['ItemAttributes']['Title'],
            static::FIELD_BUYBOX_PRICE => (isset($item['ItemAttributes']['ListPrice']['Amount']) ? $item['ItemAttributes']['ListPrice']['Amount'] / 100 : 0),
            static::FIELD_LOWEST_PRICE => (isset($item['OfferSummary']['LowestNewPrice']['Amount']) ? $item['OfferSummary']['LowestNewPrice']['Amount'] / 100 : 0),
            static::FIELD_PART_NUMBER => (isset($item['ItemAttributes']['PartNumber']) ? $item['ItemAttributes']['PartNumber'] : null),
            static::FIELD_MPN => (isset($item['ItemAttributes']['MPN']) ? $item['ItemAttributes']['MPN'] : null),
            static::FIELD_UPC => (isset($item['ItemAttributes']['UPCList']['UPCListElement']) && (gettype($item['ItemAttributes']['UPCList']['UPCListElement']) == 'array') ?
                join(",", $item['ItemAttributes']['UPCList']['UPCListElement']) : null),
            static::FIELD_MODEL => (isset($item['ItemAttributes']['Model']) ? $item['ItemAttributes']['Model'] : null),
            static::FIELD_BUYBOX_MERCHANT => (isset($item['Offers']['Offer']['Merchant']['Name']) ? $item['Offers']['Offer']['Merchant']['Name'] : null),
            static::FIELD_IS_PRIME => (isset($item['Offers']['Offer']['OfferListing']['IsEligibleForPrime']) ? true : false),
            static::FIELD_TOTAL_OFFERS => $item['Offers']['TotalOffers'],
        ];
        return $itemData;
    }
}