<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 18.10.17
 * Time: 11:56
 */

namespace rollun\amazon\Factory\Installer;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use rollun\amazon\DataStore\Factory\ItemClientAbstractFactory;
use rollun\amazon\Factory\AWSClientAbstractFactory;
use rollun\installer\Install\InstallerAbstract;

class ItemClientInstaller extends InstallerAbstract
{

    /**
     * install
     * @return array
     */
    public function install()
    {
        return [
            "dependencies" => [
                'abstract_factories' => [
                    ItemClientAbstractFactory::class
                ],
                'invokables' => [],
                'factories' => [],
                "aliases" => []
            ]
        ];
    }

    /**
     * Clean all installation
     * @return void
     */
    public function uninstall()
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * Return true if install, or false else
     * @return bool
     */
    public function isInstall()
    {
        try {
            $config = $this->container->get("config");
        } catch (NotFoundExceptionInterface $e) { return false;
        } catch (ContainerExceptionInterface $e) { return false;
        }
        return (
        in_array(ItemClientAbstractFactory::class, $config["dependencies"]["abstract_factories"])
        );
    }

    /**
     * Return string with description of installable functional.
     * @param string $lang ; set select language for description getted.
     * @return string
     */
    public function getDescription($lang = "en")
    {
        return "Add factory to amazon product advertising client service.";
    }
}
