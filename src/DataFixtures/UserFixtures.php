<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const USER_NB_TUPLES = 10;

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }
    public function load(ObjectManager $manager): void
    {
        for($i=1; $i<=self::USER_NB_TUPLES; $i++)
        {
            $user = (new User())
                ->setEmail("mail.$i@mail.com");
            $user->setPassword($this->passwordHasher->hashPassword($user, "password$i"));
            $manager->persist($user);
        }
        
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['independant','user'];
    }
}
