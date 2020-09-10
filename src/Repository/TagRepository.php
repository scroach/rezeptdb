<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository {

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Tag::class);
	}

	/**
	 * @param User $user
	 * @return Tag[]
	 */
	public function findByUser(User $user) {
		return $this->findBy(['user' => $user], ['label' => 'ASC']);
	}

	public function findOneByLabel(User $user, string $label): ?Tag {
		return $this->findOneBy(['user' => $user, 'label' => $label]);
	}

}
