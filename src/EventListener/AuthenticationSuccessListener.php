<?php

namespace App\EventListener;

use App\Services\Decoder;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener
{
    private ManagerRegistry $doctrine;
    private Decoder $decoder;

    public function __construct(
        ManagerRegistry $doctrine,
        Decoder $decoder,
    ){
        $this->doctrine = $doctrine;
        $this->decoder = $decoder;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            throw new \Exception('An error occured during the login system');
        }
        $data['isActive'] = $user->isActive();
        $data['isAdmin'] = in_array('ROLE_ADMIN', $user->getRoles());
        $data['uuid'] = $user->getUuid();
        $data['firstname'] = $user->getFirstname();
        $data['lastname'] = $user->getLastname();
        $data['email'] = $user->getEmail();
        $event->setData($data);
    }
}