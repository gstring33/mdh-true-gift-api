<?php

namespace App\Request\ParamConverter;

use App\Entity\GiftList;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class UserConverter implements ParamConverterInterface
{

    public function apply(Request $request, ParamConverter $configuration)
    {
        $data = json_decode($request->getContent(), true);
        $password = strtolower($data['firstname'][0] . $data['lastname']) . rand(0,999);
        $list = (new GiftList())
            ->setUuid(uniqid('', false))
            ->setIsPublished(0);
        $user = (new User())
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setIsActive(0)
            //->setRoles($data['roles'])
            ->setUuid(uniqid('', false))
            ->setPassword($password)
            ->setGiftList($list);

        $request->attributes->set($configuration->getName(), $user);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getName() === 'user';
    }
}