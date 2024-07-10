<?php

namespace App\DataFixtures;

use App\Entity\{Animaux,Habitats};
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AnimauxFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        for($i=1; $i<=10; $i++)
        {
            $habitat = $this->getReference(HabitatsFixtures::HABITATS_REFERENCE.random_int(1,10));

            $animaux = (new Animaux())
                ->setPrenom("Prenom $i")
                ->setRace("Race $i")
                ->setHabitat($habitat);
            $manager->persist($animaux);
        }
        
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [HabitatsFixtures::class];
    }

    public static function getGroups(): array
    {
        return ['HA'];
    }
}
