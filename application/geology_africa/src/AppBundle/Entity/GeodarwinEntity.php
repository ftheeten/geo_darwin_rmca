<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** GeodarwinEntity*/
class GeodarwinEntity
{
	
	protected function attachForeignkeys($em, $class, $attribute, $mapping_params, $var_getter)
	{
		
		$tmp= $em->getRepository($class)->findBy($mapping_params);
		$this->$attribute=array_map(function($obj) use( $var_getter) {
							return call_user_func(array($obj, $var_getter));
						}, $tmp);
		
	}
	
	protected function reattachForeignkeys($em, $class, $attribute, $mapping_params,$var_getter , $field_fk_val, $new_array, $params_fk, $var_setter)
	{
		$tmp_array=$this->$attribute;
		$to_add=array_diff($new_array,$tmp_array );
		
		foreach($to_add as $add)
		{
			$reflect  = new \ReflectionClass($class);
			$obj=$reflect->newInstance();  
			foreach($params_fk as $p_method=>$p_val)
			{
				call_user_func_array(array($obj,$p_method), array($p_val));
			}
			call_user_func_array(array($obj, $var_setter), array($add));
			
			$em->persist($obj);
			
		}
		
		$to_remove=array_diff($tmp_array,$new_array );
		foreach($to_remove as $rem)
		{
			$new_mapping_params=$mapping_params;
			$new_mapping_params[$field_fk_val]=$rem;
			$obj=$em->getRepository($class)->findOneBy($new_mapping_params);
			if($obj!==null)
			{
				 $em->remove($obj);
			}
		}
		
		$this->attachForeignkeys($em, $class, $attribute, $mapping_params, $var_getter);
	}
}