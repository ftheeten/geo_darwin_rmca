<?php

// src/AppBundle/MineralController.php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Lminerals;
use AppBundle\Entity\LmineralsHierarchy;
use AppBundle\Form\LmineralsType;
use AppBundle\Form\LmineralsHierarchyType;
use Symfony\Component\Form\FormError;

class MineralController extends AbstractController
{
	protected $limit_autocomplete=30;
	
	
	
	public function searchmineralAction(Request $request){
	
		$RAW_QUERY = "SELECT * FROM lminerals_hierarchy ORDER BY pk";
		$em = $this->getDoctrine()->getManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->execute();
		$data = $statement->fetchAll();
		return $this->render('@App/lminerals/mineral_search.html.twig', array("hierarchies"=>$data, "ranks"=> [""=> "unspecified", "mineral"=>"mineral", "class"=> "class", "groupe"=>"group"] ));  
	}
	

	
	public function main_searchAction(Request $request)
    {
		$current_page=$request->get("current_page",1);
        $page_size=$request->get("page_size",$this->page_size);
		$offset=(((int)$current_page)-1)* (int)$page_size;
		
		$namemineral=$request->get("min","");
		$rank=$request->get("rank","");
		$hierarchy=$request->get("hierarchy","");
		$has_child=$request->get("child_min","false");
	
		
		$idx_param=1;
		$params_sql=Array();
		$params_pdo=Array();
		 $RAW_QUERY="SELECT *, count(*) OVER() AS full_count FROM v_mineral_hierarchy";
		 if(strlen(trim($namemineral))>0)
		 {
			 if(strtolower(trim($has_child))=="true")
			 {
				 $params_sql[]="LOWER(TRIM(hierarch_name)) ~* ".$this->gen_pdo_name($idx_param)."";
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]="/".$namemineral."/";
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
			 }
			 else
			 {
				 $params_sql[]="LOWER(TRIM(mname))=LOWER(TRIM(".$this->gen_pdo_name($idx_param)."))";
				 $params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$namemineral;
				 $params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
			 }
			$idx_param++;
		 }
		 
		 if(strlen(trim($rank))>0)
		 {
			 
			 $params_sql[]="LOWER(TRIM(rank))=LOWER(TRIM(".$this->gen_pdo_name($idx_param)."))";
			 $params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$rank;
			 $params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
			 $idx_param++;
		 }
		 
		 if(strlen(trim($hierarchy))>0)
		 {
			 
			 $params_sql[]="fk_hierarchy=".$this->gen_pdo_name($idx_param);
			 $params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$hierarchy;
			 $params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_INT;
			 $idx_param++;
		 }
		 
		 
		 if(count($params_sql)>0)
		{
			$RAW_QUERY.=" WHERE ".implode(" AND ", $params_sql ). "  ";	
		}
		if(count($params_sql)>0)
		{
			$RAW_QUERY.=' ORDER BY hierarch_name ';
		}
		else
		{
			$RAW_QUERY.=' ORDER BY mname, pk ';
		}
		$RAW_QUERY.=' OFFSET :offset LIMIT :limit ;';
		$em = $this->container->get('doctrine')->getEntityManager();
		
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		foreach($params_pdo as $key=>$val)
		{
			
			$statement->bindParam($key, $val["value"],  $val["type"]); 
		}
		$statement->bindParam(":offset", $offset, \PDO::PARAM_INT);
		$statement->bindParam(":limit", $page_size, \PDO::PARAM_INT);
		$statement->execute(); 
		$results=$statement->fetchAll();
		if(count($results)>0)
		{
			$count_all=$results[0]["full_count"];
			$pagination = array(
				'page' => $current_page,
				'route' => 'search_main',
				'pages_count' => ceil($count_all / $page_size)
			);
			$is_modal=false;
			if($request->request->has("is_modal"))
			{
				if($request->get("is_modal"))
				{
					$is_modal=true;
				}
			}
			return $this->render('@App/lminerals/search_results_minerals.html.twig', array("results"=>$results, "pagination"=>$pagination, "page_size"=>$page_size, "nb_results"=>$count_all, "is_modal"=>$is_modal));
		}
		else
		{
			return $this->render('@App/no_results_bs.html.twig');
		}
		return $this->render('@App/no_results_bs.html.twig');
	}
	
	
	public function hierarchy_searchAction(Request $request)
    {
		$current_page=$request->get("current_page",1);
        $page_size=$request->get("page_size",$this->page_size);
		$offset=(((int)$current_page)-1)* (int)$page_size;
		
		$name=$request->get("name","");
		$author=$request->get("author","");
	
	
		
		$idx_param=1;
		$params_sql=Array();
		$params_pdo=Array();
		 $RAW_QUERY="SELECT *, count(*) OVER() AS full_count FROM lminerals_hierarchy";
	
		 
		 if(strlen(trim($name))>0)
		 {
			 
			 $params_sql[]="LOWER(TRIM(name))=LOWER(TRIM(".$this->gen_pdo_name($idx_param)."))";
			 $params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$name;
			 $params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
			 $idx_param++;
		 }
		 
		 if(strlen(trim($author))>0)
		 {
			 
			 $params_sql[]="LOWER(TRIM(author))=LOWER(TRIM(".$this->gen_pdo_name($idx_param)."))";
			 $params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$author;
			 $params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
			 $idx_param++;
		 }
		 
		 
		 if(count($params_sql)>0)
		{
			$RAW_QUERY.=" WHERE ".implode(" AND ", $params_sql ). "  ORDER BY name ";	
		}
		$RAW_QUERY.=' OFFSET :offset LIMIT :limit ;';
		$em = $this->container->get('doctrine')->getEntityManager();
		
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		foreach($params_pdo as $key=>$val)
		{
			
			$statement->bindParam($key, $val["value"],  $val["type"]); 
		}
		$statement->bindParam(":offset", $offset, \PDO::PARAM_INT);
		$statement->bindParam(":limit", $page_size, \PDO::PARAM_INT);
		$statement->execute(); 
		$results=$statement->fetchAll();
		if(count($results)>0)
		{
			$count_all=$results[0]["full_count"];
			$pagination = array(
				'page' => $current_page,
				'route' => 'search_main',
				'pages_count' => ceil($count_all / $page_size)
			);
			$is_modal=false;
			if($request->request->has("is_modal"))
			{
				if($request->get("is_modal"))
				{
					$is_modal=true;
				}
			}
			return $this->render('@App/lminerals/search_results_hierarchy.html.twig', array("results"=>$results, "pagination"=>$pagination, "page_size"=>$page_size, "nb_results"=>$count_all, "is_modal"=>$is_modal));
		}
		else
		{
			return $this->render('@App/no_results_bs.html.twig');
		}
		return $this->render('@App/no_results_bs.html.twig');
	}
	
	public function searchmineralhierarchiesAction(Request $request)
	{
		$RAW_QUERY = "SELECT DISTINCT author FROM lminerals_hierarchy ORDER BY author";
		$em = $this->getDoctrine()->getManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->execute();
		$data = $statement->fetchAll();
		return $this->render('@App/lminerals/mineralhierarchies_search.html.twig', ["authors"=>$data]);
	}
	
	public function allminerals_hierarchiesAction(Request $request)
	{
		$RAW_QUERY = "SELECT * FROM lminerals_hierarchy ORDER BY pk";
		$em = $this->getDoctrine()->getManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->execute();
		$data = $statement->fetchAll();
	     return new JsonResponse($data);		
	}
	
	
	public function raw_searchAction(Request $request)
    {
		return $this->render('@App/lminerals/mineral_search_raw.html.twig');
	}
	
	
	
	protected function recursfancytreeHierarchy($path_array, &$target_array, $path_parent, $level=1, $expanded=true, $checkbox=true, $expand_first_level=false, $name_field="mname", $parent_field="mparent", $rank_field="rank", $formula_field="mformula")
    {
        foreach($path_array[$path_parent] as $row)
        {
            $tmp_row=Array();
			$tmp_row["key"]=$row[$name_field];
			$tmp_row["checkbox"]=$checkbox;
			$tmp_row["title"]=$row[$name_field];
			if($row[ $parent_field]==null)
			{
				$row[ $parent_field]=$row[$name_field];
			}
			$tmp_row[ $parent_field]=$row[$parent_field];
            $tmp_row["expanded"]=$expanded;
			if($level==1 && $expand_first_level )
			{
				$tmp_row["expanded"]=true;
			}			

			$tmp_row["unselect"]=true;
			if(strlen($rank_field)>0)
			{
				$tmp_row[$rank_field]=$row[$rank_field];
			}
			if(strlen($formula_field)>0)
			{
				$tmp_row[$formula_field]=$row[$formula_field];
			}
            $child_path= $path_parent.$row[$name_field]."/";
            if(array_key_exists($child_path, $path_array))
            {
                $tmp_row["children"]=Array();
                $this->recursfancytreeHierarchy($path_array, $tmp_row["children"], $child_path, $level +1, $expanded, $checkbox, $expand_first_level);
            }
            $target_array[]=$tmp_row;
            
        }
        return $target_array;
    }
	
    public function get_fancytreeAction(Request $request)
	{
		$em = $this->container->get('doctrine')->getEntityManager();
		$returned=Array();
		$struct=Array();
		//$result_tmp=Array();
		$hierarch_tmp=Array();
        $keyw=$request->get("keyword","");
        $exp_str=$request->get("expanded","true");
		$fuzzy_str=$request->get("fuzzy","false");
		$children_str=$request->get("children_only","false");
        $checkbox=$request->get("checkbox","true");
        $expand_first_level=$request->get("expand_first_level","false");
        if(strtolower( $checkbox)=="false")
        {
            $checkbox=false;
        }
        else
        {
            $checkbox=true;
        }
        
		
        $expanded=true;
		$root="/";
        if(strtolower($exp_str)=="false")
        {
            $expanded=false;
        }
        if(strtolower( $expand_first_level)=="true")
        {
            $expand_first_level=true;
        }
        else
        {
            $expand_first_level=false;
        }
        if(strlen(trim($keyw))>0)
        {
			
			if(strtolower($fuzzy_str)=="true")
			{
				$comparator="hierarch_name ~* :keyw";
			}
			else
			{
				$comparator="LOWER(mname) = LOWER(:keyw) or hierarch_name ~* :keywreg ";				
			}
			if(strtolower($children_str)=="true")
			{
				 
				 $RAW_QUERY="select *,  regexp_replace(regexp_replace(hierarch_name, :keywregahead, :keywreg) ,'[^/]*/$','') path_parent  from v_mineral_hierarchy WHERE ".$comparator." ORDER BY level, hierarch_name;";
					$statement = $em->getConnection()->prepare($RAW_QUERY);
					$statement->bindParam(":keyw", $keyw, \PDO::PARAM_STR);
					//if(strtolower($fuzzy_str)!=="true")
					//{			
					$keywregahead='.*/'.$keyw.'/';
					$keywreg='/'.$keyw.'/';
					$statement->bindParam(":keywregahead",$keywregahead  , \PDO::PARAM_STR); 	
					$statement->bindParam(":keywreg", $keywreg , \PDO::PARAM_STR); 					
					//}
			}
			else
			{
				
             $RAW_QUERY="with a as 
						(
						select  *
							 from v_mineral_hierarchy WHERE ".$comparator."),
						b as (select min(level) min_lev from a),
						c as (
						select unnest(path_pk)::int as parent_pk from a where level=(select min_lev from b)
						),
						d 
						as

						select v_mineral_hierarchy.* from v_mineral_hierarchy 
						inner join c on 
						 pk=parent_pk
						 union select * from a )
						 
						 select *, regexp_replace(hierarch_name,'[^/]*/$','') path_parent from d order by hierarch_name ";
						$statement = $em->getConnection()->prepare($RAW_QUERY);
						$statement->bindParam(":keyw", $keyw, \PDO::PARAM_STR);
						if(strtolower($fuzzy_str)!=="true")
						{					
							$keywreg='/'.$keyw.'/';
							$statement->bindParam(":keywreg",$keywreg , \PDO::PARAM_STR); 					
						}						
			}
                      
        }
        else			
        {   
		
		 $RAW_QUERY="SELECT *,regexp_replace(hierarch_name,'[^/]*/$','') as path_parent FROM v_mineral_hierarchy ORDER by level, hierarch_name";
            $statement = $em->getConnection()->prepare($RAW_QUERY);               
        }
        
		$statement->execute(); 
		$struct = $statement->fetchAll();
        $struct_by_parent=Array(); 
		$term_to_pos=Array();
		if(count($struct)>0)
		{
            $struct_by_parent=Array();
            foreach($struct as $row)
            {
                if(!array_key_exists($row["path_parent"], $struct_by_parent))
                {
                    $struct_by_parent[$row["path_parent"]]=array();
                    
                }
                 $struct_by_parent[$row["path_parent"]][]=$row;
            }
            
            
            $hierarch_tmp=$this->recursfancytreeHierarchy($struct_by_parent, $hierarch_tmp, $root, 1, $expanded, $checkbox,$expand_first_level);	
			
		}
		 return new JsonResponse($hierarch_tmp);
	}
		


	public function get_MineralpathAction(Request $request)
	{
		$em = $this->container->get('doctrine')->getEntityManager();
		$keyw=$request->get("keyword","");
		$returned=Array();
		$RAW_QUERY="SELECT * FROM (SELECT regexp_split_to_table(hierarch_name,'/') 
			   AS word   FROM public.v_mineral_hierarchy WHERE mname=:keyw) a WHERE word !='';";
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->bindParam(":keyw", $keyw, \PDO::PARAM_STR);
		$statement->execute(); 
		$struct = $statement->fetchAll();
		if(count($struct)>0)
		{
			$returned=$struct;
		}
		 return new JsonResponse($returned);
	}
	
	
     public function allminerals_autocompleteAction(Request $request)
	{
		$names=Array();
		$em = $this->getDoctrine()->getManager();
		$word = strtolower($request->query->get("code",""));
		$hierarch= strtolower($request->query->get("hierarch",""));
		$getpk= strtolower($request->query->get("getpk",""));
        if(strlen($word)>0)
        {
			if(strlen(trim($getpk))>0)
			{
				$fields="string_agg(idmineral::varchar,',') id,  lower(mname) word";
				$group_by=" GROUP BY lower(mname) ";
			}
			else
			{
				$fields="lower(mname) word";
				$group_by="";
			}
			if(strlen(trim($hierarch))>0)
			{
				$RAW_QUERY = "
				SELECT DISTINCT ".$fields." FROM lminerals
				WHERE  mname  ~* :word AND fk_hierarchy=:hierarch "
				.$group_by." ORDER BY lower(mname)
				LIMIT :limit
				;"; 
				
				$statement = $em->getConnection()->prepare($RAW_QUERY);
				$statement->bindParam(":word", $word, \PDO::PARAM_STR);
				$statement->bindParam(":hierarch", $hierarch, \PDO::PARAM_INT);
			}
			else
			{
			
				$RAW_QUERY = "
				SELECT DISTINCT ".$fields." FROM lminerals
				WHERE  mname  ~* :word "
				.$group_by." ORDER BY lower(mname)
				LIMIT :limit
				;"; 
				
				$statement = $em->getConnection()->prepare($RAW_QUERY);
				$statement->bindParam(":word", $word, \PDO::PARAM_STR);
			
			}
				$statement->bindParam(":limit", $this->limit_autocomplete, \PDO::PARAM_INT);
            $statement->execute();
            $names = $statement->fetchAll();
        }       
        
        return new JsonResponse($names);
    }
	
	public function editmineralgsAction(Lminerals $lmineral, Request $request)
	{
	
		$this->set_sql_session();		
		$em = $this->getDoctrine()->getManager();

		$RAW_QUERY = "SELECT * FROM lminerals_hierarchy ORDER BY pk";
		$em = $this->getDoctrine()->getManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->execute();
		
		$data = $statement->fetchAll();	
		$form = $this->createForm(LmineralsType::class, $lmineral, array('entity_manager' => $em,));
		$twig_params=	array("hierarchies"=> $data,
					"originaction"=>"edit",
					"ranks"=> [""=> "unspecified", "mineral"=>"mineral", "class"=> "class", "groupe"=>"group"],
					'read_mode'=>$this->get_request_var($request, 'mode', "read")
			);
		return $this->edit_general($lmineral, $form,"lmineral", $request, '@App/lminerals/mineralform.html.twig', 'AppBundle\Controller\MineralController',$twig_params);
		
	}
	
	 public function addmineralgsAction(Request $request)
	{
		$this->set_sql_session();
		$allow=$this->check_collection_rights_general('AppBundle\Controller\MineralController');
		if($allow)
		{		
			$lmineral=new Lminerals();
			$em = $this->getDoctrine()->getManager();
			$form = $this->createForm(LmineralsType::class, $lmineral, array('entity_manager' => $em,));
			if ($request->isMethod('POST')) 
			{
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) 
				{
					try 
					{	
						$em->persist($lmineral);
						$em->flush();
						return $this->redirectToRoute('app_edit_mineralgs', array('pk' => $lmineral->getPk()));
					}
					catch(\Doctrine\DBAL\DBALException $e ) 
					{
						$form->addError(new FormError($e->getMessage()));
					}
					catch(Exception $e ) 
					{
						$form->addError(new FormError($e->getMessage()));
					}
				}
			}
			
			
			$RAW_QUERY_HIERARCHY = "SELECT * FROM lminerals_hierarchy ORDER BY pk";	
			$statement = $em->getConnection()->prepare($RAW_QUERY_HIERARCHY);
			$statement->execute();
			$hierarchy = $statement->fetchAll();
			return $this->render('@App/lminerals/mineralform.html.twig', array(
					'form' => $form->createView(),
					'lmineral' => $lmineral,
					'hierarchy' => $hierarchy,
					'originaction'=>'add_beforecommit',
					'read_mode'=>"create",
					"ranks"=> [""=> "unspecified", "mineral"=>"mineral", "class"=> "class", "group"=>"group"]
				)
			);
			
		}
		else
		{
			return $this->render('@App/collnoaccess.html.twig');
		}
	}
	
	public function addmineralhierarchygsAction(Request $request)
	{
		$this->set_sql_session();
		$allow=$this->check_collection_rights_general('AppBundle\Controller\MineralController');
		if($allow)
		{		
			$lmineralhierarchy=new LmineralsHierarchy();
			
			$form = $this->createForm(LmineralsHierarchyType::class, $lmineralhierarchy, );
			if ($request->isMethod('POST')) 
			{
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) 
				{
					try 
					{	
						$em = $this->getDoctrine()->getManager();
						$em->persist($lmineralhierarchy);
						$em->flush();
						return $this->redirectToRoute('app_edit_mineralhierarchygs', array('pk' => $lmineralhierarchy->getPk()));
					}
					catch(\Doctrine\DBAL\DBALException $e ) 
					{
						$form->addError(new FormError($e->getMessage()));
					}
					catch(Exception $e ) 
					{
						$form->addError(new FormError($e->getMessage()));
					}
				}
			}					
			
			return $this->render('@App/lminerals/mineralhierarchyform.html.twig', array(
					'form' => $form->createView(),
					'lmineralhierarchy' => $lmineralhierarchy,					
					'originaction'=>'add_beforecommit',
					'read_mode'=>"create",					
				)
			);
			
		}
		else
		{
			return $this->render('@App/collnoaccess.html.twig');
		}
	}
	
	public function editmineralhierarchygsAction(LmineralsHierarchy $lmineralhierarchy, Request $request)
	{
		$this->set_sql_session();		
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(LmineralsHierarchyType::class, $lmineralhierarchy, array());
		$twig_params=	array(
					"originaction"=>"edit",					
					'read_mode'=>$this->get_request_var($request, 'mode', "read")
			);
		return $this->edit_general($lmineralhierarchy, $form,"lmineralhierarchy", $request, '@App/lminerals/mineralhierarchyform.html.twig', 'AppBundle\Controller\MineralController',$twig_params);
	}
	
}