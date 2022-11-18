<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tag1 = new Tag();
        $tag1->setTitulo('Personales');
        $manager->persist($tag1);

        $tag2 = new Tag();
        $tag2->setTitulo('Ocio');
        $manager->persist($tag2);

        $tag3 = new Tag();
        $tag3->setTitulo('Trabajo/Creativas');
        $manager->persist($tag3);

        $manager->flush();
    }
}
