<?php

namespace App\Request\ParamConverter;

use App\Entity\GiftList;
use App\Entity\User;
use App\Model\UserModel;
use App\Repository\UserRepository;
use App\Services\Decoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserConverter implements ParamConverterInterface
{
    private UserRepository $userRepository;
    private Decoder $decoder;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepository $userRepository,
        Decoder $decoder,
        UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->decoder = $decoder;
        $this->passwordHasher = $passwordHasher;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $user = $this->convertExistingUser($request);
        $request->attributes->set($configuration->getName(), $user);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getName() === 'user';
    }

    /**
     * @param Request $request
     * @return User|null
     */
    private function convertExistingUser (Request $request) : ?User
    {
        $uuid = $request->attributes->get('uuid');
        $user = $this->userRepository->findOneBy(['uuid' => $uuid]);

        return $user;
    }
}