<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;


class JWTCreatedListener
{
    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        //The token information aboit data and user is retrieved when a user logs in.
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        // we add the id and the firstname of the user
        $data['user'] = array(
            'id' => $user->getId(),
            'firstname' => $user->getFirstName(),
        );

        //We hydrate the $event object with the previous data
        $event->setData($data);

        $header = $event->getHeader();
        $header['cty'] = 'JWT';

        $event->setHeader($header);
    }
}