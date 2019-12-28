<?php


namespace App\Event;


use App\Entity\User;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserDoctrineEvent
{
    /**
     * @var UserPasswordEncoderInterface
     *
     */
    private $encoder;

    /**
     * UserDoctrineEvent constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct($encoder)
    {
        $this->encoder = $encoder;
    }


    public function prePersist(User $user, LifecycleEventArgs $eventArgs){

        $user->setPassword($this->encoder->encodePassword($user, $user->getPlainPassword()));
        $user->eraseCredentials();
    }
}