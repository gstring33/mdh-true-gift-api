<?php

namespace App\Normalizer;

use App\Entity\User;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizer implements NormalizerInterface
{

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        $data['uuid'] = $object->getUuid();
        $data['firstname'] = $object->getFirstname();
        $data['lastname'] = $object->getLastname();
        $data['email'] = $object->getEmail();
        $data['isActive'] = $object->isActive();
        $data['roles'] = $object->getRoles();
        $data['lastConnectionAt'] = $object->getLastConnectionAt();

        return $data;
    }

    public function supportsNormalization(mixed $data, string $format = null)
    {
        return $data instanceof User;
    }
}