<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 20.02.18
 * Time: 1:12 AM
 */

namespace rollun\amazon;


use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Request\GuzzleRequest;
use ApaiIO\ResponseTransformer\XmlToArray;
use GuzzleHttp\Client;
use rollun\utils\Php\Serializer;

class SerializedApaiIO extends ApaiIO implements \Serializable
{

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        $data = [
            "Country" => $this->configuration->getCountry(),
            "AccessKey" => $this->configuration->getAccessKey(),
            "SecretKey" => $this->configuration->getSecretKey(),
            "AssociateTag" => $this->configuration->getAssociateTag(),
        ];
        return Serializer::phpSerialize($data);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $data = Serializer::phpUnserialize($serialized);
        // These can to be removed to config and/or be used via own factories
        $conf = new GenericConfiguration();
        $client = new Client();
        $req = new GuzzleRequest($client);
        $responseTransformer = new XmlToArray();
        $conf
            ->setCountry($data["Country"])
            ->setAccessKey($data["AccessKey"])
            ->setSecretKey($data["SecretKey"])
            ->setAssociateTag($data["AssociateTag"])
            ->setRequest($req)
            ->setResponseTransformer($responseTransformer);
        $this->configuration = $conf;
    }
}