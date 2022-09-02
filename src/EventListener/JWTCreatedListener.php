<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
    /** @var RequestStack */
    private RequestStack $requestStack;
    /** @var UserRepository */
    private UserRepository $userRepository;

    public function __construct(RequestStack $requestStack, UserRepository $userRepository)
    {
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
    }

    /**
     * @param JWTCreatedEvent $event
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $content = json_decode($request->getContent(), true);
        $user = $this->userRepository->findOneBy(['email' => $content['email']]);
        $payload = $event->getData();
        $payload['uuid'] = $user->getUuid();
        $payload['isActive'] = $user->isActive();
        $event->setData($payload);
    }
}