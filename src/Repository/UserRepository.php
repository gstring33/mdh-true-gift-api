<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, User::class);
        $this->security = $security;
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    public function selectPartner (User $currentUser)
    {
        if ($currentUser->getOfferGiftTo() !== null) {
            return null;
        }

        $builder = $this->createQueryBuilder('u');
        $users = $builder
            ->where('u.uuid != :uuid')
            ->setParameter('uuid', $currentUser->getUuid())
            ->andWhere($builder->expr()->isNull('u.recieveGiftFrom'))
            ->getQuery()
            ->getResult();

        $totalUsers =  count($users);

        if ($totalUsers === 0) {
            return null;
        }

        if ($totalUsers === 2) {
            $userSelected = array_filter($users, function (User $u) use ($currentUser) {
                return $u->getOfferGiftTo() !== $currentUser;
            });

            if (count($userSelected) === 1) {
                return $userSelected[0] ?? $userSelected[1];
            }
        }
        $key = array_rand($users);
        return $users[$key];
    }

    public function findAllOtherUsers (User $currentUser)
    {
        $builder = $this->createQueryBuilder('u');
        $users = $builder
            ->where('u.uuid != :uuid')
            ->setParameter('uuid', $currentUser->getUuid())
            ->getQuery()
            ->getResult();
        $currentUser = $this->security->getUser();
        $partnerSelected = $currentUser->getOfferGiftTo();
        foreach ($users as $user) {
            $gender = $user->getGender() === 'F' ? 'girl' : 'man';
            $img = "user-{$gender}";
            if ($partnerSelected !== null) {
                if ($partnerSelected === $user) {
                    $img .= '-success';
                    $user->setIsPartner(true);
                }else{
                    $img .= '-disable';
                    $user->setIsPartner(false);
                }
            }
            $user->setImg($img);
        }
        return $users;
    }
}
