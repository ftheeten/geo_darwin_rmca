<?php
// src/AppBundle/Repository/TDataLogRepository.php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TDataLogRepository extends EntityRepository
{
	public function findContributions($table_name, $pk)
    {
        /*$query=$this->getEntityManager()
		  ->createQuery("
			SELECT t.pk, t.referencedTable, t.recordId, t.action,  t.modificationDateTime, t.secondaryTable, u.firstName, u.lastName
				,jsonb_diff_val(oldValue::jsonb, newValue::jsonb) FROM AppBundle:TDataLog t
				LEFT JOIN AppBundle:Duser u WITH t.userRef=u.id
				WHERE t.referencedTable=:table AND
				t.recordId=:pk ORDER BY t.modificationDateTime desc
		  ")->setParameter('table', $table_name)->setParameter('pk', $pk)
			;
			//returns an array of Product objects
        return $query->getResult();*/
		
		$conn = $this->getEntityManager()->getConnection();
		$stmt = $conn->prepare('SELECT t.pk, t.referenced_table, t.record_id, t.action,  t.modification_date_time, t.secondary_table, u.first_name, u.last_name,
		jsonb_diff_val(old_value::jsonb, new_value::jsonb) as difference
		FROM t_data_log t LEFT JOIN duser u ON t.user_ref=u.id WHERE t.referenced_table=:table AND
				t.record_id=:pk ORDER BY t.modification_date_time desc
				');
		$stmt->bindParam(':pk', $pk);
		$stmt->bindParam(':table', $table_name);
		$stmt->execute();
		$results = $stmt->fetchAll(\PDO::FETCH_ASSOC);	
		return $results;
    }
}
