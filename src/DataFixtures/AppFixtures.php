<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\IngredientGroup;
use App\Entity\Recipe;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

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
        // tests? where we go we don't need tests!
        $recipe->setModified(new \DateTime('2035-10-26 01:21:00'));
        $manager->persist($recipe);

        /** @var ClassMetadataInfo $metadata */
        $metadata = $manager->getClassMetaData(get_class($recipe));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

        $tag = new Tag();
        $tag->setLabel('bacon');
        $manager->persist($tag);

        for ($i = 2; $i <= 30; $i++) {
            $recipe = new Recipe();
            $recipe->setId($i);
            $recipe->setLabel('Rezept');
            $recipe->setDescription('TestRecipe');
            $recipe->setEffort(10);
            $recipe->setTags([$tag]);
            $recipe->setModified((new \DateTime('2035-10-26 01:21:00'))->modify("-$i seconds"));
            $manager->persist($recipe);
        }

        $recipe = new Recipe();
        $recipe->setId(50);
        $recipe->setLabel('UnicornLasagna');
        $recipe->setDescription('With nooodles');
        $recipe->setEffort(10);
        $group = new IngredientGroup();
        $group->addIngredient(new Ingredient($group, 'Beans'));
        $recipe->addIngredientGroup($group);
        $recipe->setTags([$tag]);
        $manager->persist($recipe);


        $manager->flush();
    }
}