<?php

namespace App\EventListener;

use App\Model\TokenMailModel;
use App\Services\Decoder;
use App\Services\Encoder;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener
{
    private ManagerRegistry $doctrine;
    private Encoder $encoder;
    private Decoder $decoder;

    public function __construct(
        ManagerRegistry $doctrine,
        Encoder $encoder,
        Decoder $decoder,
    ){
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
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
        $event->setData($data);
    }
}