<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{

/**
 * @param AuthenticationSuccessEvent $event
 */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
    //     $data = $event->getData();
    //     $user = $event->getUser();

    //     if (!$user instanceof User) {
    //         return;
    //     }

    //     $data['user'] = array(
    //         'id' => $user->getId(),
    //         'firstname' => $user->getFirstName(),
    //         'roles' => $user->getRoles(),
    //     );

    //     $event->setData($data);
    }
}