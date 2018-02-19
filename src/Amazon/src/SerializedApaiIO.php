<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 20.02.18
 * Time: 1:12 AM
 */

namespace rollun\amazon;


use ApaiIO\ApaiIO;

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
        return json_encode($this->configuration);
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
        $this->configuration = json_decode($serialized);
    }
}