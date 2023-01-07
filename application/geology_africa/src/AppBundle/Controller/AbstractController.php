<?php

// src/AppBundle/Controller/SearchController.php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use AppBundle\Entity\Codecollection;
use AppBundle\Entity\Dsample;
use AppBundle\Entity\Dsamminerals;
use AppBundle\Entity\Dsammagsusc;
use AppBundle\Entity\Dsamgranulo;
use AppBundle\Entity\Lminerals;
use AppBundle\Entity\LPrecision;
use AppBundle\Entity\LMedium;
use AppBundle\Entity\DLoccenter;
use AppBundle\Entity\Ddocument;
use AppBundle\Entity\Ddoctitle;
use AppBundle\Entity\Ddocsatellite;
use AppBundle\Entity\Dsamheavymin;
use AppBundle\Entity\Dsamheavymin2;
use AppBundle\Entity\Dcontribution;
use AppBundle\Entity\Dcontributor;
use AppBundle\Entity\Dlinkcontribute;
use AppBundle\Entity\Dlinkcontsam;
use AppBundle\Entity\Dlinkcontloc;
use AppBundle\Entity\Dlinkcontdoc;
use AppBundle\Entity\Dlinkdocloc;
use AppBundle\Entity\Dlocdrilling; 
use AppBundle\Entity\DLoclitho;
use AppBundle\Entity\Dkeyword;
use AppBundle\Entity\TDataLog;

class AbstractController extends Controller
{
	protected $page_size=20;
    protected $limit_autocomplete=30;
	
    public function indexAction(Request $request)
	{		
		return $this->render('@App/home.html.twig');
		$this->set_sql_session();
    }
	
	protected function set_sql_session()
	{
		$em = $this->getDoctrine()->getManager();		
		$uk=$this->getUser()->getId();
		if(is_numeric($uk))
		{
			$RAW_QUERY = "SET  session.geo_darwin_user TO ".$uk.";";
			$statement = $em->getConnection()->prepare($RAW_QUERY);
			$statement->execute();
		}
	}
	
	    public function getusercoll_right($coll,$rights)
		{          
		$em = $this->getDoctrine()->getManager();
		$username = $this->getUser()->getUsername();
		$found=0;
		
		$RAW_QUERY = "SELECT 
							uc.collection_id as ID_Collection, 
							uc.user_id as ID_User,  
							zoneutilisation,  
							username,  
							uc.role as role
				FROM codecollection c
				left join fos_user_collections uc on c.pk = uc.collection_id
				left join duser u on u.id = uc.user_id
				WHERE uc.collection_id = ANY ('{".$coll."}'::int[])
				AND username = '".$username."';";
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->execute();
		$arrayusercoll = $statement->fetchAll();
		foreach($arrayusercoll as $arrayusercoll_obj){
			foreach($rights as $rights_obj){
				if ($arrayusercoll_obj['role'] == $rights_obj){
					//print_r($rights_obj);
					$found=1;
					break;
				}
			}
		}
		if ($found==1){
			return true;
		}else{return false;}
	}

	public function edit_general($obj, $form, $name_twig_class, Request $request, $twig, $controller_name='AppBundle\Controller\GeoController', $array_options=Array())
	{
		$collection_rights=$this->check_collection_rights_general($controller_name);
		if ($collection_rights == true)
		{		
			$options=array($name_twig_class => $obj,'form' => $form->createView(),'origin'=>'edit','originaction'=>'edit',
							'read_mode'=>$this->enable_read_write($request));
			if(count($array_options)>0)
			{
				$options=array_merge($options, $array_options);
			}
			$em = $this->getDoctrine()->getManager();	
			$this->set_sql_session();
			if (!$obj) 
			{
				throw $this->createNotFoundException('No document found' );
			}
			elseif($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
			{
				
				if ($request->isMethod('POST')) 
				{				
					
					$form->handleRequest($request);
					if ($form->isSubmitted() && $form->isValid()) 
					{					
						
						try 
						{						
							$em = $this->getDoctrine()->getManager();
							$em->flush();
							$this->addFlash('success','DATA RECORDED IN DATABASE!');  
							return $this->render($twig,$options );
							
						}catch(\Doctrine\DBAL\DBALException $e ) {
							
							$form->addError(new FormError($e->getMessage()));
							
							return $this->render($twig, $options);
						}
						catch(\Doctrine\DBAL\DBALException\UniqueConstraintViolationException $e ) {
							$form->addError(new FormError($e->getMessage()));	
							return $this->render($twig, $options);						
						}
						catch(\Doctrine\DBAL\DBALException\ConstraintViolationException $e ) {
							$form->addError(new FormError($e->getMessage()));
							return $this->render($twig, array($name_twig_class => $obj,'form' => $form->createView(),'origin'=>'edit','originaction'=>'edit',
							'read_mode'=>$this->enable_read_write($request)));						
						}
						catch(Exception $e ) {
							$form->addError(new FormError($e->getMessage()));
							return $this->render($twig, $options);						
						}
						finally
						{
							
						}
						
					}
					elseif ($form->isSubmitted() && !$form->isValid() )
					{
						$form->addError(new FormError("Other validation error"));
						return $this->render($twig, $options);
					}
					elseif (!$form->isSubmitted())
					{					
						return $this->render($twig, $options);
					}
				}
				else
				{
					//print("issue");
				}
				
				
			}
			//print("test");
			
			return $this->render($twig,$options);
		}
		else
		{
			return $this->render('@App/collnoaccess.html.twig');
		}
	}
	
	protected function get_request_var(Request $request, $name_var, $default="")
	{
		$returned=$request->request->get($name_var,"");

		if($returned=="")
		{
			$returned=$request->query->get($name_var,"");
		}
		if($returned=="")
		{
			$returned=$request->get($name_var,$default);
		}
		return $returned;
	}
	
	protected function check_collection_rights_general($controller_name='AppBundle\Controller\GeoController')
	{
		return $this->container->get($controller_name)->getusercoll_right('1,2,3,7,9',['Curator','Validator','Encoder','Collection_manager']);
	}
	
	protected function enable_read_write(Request $request, $default="read", $variable="mode")
	{
		return $this->get_request_var($request, $variable, $default);
	}
	
	protected function validate_form(Request $request, $em, $name_table, $pk_obj)
	{
		$validate=$this->get_request_var($request, "validate_log", "");
		if($validate=="validate"||$validate=="invalidate")
		{
			//$em = $this->getDoctrine()->getManager();
			//$username = $this->getUser()->getUsername();
			$log=new TDataLog();
			$log->setAction($validate);
			$log->setRecordId($pk_obj);
			$log->setReferencedTable($name_table);
			$log->setModificationDateTime(date("Y-m-d H:i:s"));
			$log->setUserRef($this->getUser());
			$em->persist($log);
			$em->flush();
		}
	}
	
	public function get_next_idAction(Request $request)
	{
		
		$val="";
		$collection=$this->get_request_var($request, "collection","");
		$table=$this->get_request_var($request, "table","document");
		if($collection!="")
		{
			if($table=="document")
			{
				$sql_table="ddocument";
				$field="iddoc";
			}
			elseif($table=="sample")
			{
				$sql_table="dsample";
				$field="idsample";
			}
			elseif($table=="locality")
			{
				$sql_table="dloccenter";
				$field="idpt";
			}
			elseif($table=="dloccenter")
			{
				$sql_table="dloccenter";
				$field="idpt";
			}
			elseif($table=="dcontribution")
			{
				$sql_table="dcontribution";
				$field="idcontribution";
			}
			elseif($table=="dsample")
			{
				$sql_table="dsample";
				$field="idsample";
			}
			
			
			
			else
			{
				return new JsonResponse(Array("id"=>""));
			}
			$em = $this->container->get('doctrine')->getEntityManager();
			$RAW_QUERY="SELECT MAX(COALESCE(".$field.",0))+ 1 as next_id FROM ".$sql_table." WHERE LOWER(idcollection)=LOWER(:idcollection);";
			$statement = $em->getConnection()->prepare($RAW_QUERY);
			$statement->bindParam(":idcollection", $collection, \PDO::PARAM_STR);
			$statement->execute();
			$result = $statement->fetchAll(\PDO::FETCH_ASSOC);
			$val=$result[0]["next_id"];
		}
		else
		{
			if($table=="contributor")
			{
				$sql_table="dcontributor";
				$field="idcontributor";
			}
			elseif($table=="dcontribution")
			{
				$sql_table="dcontribution";
				$field="idcontribution";
			}
			elseif($table=="dloccenter")
			{
				$sql_table="dloccenter";
				$field="idpt";
			}
			elseif($table=="docplanvol")
			{
				$sql_table="docplanvol";
				$field="fid::integer";
			}
			elseif($table=="lminerals")
			{
				$sql_table="lminerals";
				$field="idmineral";
			}
			elseif($table=="dsample")
			{
				$sql_table="dsample";
				$field="idsample";
			}
			$em = $this->container->get('doctrine')->getEntityManager();
			$RAW_QUERY="SELECT COALESCE(MAX(COALESCE(".$field.",0))+ 1,1) as next_id FROM ".$sql_table." ;";
		
			$statement = $em->getConnection()->prepare($RAW_QUERY);
			$statement->execute();
			$result = $statement->fetchAll(\PDO::FETCH_ASSOC);
			$val=$result[0]["next_id"];
		}
		return new JsonResponse(Array("id"=>$val));
	}
	
	protected function gen_pdo_name($idx)
	{
		return ":pgeol".(string)$idx;
	}
	
		public function adminAction(Request $request)
		{
		return $this->render('@App/admin.html.twig');
    }
    
    //
    

    public function all_object_categoriesAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		
	   $RAW_QUERY = "select distinct main_type from mv_rmca_main_objects_description ORDER BY main_type;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $codes = $statement->fetchAll();
        
        return new JsonResponse($codes);
    }
	
	 public function all_documents_categoriesAction(Request $request)
	 {
		$em = $this->getDoctrine()->getManager();
		
	   $RAW_QUERY = "select distinct main_type from mv_rmca_main_objects_description WHERE main_type LIKE '%document%' ORDER BY main_type ;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $codes = $statement->fetchAll();        
        return new JsonResponse($codes);
    }
	
	 public function all_dloccenter_categoriesAction(Request $request)
	 {
		       
        return new JsonResponse([["main_type"=>'georef']]);
    }
	
	public function all_collectionsAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();		
	   $RAW_QUERY = "select distinct idcollection from mv_rmca_merge_all_objects_vertical_expand order by idcollection;"; 
		$statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $codes = $statement->fetchAll();        
        return new JsonResponse($codes);
    }
	
	protected function get_sample_collections()
	{
		
		$RAW_QUERY = "SELECT  DISTINCT dsample.idcollection, collection FROM dsample INNER JOIN codecollection ON idcollection=codecollection ORDER BY idcollection;";
		$em = $this->getDoctrine()->getManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->execute();
		$data = $statement->fetchAll();
		return $data;
	}
	
	public function all_collections_samplesAction(Request $request)
	{
		$data=$this->get_sample_collections();
		 return new JsonResponse($data);
	}
	
	public function pdo_param_string($request, $field, $sql_field, &$params_sql_or, &$params_pdo, &$params_sql_or_group, &$idx_param, $type, $comp="LIKE", $suffix="||'%'")
	{
		$val=$request->request->get($field,"");		
		if($val!="")
		{			
				if(strpos($comp,"=")||$type==\PDO::PARAM_INT)
				{
					$params_sql_or[]=$sql_field ."=".$this->gen_pdo_name($idx_param);
				}
				else
				{
					$params_sql_or[]="LOWER(".$sql_field.") ".$comp." LOWER(".$this->gen_pdo_name($idx_param).")".$suffix;
				}
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$val;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=$type;
				$idx_param++;
				$params_sql_or_group[$sql_field]=$params_sql_or;	
		}				
	}
	
	public function pdo_param_general($request, $field, $sql_field, &$params_sql_or, &$params_pdo, &$params_sql_or_group, &$idx_param, $type, $comp="LIKE", $suffix="||'%'")
	{
		$list=$request->request->get($field,Array());
		if(count($list)>0)
		{
			foreach($list as $val)
			{
				if(strpos($comp,"=")||$type==\PDO::PARAM_INT)
				{
					$params_sql_or[]=$sql_field ."=".$this->gen_pdo_name($idx_param);
				}
				else
				{
					$params_sql_or[]="LOWER(".$sql_field.") ".$comp." LOWER(".$this->gen_pdo_name($idx_param).")".$suffix;
				}
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$val;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=$type;
				$idx_param++;
			}			
			$params_sql_or_group[$sql_field]=$params_sql_or;
		}
	}
	
	
	public function set_date_ctrl_general($form, $date, $date_format, $year_field, $month_field, $day_field )
	{
		if($date !=null)
		{
			$form->get($year_field)->setData($date->format("Y"));
			if(($date_format & 16)==16)
			{
				$form->get($month_field)->setData($date->format("m"));
			}
			if(($date_format & 8)==8)
			{
				$form->get($day_field)->setData($date->format("d"));
			}			
		}
	}
	

public function handle_many_to_many_detail_general(Request $request, $index, $target_class_name, $field_target_id, $id_target_obj, $link_class_name, $fk_mapping=Array(), $http_mapping=Array(), $direct_mapping = Array())
	{		
		$target_obj=$this->getDoctrine()
			->getRepository($target_class_name)
			 ->findOneBy(array($field_target_id => $id_target_obj));
		
		if($target_obj!==null)
		{
			$reflect  = new \ReflectionClass($link_class_name);
			$dlink_obj=$reflect->newInstance(); 	 
			foreach($fk_mapping as $get_method=>$set_method)
			{			
				$src_val=call_user_func(array($target_obj,$get_method));
				call_user_func_array(array($dlink_obj, $set_method), array($src_val));
			}
			foreach($http_mapping as $prefix=>$method)
			{
				
				$val=$request->request->get($prefix.$index);			
				call_user_func_array(array($dlink_obj, $method), array($val));
			}
			foreach($direct_mapping as $method=>$val)
			{
				call_user_func_array(array($dlink_obj, $method), array($val));
			}
			return $dlink_obj;
		}
		else
		{
			//print("IS_NULL");
			return null;
		}		
	}
	

	
	public function handle_many_to_many_relation_general(Request $request, $obj, $prefix_pk_url, $target_class_name, $field_target_id, $link_class_name, $fk_mapping=Array(), $http_mapping=Array(), $direct_mapping = Array())
	{			
		if ($request->isMethod('POST'))
		{		
			
			$params=$request->request->all();
		}
		else
		{
		
			$params=$request->query->all();
		}
		$tmp_array=Array();
		//print( $prefix_pk_url);
		//print("<br/>");
		foreach($params as $key=>$val)
		{			
			//print("_RECORD_<br/>");
			if(strpos($key, $prefix_pk_url )===0)
			{		
				//print($key);
				$tmp_idx=preg_replace('/^(.+?)(\d+)$/i', '${2}', $key);
				//print($tmp_idx);
				$id_obj=$request->request->get($prefix_pk_url.$tmp_idx);						
				$tmp_obj=$this->handle_many_to_many_detail_general($request, $tmp_idx, $target_class_name, $field_target_id,$id_obj , $link_class_name, $fk_mapping, $http_mapping, $direct_mapping);
				$tmp_array[$tmp_idx]=$tmp_obj;
				//print_r((array)$tmp_array[$tmp_idx]);
				//$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
			}
		}
		//print_r($tmp_array);
		return $tmp_array;
	}	
	
	
	public function getHTTPOneToManyByIndex($request, $pk_field, $target_class_name, $http_mapping=Array(), $direct_mapping = Array() , $checkbox_mapping=Array())
	{
		//print("call");
		$array_obj=Array();
		
		$found=false;
		$max_index;
		
		$test=$this->get_request_var($request, $pk_field);
		
		if($test!==null)
		{
			if(is_array($test))
			{
				$found=true;
			}
		}
		if($found)
		{
			//print("found");
			//!1 based array
			$i=1;
			foreach($test as $item)
			{
				$reflect  = new \ReflectionClass($target_class_name);
				$dlink_obj=$reflect->newInstance();
				$array_obj[$i]=$dlink_obj;				
				foreach($http_mapping as $prefix=>$method)
				{
					
					$array_tmp=$request->request->get($prefix);
					if($array_tmp!==null)
					{
						if(is_array($array_tmp))
						{
							if(count($array_tmp)>=$i)
							{
								//print_r($array_tmp);
								$val_tmp=$array_tmp[$i];
								if(strlen(trim($val_tmp))>0)
								{
									//print("ADD");
									call_user_func_array(array($dlink_obj, $method), array($val_tmp));
								}
							}
						}
					}
				}
				foreach($direct_mapping as $method=>$val)
				{
					call_user_func_array(array($dlink_obj, $method), array($val));
				}
				foreach($checkbox_mapping as $field=>$method)
				{
					
					$array_tmp=$request->request->get($field);
					
					if(isset($array_tmp))
					{
						if(array_key_exists($i,$array_tmp))
						{
							
							call_user_func(array($dlink_obj, $method), true);
						}
						else
						{
							
							call_user_func(array($dlink_obj, $method), false);
						}
					}
				}
				
				$i++;
				//print($i);
			}
		}		
		return $array_obj;		
	}
	
	public function delete_general($obj, $form,  $name_twig_class, Request $request, $twig)
	{	
		$collection_rights=$this->check_collection_rights_general();
		if ($collection_rights == true)
		{		
			$em = $this->getDoctrine()->getManager();	
			$this->set_sql_session();
			if (!$obj) {
				throw $this->createNotFoundException('No document found' );
			}
			elseif($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
			{
				
				if ($request->isMethod('POST')) 
				{				
					
					$form->handleRequest($request);
					if ($form->isSubmitted() && $form->isValid()) 
					{					
						try 
						{						
							$em = $this->getDoctrine()->getManager();
							$em->remove($obj);
							$em->flush();
							$this->addFlash('success','DATA DELETED!');  
							
							return $this->redirectToRoute('app_home');
							
						}catch(\Doctrine\DBAL\DBALException $e ) {
							
							$form->addError(new FormError($e->getMessage()));
							return $this->render($twig, array($name_twig_class => $obj,'form' => $form->createView(),'origin'=>'edit','originaction'=>'edit'));
						}
						catch(\Doctrine\DBAL\DBALException\UniqueConstraintViolationException $e ) {
							$form->addError(new FormError($e->getMessage()));	
							return $this->render($twig, array($name_twig_class => $obj,'form' => $form->createView(),'origin'=>'edit','originaction'=>'edit'));						
						}
						catch(\Doctrine\DBAL\DBALException\ConstraintViolationException $e ) {
							$form->addError(new FormError($e->getMessage()));
							return $this->render($twig, array($name_twig_class => $obj,'form' => $form->createView(),'origin'=>'edit','originaction'=>'edit'));						
						}
						catch(Exception $e ) {
							$form->addError(new FormError($e->getMessage()));
							return $this->render($twig, array($name_twig_class => $obj,'form' => $form->createView(),'origin'=>'edit','originaction'=>'edit'));						
						}
						finally
						{
							
						}
						
					}
					elseif ($form->isSubmitted() && !$form->isValid() )
					{
						$form->addError(new FormError("Other validation error"));
						return $this->render($twig, array($name_twig_class => $object,'id'=> $id_param,'form' => $form->createView(),'origin'=>'edit','originaction'=>'edit'));
					}
					elseif (!$form->isSubmitted())
					{					
						return $this->render($twig, array($name_twig_class => $object,'id'=> $id_param,'form' => $form->createView(),'origin'=>'edit','originaction'=>'edit'));
					}
				}
				else
				{
					print("issue");
				}
				
				
			}
			return $this->redirectToRoute('app_home');
		}
		else
		{
			return $this->render('@App/collnoaccess.html.twig');
		}
    }
	
	protected function get_hierarchy_mineral()
	{
		
		$RAW_QUERY = "SELECT * FROM lminerals_hierarchy ORDER BY pk";
		$em = $this->getDoctrine()->getManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->execute();
		$data = $statement->fetchAll();
		return $data;
	}
}