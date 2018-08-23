<?php

namespace App\DataFixtures;

use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('test@test.com');
        // set 'supersecurepassword!' as password
        $user->setPassword('$2a$04$CYpUWq8QCVnXaDKBZxzbBeeVc4WmdgZ8nKUTpBYkbvbg7oRkqnmOu');
        $user->setUsername('testuser');
        $manager->persist($user);


        $recipe = new Recipe();
        $recipe->setId(1);
        $recipe->setLabel('RezeptFixed');
        $recipe->setDescription('123 test');
        $recipe->setEffort(999);
        $manager->persist($recipe);

        $metadata = $manager->getClassMetaData(get_class($recipe));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());


        for ($i = 2; $i <= 20; $i++) {
            $recipe = new Recipe();
            $recipe->setId($i);
            $recipe->setLabel('Rezept');
            $recipe->setDescription('123 test');
            $recipe->setEffort(10);
            $manager->persist($recipe);
        }

        $manager->flush();
    }
}