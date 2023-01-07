<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormError;
use Symfony\Component\Config\Definition\Exception\Exception;

/** GeodarwinEntity*/
class GeodarwinEntity
{
	public function getLogInformation($em, $referenced_table)
	{
		$tmp_logs=$em->getRepository(TDataLog::class)
						 ->findContributions($referenced_table, $this->getPk());
		return $tmp_logs;
	}
	
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
	
	protected function attachForeignkeysAsObject($em, $class, $attribute, $mapping_params, $sort_criteria=NULL)
	{
		
		$tmp= $em->getRepository($class)->findBy($mapping_params);
		//$em->flush();		
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
		
		$this->$attribute=$tmp;
		
	}
	
	public function reattachForeignKeysAsObject(
		$em, 
		$class, 
		$attribute,		
		$signatureFunction,
		$new_array,		
		$mapping_params,
		$pk_fct="getPk"
	)
	{
		//print("reattach");
		$signatures_existing=Array();
		$signatures_existing_pk=Array();
		
		if($this->$attribute!==NULL)
		{
			foreach($this->$attribute as $obj)
			{
				
				$signature=call_user_func(array($obj, $signatureFunction));
				$signatures_existing[$signature]=$obj;
				$signatures_existing_pk[$signature]=call_user_func(array($obj, $pk_fct));
			}
		}
		$signatures_new=Array();
		$signatures_new_pk=Array();
		//passing object and not pk !		
		foreach($new_array as $obj)
		{	
			//print_r((array)$obj);
			$signature=call_user_func(array($obj, $signatureFunction));			
			$signatures_new[$signature]=$obj;
			$signatures_new_pk[$signature]=call_user_func(array($obj, $pk_fct));			
		}
		
		
		$to_remove=array_diff_key($signatures_existing,$signatures_new);
		
		$to_add=$signatures_new;//array_diff_key($signatures_new,$signatures_existing );
		$to_edit_pk=array_intersect($signatures_existing_pk,$signatures_new_pk );
		
		foreach($to_remove as $key=>$obj)
		{
			
			if($obj!==null)
			{
				//to allow edition
				if(!in_array(call_user_func(array($obj, $pk_fct)),$signatures_new_pk))
				{
				 $em->remove($obj);
				 $em->flush();
				}			 
			}
			
		}
		
		
		foreach($to_add as $key=>$obj)
		{	
			
			$tmp_pk=call_user_func(array($obj, $pk_fct));
			
			if(strlen($tmp_pk)>0)
			{
				if(!in_array($tmp_pk,$signatures_new_pk))
				{
	
					$em->persist($obj);
				}
				else
				{
					$em->merge($obj);
				}
				//else just edit
				$em->flush();
			}
			else
			{
				//assume no pk=new			

				$em->persist($obj);
			}
		}
		//for interface resync flush
		$em->flush();
		$this->attachForeignkeysAsObject($em,$class,$attribute, $mapping_params);
		return $this->$attribute;
	}
	
	public function reattachForeignKeysSignature(
		$em, 
		$class, 
		$attribute, 
		$signatureKeyField, 
		$signatureFunction, 
		$new_array,		
		$var_getter, 
		$mapping_params
		)
	{
		
		$signatures_existing=Array();
		//passing pk and not object !
		
		//to decide whether update or insert
		$pk_existing=Array();
				
		if($this->$attribute!==NULL)
		{
			foreach($this->$attribute as $pk_link)
			{
				
				
				$obj=$em->getRepository($class)->findOneBy(array($signatureKeyField => $pk_link));
				$signature=call_user_func(array($obj, $signatureFunction));
				$signatures_existing[$signature]=$obj;
				if(property_exists($obj, "pk"))
				{
					$pk_existing[]=$obj->getPk();
				}
			}
		}
		
		
		$signatures_new=Array();
		
		//passing object and not pk !		
		foreach($new_array as $obj)
		{
			
			$signature=call_user_func(array($obj, $signatureFunction));
			
			$signatures_new[$signature]=$obj;
			
		}
		
		
		$to_remove=array_diff_key($signatures_existing,$signatures_new);
		
		foreach($to_remove as $key=>$obj)
		{
			
			if($obj!==null)
			{	
				 //print("TRY_REMOVE");
				 $em->remove($obj);
				 //without flush asynchronous Doctrine ?
				 //seems to work with delete sync and update/insert async ??
				 $em->flush();
				 
			}
			
		}
		
		
		$to_add=array_diff_key($signatures_new,$signatures_existing );
		
		foreach($to_add as $key=>$obj)
		{	
			$flag_update=false;
			
			if(property_exists($obj, "pk"))
			{
				$tmp_pk=$obj->getPk();
				if(in_array($tmp_pk, $pk_existing))
				{
					$flag_update=true;
				}
			}
			
			if(!$flag_update)
			{
				
				$em->persist($obj);
				$em->flush();
			}
			else
			{
				$em->merge($obj);
				$em->flush();
			}
			//flush produces an asynchronous request !
			//$em->flush();
		}
		if($var_getter!==null)
		{
			$this->attachForeignkeys($em, $class, $attribute, $mapping_params, $var_getter);
		}
	}
	

	protected function handle_date_general($year, $month=null, $day=null)
	{
		
		$returned=$year;
		if($month==null)
		{
			$returned.="-01";
		}
		else
		{
			$returned.="-".str_pad($month,2,0,STR_PAD_LEFT);
		}
		if($day==null)
		{
			$returned.="-01";
		}
		else
		{
			$returned.="-".str_pad($day,2,0,STR_PAD_LEFT);
		}
		
		return \DateTime::createFromFormat("Y-m-d",$returned);	
	}
	
	protected function handle_date_generalForm($form, $year, $month=null, $day=null)
	{
		
		$returned=$year;
		if($month==null)
		{
			$returned.="-01";
		}
		else
		{
			$returned.="-".str_pad($month,2,0,STR_PAD_LEFT);
		}
		if($day==null)
		{
			$returned.="-01";
		}
		else
		{
			$returned.="-".str_pad($day,2,0,STR_PAD_LEFT);
		}
		$date_ctrl = \DateTime::createFromFormat("Y-m-d", $returned);
		$date_errors = \DateTime::getLastErrors();
		if($date_errors["warning_count"]>0 ||$date_errors["error_count"]>0 )
		{
			print("ERROR");
			$form->addError(new FormError("Invalid date".$returned.". Check the date format"));			
		}
		return \DateTime::createFromFormat("Y-m-d",$returned);	
	}
	
	protected function handle_date_format_general($year, $month=null, $day=null)
	{
		$returned=0;
		if($year!==null)
		{
			$returned+=32;
		}
		if($month!==null)
		{
			$returned+=16;
		}
		if($day!==null)
		{
			$returned+=8;
		}
		return $returned;
	}
	
	
	
}