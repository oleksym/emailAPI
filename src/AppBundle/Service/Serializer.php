<?php

namespace AppBundle\Service;

use Symfony\Component\Serializer\Serializer as Symfony_Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Serializer
{
    private $serializer;

    public function __construct()
    {
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $this->serializer = new Symfony_Serializer([$normalizer], [new JsonEncoder()]);
    }

    public function __call($name, $arguments)
    {
        if ($this->serializer) {
            return call_user_func_array([$this->serializer, $name], $arguments);
        }

        return;
    }
}
