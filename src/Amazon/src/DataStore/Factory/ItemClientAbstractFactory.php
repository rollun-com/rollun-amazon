<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 18.02.18
 * Time: 2:04 PM
 */

namespace rollun\amazon\ProductAdverstising\DataStore\Factory;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use rollun\amazon\DataStore\ItemClient;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class ItemClientAbstractFactory implements AbstractFactoryInterface
{
    const KEY = "dataStore";

    const KEY_APAI_IO = "apaiIO";

    const KEY_CLASS = "class";

    const DEFAULT_CLASS = ItemClient::class;

    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        try {
            $config = $container->get("config");
        } catch (NotFoundExceptionInterface $e) {return false;
        } catch (ContainerExceptionInterface $e) {return false;
        }
        return (
          isset($config[static::KEY][$requestedName][static::KEY_CLASS]) &&
          is_a($config[static::KEY][$requestedName][static::KEY_CLASS], static::DEFAULT_CLASS, true)
        );
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ItemClient
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get("config");
        $factoryConfig = $config[static::KEY][$requestedName];
        if(isset($factoryConfig[static::KEY_APAI_IO])) {
            throw new ServiceNotCreatedException("Not set apaiIO service name in $requestedName dataStore service config.");
        }
        $apaiIO = $container->get($factoryConfig[static::KEY_APAI_IO]);
        return new ItemClient($apaiIO);
    }
}