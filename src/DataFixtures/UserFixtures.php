<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    public function load(ObjectManager $manager): void
    {
        // SEGUNDO USER CON DUMMMY DATA
        $usuario = new User();
        $usuario->setEmail('user1@notas.com');
        $usuario->setPassword($this->passwordEncoder->encodePassword(
            $usuario,
            '123'
        ));
        $manager->persist($usuario);

        // SEGUNDO USER CON DUMMMY DATA
        $usuario2 = new User();
        $usuario2->setEmail('user2@notas.com');
        $usuario2->setPassword($this->passwordEncoder->encodePassword(
            $usuario2,
            '123'
        ));

        $manager->persist($usuario2);

        $manager->flush();
    }
}
