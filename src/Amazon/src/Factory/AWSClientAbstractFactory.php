<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 12.10.17
 * Time: 17:35
 */

namespace rollun\amazon\Factory;

use Aws\Sqs\SqsClient;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class AWSClientAbstractFactory implements AbstractFactoryInterface
{

    const KEY = AWSClientAbstractFactory::class;

    const KEY_REGION = "region";

    const KEY_VERSION = "version";

    const KEY_CREDENTIALS = "credentials";

    const KEY_CREDENTIALS_KEY = "key";

    const KEY_CREDENTIALS_SECRET = "secret";

    /**
     * [
     *   "sqsClient" => [
     *      'credentials' => [
     *          'key'    => "",
     *          'secret' => "",
     *      ],
     *      //'profile' => 'service_suppliers', and save credentials to ~/.aws/credentials
     *      'region'  => 'us-west-2',
     *      'version' => 'latest'
     *   ]
     * ]
     * cat ~/.aws/credentials
     * [service_suppliers]
     * aws_access_key_id =
     * aws_secret_access_key =
     *
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getServiceConfig(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get("config");
        $options = isset($options) ? $options : [];
        $serviceConfig = isset($config[static::KEY][$requestedName]) ? $config[static::KEY][$requestedName] : [];
        $serviceConfig = array_merge($serviceConfig, $options);
        return $serviceConfig;
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $this->getServiceConfig($container, $requestedName, $options);
        return new SqsClient($config);
    }

    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get("config");
        return isset($config[static::KEY][$requestedName]);
    }
}
