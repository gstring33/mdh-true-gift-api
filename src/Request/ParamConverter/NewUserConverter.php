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

class NewUserConverter implements ParamConverterInterface
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
        $user = $this->convertNewUser($request);
        $request->attributes->set($configuration->getName(), $user);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getName() === 'new-user';
    }

    /**
     * @param Request $request
     * @return User
     */
    private function convertNewUser (Request $request) : User
    {
        /** @var UserModel $userModel */
        $userModel = $this->decoder->jsonDecode($request->getContent(), UserModel::class);

        $list = (new GiftList())
            ->setUuid(uniqid('', false))
            ->setIsPublished(0);

        $user = (new User())
            ->setFirstname($userModel->firstname)
            ->setLastname($userModel->lastname)
            ->setEmail($userModel->email)
            ->setIsActive(0)
            ->setUuid(uniqid('', false))
            ->setGiftList($list);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $userModel->password
        );
        $user->setPassword($hashedPassword);

        $roles = !isset($userModel->roles) ? ['ROLE_USER'] : $userModel->roles;
        $user->setRoles($roles);

        return $user;
    }
}