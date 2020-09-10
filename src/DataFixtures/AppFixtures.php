<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\IngredientGroup;
use App\Entity\Recipe;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
		$rick = new User();
		$rick->setEmail('rick@rick.com');
        // set 'supersecurepassword!' as password
		$rick->setPassword('$2a$04$CYpUWq8QCVnXaDKBZxzbBeeVc4WmdgZ8nKUTpBYkbvbg7oRkqnmOu');
		$rick->setUsername('rick');
        $manager->persist($rick);

        $morty = new User();
		$morty->setEmail('morty@morty.com');
        // set 'supersecurepassword!' as password
		$morty->setPassword('$2a$04$CYpUWq8QCVnXaDKBZxzbBeeVc4WmdgZ8nKUTpBYkbvbg7oRkqnmOu');
		$morty->setUsername('morty');
        $manager->persist($morty);


        $recipe = new Recipe();
        $recipe->setId(1);
        $recipe->setLabel('RezeptFixed');
        $recipe->setDescription('123 test');
        $recipe->setEffort(999);
        // tests? where we go we don't need tests!
        $recipe->setModified(new \DateTime('2035-10-26 01:21:00'));
		$recipe->setUser($rick);
        $manager->persist($recipe);

        $lilBits = new Recipe();
		$lilBits->setId(2);
		$lilBits->setLabel('LilBits Eggs');
		$lilBits->setDescription('123 test');
		$lilBits->setEffort(999);
		$lilBits->setModified(new \DateTime('2035-10-26 01:21:00'));
		$lilBits->setUser($morty);
        $manager->persist($recipe);

        /** @var ClassMetadataInfo $metadata */
        $metadata = $manager->getClassMetaData(get_class($recipe));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

        $tag = new Tag();
        $tag->setLabel('bacon');
		$tag->setUser($rick);
        $manager->persist($tag);

        for ($i = 101; $i <= 130; $i++) {
            $recipe = new Recipe();
            $recipe->setId($i);
            $recipe->setLabel('Rezept');
            $recipe->setDescription('TestRecipeSearchDescription');
            $recipe->setEffort(10);
            $recipe->setTags([$tag]);
            $recipe->setModified((new \DateTime('2035-10-26 01:21:00'))->modify("-$i seconds"));
			$recipe->setUser($rick);
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
		$recipe->setUser($rick);
        $manager->persist($recipe);


        $manager->flush();
    }
}