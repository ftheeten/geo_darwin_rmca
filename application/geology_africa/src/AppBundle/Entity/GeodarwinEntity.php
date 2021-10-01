<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** GeodarwinEntity*/
class GeodarwinEntity
{
	
	protected function attachForeignkeys($em, $class, $attribute, $mapping_params, $var_getter, $sort_criteria=NULL)
	{
	
		$tmp= $em->getRepository($class)->findBy($mapping_params);		
		if($sort_criteria !==NULL)
		{
			$tmp2=Array();
			foreach($tmp as $obj)
			{				
				$tmp2[(int)call_user_func(array($obj,$sort_criteria))]=$obj;
			}
			ksort($tmp2);
			$tmp=$tmp2;
		}
		$this->$attribute=array_map(function($obj) use( $var_getter) {
							return call_user_func(array($obj, $var_getter));
						}, $tmp);
		
		
	}
	
	
	
	public function reattachForeignKeysSignature(
		$em, 
		$class, 
		$attribute, 
		$signatureKeyField, 
		$signatureFunction, 
		$new_array,		
		$var_getter, 
		$mapping_params)
	{
		$signatures_existing=Array();
		//passing pk and not object !
		
	
		if($this->$attribute==NULL)
		{
			print("NULL");
		}
				
		if($this->$attribute!==NULL)
		{
			foreach($this->$attribute as $pk_link)
			{
				$obj=$em->getRepository($class)->findOneBy(array($signatureKeyField => $pk_link));
				$signature=call_user_func(array($obj, $signatureFunction));
				$signatures_existing[$signature]=$obj;
			}
		}
		
		
		$signatures_new=Array();
		
		//passing object and not pk !
		
		foreach($new_array as $obj)
		{
			//$obj=$em->getRepository($class)->findOneBy(array($signatureKeyField => $pk_link));
			$signature=call_user_func(array($obj, $signatureFunction));
			
			$signatures_new[$signature]=$obj;
			/*foreach($params_fk as $function=>$var)
			{
				call_user_func(array($obj, $signatureFunction));
			}*/
		}
		
		
		
		$to_remove=array_diff_key($signatures_existing,$signatures_new);
		foreach($to_remove as $key=>$obj)
		{
			
			if($obj!==null)
			{				
				 $em->remove($obj);
				 
			}
			
		}
		
		$to_add=array_diff_key($signatures_new,$signatures_existing );
		foreach($to_add as $key=>$obj)
		{		
			
			$em->persist($obj);			
		}
		if($var_getter!==null)
		{
			$this->attachForeignkeys($em, $class, $attribute, $mapping_params, $var_getter);
		}
	}
	
	protected function reattachForeignkeys(
		$em, 
		$class, 
		$attribute, 
		$mapping_params,
		$var_getter , 
		$field_fk_val, 
		$new_array, 
		$params_fk, 
		$var_setter)
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