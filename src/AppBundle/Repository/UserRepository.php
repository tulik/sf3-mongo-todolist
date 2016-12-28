<?php

namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class UserRepository extends DocumentRepository
{
    public function getUserByName($username)
    {
        $qb = $this->getDocumentManager()->createQueryBuilder('AppBundle:User');
        $qb->field('username')->equals($username);

        return $qb->getQuery()->getSingleResult();
    }

    public function count()
    {
        $qb = $this->getDocumentManager()->createQueryBuilder('AppBundle:User');

        return $qb->getQuery()->count();
    }

    public function getRandomUser()
    {
        $qb = $this->getDocumentManager()->createQueryBuilder('AppBundle:User');
        $count =  $qb->getQuery()->count();
        $skip_count = random_int(0, $count);
        $qb->skip($skip_count);

        return $qb->getQuery()->getSingleResult();
    }

}
