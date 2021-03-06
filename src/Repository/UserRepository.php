<?php

namespace App\Repository;

use App\Entity\User;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getResult();
    }

    public function loadUserByUsernameOrEmail($username)
    {
        $users = $this->createQueryBuilder('u')
            ->where('u.username = :username')
            ->orWhere('u.email = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getResult();

        return count($users) > 0? $users[0]: null;
    }

    /**
     * @param $token
     * @return null|User
     */
    public function findByToken($token)
    {
        $now = Carbon::now();

        $users = $this->createQueryBuilder('u')
            ->where('u.confirmToken = :token')
            ->andWhere('u.confirmTokenLimit > :now')
            ->setParameter('token', $token)
            ->setParameter('now', $now)
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();

        return empty($users)? null: $users[0];
    }
}
