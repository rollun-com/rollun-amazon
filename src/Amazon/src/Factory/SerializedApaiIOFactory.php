<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 18.02.18
 * Time: 2:52 PM
 */

namespace rollun\amazon\Factory;

use ApaiIO\Request\GuzzleRequest;
use ApaiIO\ResponseTransformer\XmlToArray;
use GuzzleHttp\Client;
use Interop\Container\ContainerInterface;
use rollun\amazon\SerializedApaiIO;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\ApaiIO;


/**
 * Class ApaiIOFactory
 *
 * <code>
 * 'ApaiIO' => [
 *     'country' => 'com', // The country could be one of the following: de, com, co.uk, ca, fr, co.jp, it, cn, es, in, com.br, com.mx, com.au
 *     'access_key' => '',
 *     'secret_key' => '',
 *     'associate_tag' => '',
 * ],
 * </code>
 *
 * @package rollun\amazonItemSearch\Client\Factory
 */
class SerializedApaiIOFactory implements FactoryInterface
{
    const KEY = 'ApaiIOFactory';
    const KEY_COUNTRY = 'country';
    const KEY_ACCESS_KEY = 'access_key';
    const KEY_SECRET_KEY = 'secret_key';
    const KEY_ASSOCIATE_TAG = 'associate_tag';

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // These can to be removed to config and/or be used via own factories
        $conf = new GenericConfiguration();
        $client = new Client();
        $req = new GuzzleRequest($client);
        $responseTransformer = new XmlToArray();
        $config = $container->get('config');
        if (!isset($config[static::KEY])) {
            throw new ServiceNotFoundException("There is no config for the ApaiIO client in the config");
        }
        $serviceConfig = $config[static::KEY];
        if (
            !isset($serviceConfig[static::KEY_COUNTRY]) ||
            !isset($serviceConfig[static::KEY_ACCESS_KEY]) ||
            !isset($serviceConfig[static::KEY_SECRET_KEY]) ||
            !isset($serviceConfig[static::KEY_ASSOCIATE_TAG])
        ) {
            throw new ServiceNotCreatedException("The service wasn't created because required parameters weren't found");
        }
        $conf
            ->setCountry($serviceConfig[static::KEY_COUNTRY])
            ->setAccessKey($serviceConfig[static::KEY_ACCESS_KEY])
            ->setSecretKey($serviceConfig[static::KEY_SECRET_KEY])
            ->setAssociateTag($serviceConfig[static::KEY_ASSOCIATE_TAG])
            ->setRequest($req)
            ->setResponseTransformer($responseTransformer);
        $instance = new SerializedApaiIO($conf);
        return $instance;
    }
}