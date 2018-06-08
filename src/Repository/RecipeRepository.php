<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository {
	public function __construct(RegistryInterface $registry) {
		parent::__construct($registry, Recipe::class);
	}

	public function search(string $searchString) {

		$matchFulltext = "MATCH_AGAINST(r.label, r.description, r.originUrl, :search 'WTH QUERY EXPANSION') + MATCH_AGAINST(t.label, :search)+MATCH_AGAINST(i.label, :search)";
		$qb = $this->createQueryBuilder('r');
		$query = $qb
			->select("MAX($matchFulltext) AS recipeScore, r as recipe")
			->join('r.ingredients', 'i')
			->join('r.tags', 't')
			->where("$matchFulltext> 0")
			->groupBy('r.id')
			->orderBy('recipeScore', 'DESC')
			->setParameter('search', $searchString)
			->getQuery();

		$result = $query->getResult();
		$alreadyFoundIds = [];

		$recipes = [];
		if (count($result)) {
			$maxScore = $result[0]['recipeScore'];
			foreach ($result as $resultObj) {
				$resultObj['recipe']->setSearchRating($resultObj['recipeScore'] / $maxScore);
				$alreadyFoundIds[] = $resultObj['recipe']->getId();
				$recipes[] = $resultObj['recipe'];
			}
		}

		$additionalQuery = $this->createQueryBuilder('r')
			->where('(r.description LIKE :search OR r.label LIKE :search)')
			->setParameter('search', "%$searchString%");
		if (count($alreadyFoundIds)) {
			$additionalQuery->andWhere('r.id NOT IN (:ids)')
				->setParameter('ids', $alreadyFoundIds);
		}

		$additional = $additionalQuery->getQuery()->getResult();

		/** @var Recipe $recipe */
		foreach ($additional as $recipe) {
			$recipe->setSearchRating(0.01);
			$recipes[] = $recipe;
		}

		return $recipes;
	}

}
