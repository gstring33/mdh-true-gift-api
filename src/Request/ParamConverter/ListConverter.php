<?php

namespace App\Request\ParamConverter;

use App\Repository\GiftListRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ListConverter implements ParamConverterInterface
{
    private JWTTokenManagerInterface $jwtManager;
    private GiftListRepository $giftListRepository;
    private TokenStorageInterface $tokenStorage;
    private UserRepository $userRepository;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        GiftListRepository $giftListRepository,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository
    )
    {
        $this->jwtManager = $jwtManager;
        $this->giftListRepository = $giftListRepository;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());
        $userUuid = $decodedJwtToken['uuid'];
        $list = $this->userRepository->findOneBy(['uuid' => $userUuid])->getGiftList();

        $request->attributes->set($configuration->getName(), $list);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getName() === 'list';
    }
}