<?php

namespace App\Request\ParamConverter;

use App\Entity\GiftList;
use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class UserConverter implements ParamConverterInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        if ($this->isNewCreatedUser($request)) {
            $user = $this->convertNewUser($request);
        } else {
            $user = $this->convertExistingUser($request);
        }

        $request->attributes->set($configuration->getName(), $user);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getName() === 'user';
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isNewCreatedUser (Request $request) : bool
    {
        return $request->isMethod('POST');
    }

    /**
     * @param Request $request
     * @return User
     */
    private function convertNewUser (Request $request) : User
    {
        $data = json_decode($request->getContent(), true);
        $list = (new GiftList())
            ->setUuid(uniqid('', false))
            ->setIsPublished(0);
        $user = (new User())
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setIsActive(0)
            ->setPlainTextPassword($data['password'])
            ->setUuid(uniqid('', false))
            ->setGiftList($list);
        $roles = !isset($roles['roles']) ? ['ROLE_USER'] : $roles['roles'];
        $user->setRoles($roles);

        return $user;
    }

    /**
     * @param Request $request
     * @return User
     */
    private function convertExistingUser (Request $request) : User
    {
        $uuid = $request->attributes->get('uuid');
        $user = $this->userRepository->findOneBy(['uuid' => $uuid]);

        return $user;
    }
}