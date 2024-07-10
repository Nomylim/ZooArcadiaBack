<?php

namespace App\DataFixtures;

use App\Entity\Habitats;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class HabitatsFixtures extends Fixture implements FixtureGroupInterface
{
    public const HABITATS_REFERENCE = "habitat";
    public const HABITATS_NB_TUPLES = 10;

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();
        for($i=1; $i<=self::HABITATS_NB_TUPLES; $i++)
        {
            $habitat = (new Habitats())
                ->setName($faker->country())
                ->setDescription($faker->text());
            $manager->persist($habitat);
            $this->addReference(self::HABITATS_REFERENCE.$i, $habitat);
        }
        
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['HA'];
    }
}
