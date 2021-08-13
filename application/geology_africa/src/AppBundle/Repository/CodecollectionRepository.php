<?php
// src/AppBundle/Repository/CodecollectionRepository.php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CodecollectionRepository extends EntityRepository
{
   public function findAllCollOrderedByName()
    {
        return $this->getEntityManager()
            ->createQuery(
				"SELECT codecollection FROM AppBundle:codecollection p where p.zoneutilisation LIKE 'sample%' ORDER BY p.codecollection ASC;"
            )
            ->getResult();
    }

}
