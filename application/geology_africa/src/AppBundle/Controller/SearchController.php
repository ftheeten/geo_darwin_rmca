<?php

// src/AppBundle/Controller/SearchController.php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Form\SearchAllForm;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{

	public function refresh_viewsAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		
	   $RAW_QUERY = "select rmca_refresh_materialized_view_auto as init_portal FROM rmca_refresh_materialized_view_auto();"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $codes = $statement->fetchAll();
        
        return new JsonResponse($codes);
    }
	
	public function searchallAction(Request $request){
		return $this->render('@App/searchall.html.twig');  
	}
	

	
	
	 protected function init_map_json($results)
    {
			$returned=Array();
			$returned["type"]="FeatureCollection";
			$returned["crs"]["type"]="name";
			$returned["crs"]["properties"]["name"]=["EPSG:4326"];
			
			$points=Array();
			foreach($results as $row)
			{
				
				if(is_numeric($row["coord_long"])&&is_numeric($row["coord_lat"]))
				{
					$has_coordinates=true;
					$obj=Array();
					$obj["type"]="Feature";
					$obj["geometry"]["type"]="Point";
					$obj["geometry"]["coordinates"]=[(float)$row["coord_long"], (float)$row["coord_lat"]];
					$obj["properties"]["dw_type"]=$row["main_type"];
					$obj["properties"]["dw_idobject"]=$row["idobject"];
					$obj["properties"]["dw_idcollection"]=$row["idcollection"];
					$obj["properties"]["dw_pk"]=$row["pk"];
					
					$points[]=$obj;
				}		
			}
			$returned["features"]=$points;
			return $returned;
		}
    
     public function searchallbsAction(Request $request)
	 {    
        $form=$this->createForm(SearchAllForm::class, null);
		return $this->render('@App/search_all/searchallbs.html.twig',array('form' => $form->createView()));  
	}
	
	 /*public function searchallbs_documents_rawAction(Request $request)
	 {    
        $form=$this->createForm(SearchAllForm::class, null);
		return $this->render('@App/searchallbs_raw.html.twig',array('form' => $form->createView(), 'type_filter'=>'document'));  
	}*/
	
	public function searchallbs_rawAction(Request $request)
	{
    
        $form=$this->createForm(SearchAllForm::class, null);
		return $this->render('@App/searchallbs_raw.html.twig',array('form' => $form->createView()));  
	}
    
	public function contribution_searchAction(Request $request)
	{
		$current_page=$request->get("current_page",1);
        $page_size=$request->get("page_size",$this->page_size);
		$offset=(((int)$current_page)-1)* (int)$page_size;
		
		$namecontrib=$request->get("namecontrib");
		$institute=$request->get("institute");
		$results=Array();
		$pagination=Array();
		
		$params_sql=Array();
		$params_sql_or=Array();
		$params_sql_or_group=Array();
		$params_pdo=Array();
		$idx_param=1;
		
		if($request->request->has("nameevent"))
		{
			$contribs=$request->get("nameevent");
			$params_sql_or=Array();
			foreach($contribs as $contrib)
			{
			
				$params_sql_or[]="LOWER(name) LIKE LOWER(".$this->gen_pdo_name($idx_param).")||'%'";
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$contrib;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
				$idx_param++;
			}
			$params_sql_or_group["contributor"]=$params_sql_or;
		}
		
		if($request->request->has("namecontrib"))
		{
			$contribs=$request->get("namecontrib");
			$params_sql_or=Array();
			foreach($contribs as $contrib)
			{
			
				$params_sql_or[]="LOWER(people) LIKE LOWER(".$this->gen_pdo_name($idx_param).")||'%'";
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$contrib;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
				$idx_param++;
			}
			$params_sql_or_group["contributor"]=$params_sql_or;
		}
		if($request->request->has("institute"))
		{
			$institutes=$request->get("institute");
			$params_sql_or=Array();
			foreach($institutes as $institute)
			{
				$params_sql_or[]="LOWER(institut) = LOWER(".$this->gen_pdo_name($idx_param).")";
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$institute;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
				$idx_param++;
			}
			$params_sql_or_group["institute"]=$params_sql_or;
		}
		
		if($request->request->has("rolecontrib"))
		{
			$roles=$request->get("rolecontrib");
			
			$params_sql_or=Array();
			foreach($roles as $role)
			{
				$params_sql_or[]="LOWER(contributorrole) = LOWER(".$this->gen_pdo_name($idx_param).")";
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$role;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
				$idx_param++;
			}
			$params_sql_or_group["role"]=$params_sql_or;
		}
		
		if($request->request->has("typecontrib"))
		{
			$types=$request->get("typecontrib");
			
			$params_sql_or=Array();
			foreach($types as $type)
			{
				$params_sql_or[]="LOWER(datetype) = LOWER(".$this->gen_pdo_name($idx_param).")";
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$type;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
				$idx_param++;
			}
			$params_sql_or_group["typecontrib"]=$params_sql_or;
		}
		
		if($request->request->has("contrib_year_from"))
		{
			$year_from=$request->get("contrib_year_from");
			if(strlen($year_from)>0)
			{
				$params_sql_or=Array();
				
				$params_sql_or[]="year >= ".$this->gen_pdo_name($idx_param);
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$year_from;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_INT;
				$idx_param++;
				
				$params_sql_or_group["year_from"]=$params_sql_or;
			}
		}
		
		if($request->request->has("contrib_year_to"))
		{
			$year_to=$request->get("contrib_year_to");
			$year_from=$request->get("contrib_year_from");
			if(strlen($year_to)>0)
			{
				$params_sql_or=Array();
				
				$params_sql_or[]="year <= ".$this->gen_pdo_name($idx_param);
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$year_to;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_INT;
				$idx_param++;
				
				$params_sql_or_group["year_to"]=$params_sql_or;
			}
		}
		
		$query_or_builder=Array();
			
		foreach($params_sql_or_group as $key=>$params_sql_or)
		{
			$params_sql[]="(".implode(" OR ", $params_sql_or ).")";
		}
		
		if(count($params_sql)>0)
		{
			$query_where=" WHERE ".implode(" AND ", $params_sql );				
		}
		else
		{
			$query_where="";				
		}
		
		$RAW_QUERY="
			WITH main_q AS (SELECT dcontribution.pk, 
				dcontribution.idcontribution ,
				datetype, date, year,
				name			
FROM dcontribution  LEFT JOIN dlinkcontribute ON dcontribution.idcontribution=dlinkcontribute.idcontribution 
				INNER JOIN dcontributor ON dlinkcontribute.idcontributor=dcontributor.idcontributor ".$query_where."  GROUP BY dcontribution.pk, dcontribution.idcontribution ,
				datetype, date, year),
		    desc_q AS
			(SELECT dcontribution.pk ,	string_agg(
					COALESCE(contributorrole||':','')||
					COALESCE(' '||people||' '||institut,'')
				,';' order by contributororder) as people_desc
				FROM dcontribution  LEFT JOIN dlinkcontribute ON dcontribution.idcontribution=dlinkcontribute.idcontribution 
				INNER JOIN dcontributor ON dlinkcontribute.idcontributor=dcontributor.idcontributor
				GROUP BY dcontribution.pk, dcontribution.idcontribution ,
				datetype, date, year				
				)		
			SELECT main_q.* ,desc_q.people_desc, count(main_q.*) OVER() AS full_count
			FROM main_q LEFT JOIN desc_q ON main_q.pk=desc_q.pk  ORDER BY main_q.idcontribution OFFSET :offset LIMIT :limit;";
		$em = $this->container->get('doctrine')->getEntityManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		foreach($params_pdo as $key=>$val)
		{
			$statement->bindParam($key, $val["value"],  $val["type"]);				
		}
		$statement->bindParam(":offset", $offset, \PDO::PARAM_INT);
		$statement->bindParam(":limit", $page_size, \PDO::PARAM_INT);
		$statement->execute();
		$results = $statement->fetchAll(\PDO::FETCH_ASSOC);	
		if(count($results)>0)
		{
			$count_all=$results[0]["full_count"];
			$pagination = array(
				'page' => $current_page,
				'route' => 'search_main',
				'pages_count' => ceil($count_all / $page_size)
			);
			
			return $this->render('@App/search_results_contributions.html.twig', array("results"=>$results, "pagination"=>$pagination, "page_size"=>$page_size, "nb_results"=>$count_all));
		}
		else
		{
			return $this->render('@App/no_results_bs.html.twig');
		}
		return $this->render('@App/no_results_bs.html.twig');
	}
	
	
	public function satellite_searchAction(Request $request)
	{
		$current_page=$request->get("current_page",1);
        $page_size=$request->get("page_size",$this->page_size);
		$offset=(((int)$current_page)-1)* (int)$page_size;
		
		$results=Array();
		$pagination=Array();
		
		$params_sql=Array();
		$params_sql_or=Array();
		$params_sql_or_group=Array();
		$params_pdo=Array();
		$idx_param=1;
		
					
		$this->pdo_param_general($request, "collections", "idcollection",$params_sql_or, $params_pdo,$params_sql_or_group, $idx_param,\PDO::PARAM_STR, "LIKE","||'%'");
		$this->pdo_param_general($request, "idobject", "iddoc",$params_sql_or, $params_pdo,$params_sql_or_group, $idx_param,\PDO::PARAM_INT);
		$this->pdo_param_general($request, "satnum", "satnum",$params_sql_or, $params_pdo,$params_sql_or_group, $idx_param,\PDO::PARAM_INT);
		$this->pdo_param_general($request, "sattype", "sattype",$params_sql_or, $params_pdo, $params_sql_or_group, $idx_param,\PDO::PARAM_STR, "LIKE","");
		$this->pdo_param_general($request, "sensor", "sensor",$params_sql_or, $params_pdo, $params_sql_or_group, $idx_param,\PDO::PARAM_STR, "LIKE","");
		$this->pdo_param_general($request, "moderadar", "moderadar",$params_sql_or, $params_pdo, $params_sql_or_group, $idx_param,\PDO::PARAM_STR, "LIKE","");
		$this->pdo_param_general($request, "orbit", "orbit",$params_sql_or, $params_pdo, $params_sql_or_group,$idx_param,\PDO::PARAM_STR, "LIKE","");
		$this->pdo_param_general($request, "pathrack", "pathrack",$params_sql_or, $params_pdo, $params_sql_or_group, $idx_param,\PDO::PARAM_STR, "LIKE","");
		$this->pdo_param_general($request, "rowframe", "rowframe",$params_sql_or, $params_pdo, $params_sql_or_group, $idx_param,\PDO::PARAM_STR, "LIKE","");
		
		$this->pdo_param_string($request, "date_from", "date",$params_sql_or, $params_pdo, $params_sql_or_group, $idx_param,\PDO::PARAM_STR, ">=","");
		
		$this->pdo_param_string($request, "date_to", "date",$params_sql_or, $params_pdo, $params_sql_or_group, $idx_param,\PDO::PARAM_STR, "<=","");
		
		foreach($params_sql_or_group as $key=>$params_sql_or)
		{
			$params_sql[]="(".implode(" OR ", $params_sql_or ).")";
		}
		
		if(count($params_sql)>0)
		{
			$query_where=" WHERE ".implode(" AND ", $params_sql );				
		}
		else
		{
			$query_where="";				
		}
		
		$RAW_QUERY='
			SELECT ddocsatellite.*, ddocument.pk AS pk_doc, count(*) OVER() AS full_count FROM ddocsatellite LEFT JOIN ddocument ON ddocsatellite.iddoc=ddocument.iddoc AND ddocsatellite.idcollection=ddocument.idcollection '.$query_where.' OFFSET :offset LIMIT :limit;';
		$em = $this->container->get('doctrine')->getEntityManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		foreach($params_pdo as $key=>$val)
		{
			$statement->bindParam($key, $val["value"],  $val["type"]);				
		}
		$statement->bindParam(":offset", $offset, \PDO::PARAM_INT);
		$statement->bindParam(":limit", $page_size, \PDO::PARAM_INT);
		$statement->execute();
		$results = $statement->fetchAll(\PDO::FETCH_ASSOC);	
		if(count($results)>0)
		{
			$count_all=$results[0]["full_count"];
			$pagination = array(
				'page' => $current_page,
				'route' => 'search_main',
				'pages_count' => ceil($count_all / $page_size)
			);
			
			return $this->render('@App/search_results_satellite.html.twig', array("results"=>$results, "pagination"=>$pagination, "page_size"=>$page_size, "nb_results"=>$count_all));
		}
		else
		{
			return $this->render('@App/no_results_bs.html.twig');
		}
		return $this->render('@App/no_results_bs.html.twig');
		
		
	}
	
	public function contributor_searchAction(Request $request)
	{
		$current_page=$request->get("current_page",1);
        $page_size=$request->get("page_size",$this->page_size);
		$offset=(((int)$current_page)-1)* (int)$page_size;
		
		$namecontrib=$request->get("namecontrib");
		$institute=$request->get("institute");
		$results=Array();
		$pagination=Array();
		
		$params_sql=Array();
		$params_sql_or=Array();
		$params_sql_or_group=Array();
		$params_pdo=Array();
		$idx_param=1;
		
		if($request->request->has("namecontrib"))
		{
			$contribs=$request->get("namecontrib");
			$params_sql_or=Array();
			foreach($contribs as $contrib)
			{
			
				$params_sql_or[]="LOWER(people) LIKE LOWER(".$this->gen_pdo_name($idx_param).")||'%'";
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$contrib;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
				$idx_param++;
			}
			$params_sql_or_group["contributor"]=$params_sql_or;
		}
		if($request->request->has("institute"))
		{
			$institutes=$request->get("institute");
			$params_sql_or=Array();
			foreach($institutes as $institute)
			{
				$params_sql_or[]="LOWER(institut) = LOWER(".$this->gen_pdo_name($idx_param).")";
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$institute;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
				$idx_param++;
			}
			$params_sql_or_group["institute"]=$params_sql_or;
		}
		
		$query_or_builder=Array();
			
		foreach($params_sql_or_group as $key=>$params_sql_or)
		{
			$params_sql[]="(".implode(" OR ", $params_sql_or ).")";
		}
		
		if(count($params_sql)>0)
		{
			$query_where=" WHERE ".implode(" AND ", $params_sql );				
		}
		else
		{
			$query_where="";				
		}
		
		$RAW_QUERY='
			WITH main_q AS (SELECT * FROM dcontributor '.$query_where.')
			SELECT *, count(*) OVER() AS full_count FROM main_q ORDER BY people, institut, peoplefonction OFFSET :offset LIMIT :limit;';
		$em = $this->container->get('doctrine')->getEntityManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		foreach($params_pdo as $key=>$val)
		{
			$statement->bindParam($key, $val["value"],  $val["type"]);				
		}
		$statement->bindParam(":offset", $offset, \PDO::PARAM_INT);
		$statement->bindParam(":limit", $page_size, \PDO::PARAM_INT);
		$statement->execute();
		$results = $statement->fetchAll(\PDO::FETCH_ASSOC);	
		if(count($results)>0)
		{
			$count_all=$results[0]["full_count"];
			$pagination = array(
				'page' => $current_page,
				'route' => 'search_main',
				'pages_count' => ceil($count_all / $page_size)
			);
			
			return $this->render('@App/search_results_contrib.html.twig', array("results"=>$results, "pagination"=>$pagination, "page_size"=>$page_size, "nb_results"=>$count_all));
		}
		else
		{
			return $this->render('@App/no_results_bs.html.twig');
		}
		return $this->render('@App/no_results_bs.html.twig');
	}
	
	public function flightplan_searchAction(Request $request)
	{
		$current_page=$request->get("current_page",1);
        $page_size=$request->get("page_size",$this->page_size);
		$offset=(((int)$current_page)-1)* (int)$page_size;
		
		$fid=$request->get("fid");
		$nombloc=$request->get("nombloc");
		$bid=$request->get("bid");
		$band=$request->get("band");
		$results=Array();
		$pagination=Array();
		
		$params_sql=Array();
		$params_sql_or=Array();
		$params_sql_or_group=Array();
		$params_pdo=Array();
		$idx_param=1;
		
		if($request->request->has("fid"))
		{
			
			$fid=$request->get("fid");
			if(strlen($fid)>0)
			{
				$params_sql_or=Array();
				$fids=[$fid];
				foreach($fids as $fid)
				{
					
						$params_sql_or[]="LOWER(fid) LIKE LOWER(".$this->gen_pdo_name($idx_param).")";
						$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$fid;
						$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
						$idx_param++;
					
				}
				$params_sql_or_group["fid"]=$params_sql_or;
			}
		}
		if($request->request->has("nombloc"))
		{
			$nombloc=$request->get("nombloc");
			if(strlen($nombloc)>0)
			{
				$params_sql_or=Array();
				$nomblocs=[$nombloc];
				foreach($nomblocs as $nombloc)
				{
					
						$params_sql_or[]="LOWER(nombloc) = LOWER(".$this->gen_pdo_name($idx_param).")";
						$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$nombloc;
						$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
						$idx_param++;
					
				}
				$params_sql_or_group["nombloc"]=$params_sql_or;
			}
		}
		if($request->request->has("bid"))
		{
			$bid=$request->get("bid");
			if(strlen($bid))
			{
				
				$params_sql_or=Array();
				$bids=[$bid];
				foreach($bids as $bid)
				{
					
						$params_sql_or[]="LOWER(bid) LIKE LOWER(".$this->gen_pdo_name($idx_param).")";
						$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$bid;
						$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
						$idx_param++;
					
				}
				$params_sql_or_group["bid"]=$params_sql_or;
			}
		}
		if($request->request->has("band"))
		{
			$band=$request->get("band");
			if(strlen($band)>0)
			{
				$params_sql_or=Array();
				$bands=[$band];
				foreach($bands as $band)
				{
					
						$params_sql_or[]="LOWER(bande) LIKE LOWER(".$this->gen_pdo_name($idx_param).")";
						$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$band;
						$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
						$idx_param++;
					
				}
				$params_sql_or_group["band"]=$params_sql_or;
			}
		}
		
		$query_or_builder=Array();
			
		foreach($params_sql_or_group as $key=>$params_sql_or)
		{
			$params_sql[]="(".implode(" OR ", $params_sql_or ).")";
		}
		
		if(count($params_sql)>0)
		{
			$query_where=" WHERE ".implode(" AND ", $params_sql );				
		}
		else
		{
			$query_where="";				
		}
		
		$RAW_QUERY='
			WITH main_q AS (SELECT * FROM docplanvol '.$query_where.')
			SELECT *, count(*) OVER() AS full_count FROM main_q ORDER BY fid OFFSET :offset LIMIT :limit;';
		$em = $this->container->get('doctrine')->getEntityManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		foreach($params_pdo as $key=>$val)
		{
			$statement->bindParam($key, $val["value"],  $val["type"]);				
		}
		$statement->bindParam(":offset", $offset, \PDO::PARAM_INT);
		$statement->bindParam(":limit", $page_size, \PDO::PARAM_INT);
		$statement->execute();
		$results = $statement->fetchAll(\PDO::FETCH_ASSOC);	
		if(count($results)>0)
		{
			$count_all=$results[0]["full_count"];
			$pagination = array(
				'page' => $current_page,
				'route' => 'search_main',
				'pages_count' => ceil($count_all / $page_size)
			);
			
			return $this->render('@App/search_results_flightplans.html.twig', array("results"=>$results, "pagination"=>$pagination, "page_size"=>$page_size, "nb_results"=>$count_all));
		}
		else
		{
			return $this->render('@App/no_results_bs.html.twig');
		}
		return $this->render('@App/no_results_bs.html.twig');
	}
	
	public function main_searchAction(Request $request)
    {
	
		$order=$request->get("order","idcollection, idobject");

		$order_dir=$request->get("order_dir","ASC");

		$doc_type=$request->get("doc_type");
		
		$current_page=$request->get("current_page",1);
        $page_size=$request->get("page_size",$this->page_size);
		$offset=(((int)$current_page)-1)* (int)$page_size;
		
		$contributor=$request->get("contributor");
		$results=Array();
		$pagination=Array();
		$order_inj=Array("idcollection, idobject", "iddoc", "idobject", "idcollection" , "col_1_value","col_2_value", "col_3_value","col_4_value" );
		
		if(!in_array($order, $order_inj))
		{
			$order=$order_inj[0];
		}
		if(($order_dir=="ASC"||$order_dir=="DESC")&& in_array($order, $order_inj))
		{
			
			$params_sql=Array();
			$params_sql_or=Array();
			$params_sql_or_group=Array();
			$params_pdo=Array();
			$idx_param=1;
			
			$display_csv=false;
			if(strtolower($request->get("csv","false"))=="true")
			{
				$display_csv=true;
			}
			
			if($request->request->has("doc_type"))
			{
				
				$types=$request->get("doc_type");
				$params_sql_or=Array();
				foreach($types as $type)
				{
					$params_sql_or[]="main_type= ".$this->gen_pdo_name($idx_param);
					$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$type;
					$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
					$idx_param++;
				}
				$params_sql_or_group["main_type"]=$params_sql_or;
				
			}
			
			if($request->request->has("collections"))
			{
				$collections=$request->get("collections");
				$params_sql_or=Array();
				foreach($collections as $collection)
				{
					$params_sql_or[]="idcollection= ".$this->gen_pdo_name($idx_param);
					$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$collection;
					$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
					$idx_param++;
				}
				$params_sql_or_group["collections"]=$params_sql_or;
				
			}
			
			if($request->request->has("contributor"))
			{
				$contribs=$request->get("contributor");
				$params_sql_or=Array();
				if($request->request->has("role"))
				{
					$roles=$request->get("role");
					
					foreach($contribs as $contrib)
					{		
						
						$sql_tmp="EXISTS (SELECT contr.pk FROM v_all_contributions_to_object contr WHERE contr.idcontributor=".$this->gen_pdo_name($idx_param)." AND a.pk=contr.pk"
						;
						$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$contrib;
						$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_INT;
						$idx_param++;
						
						$tmp_roles=Array();
						foreach($roles as $role)
						{
							$tmp_roles[]="contributorrole=".$this->gen_pdo_name($idx_param);
							$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$role;
							$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
							$idx_param++;
						}
						if(count($tmp_roles)>0)
						{
							$tmp_roles_sql=implode(" OR ",$tmp_roles);
							$sql_tmp.= " AND (".$tmp_roles_sql.")";
						}
						$sql_tmp.=")";
						$params_sql_or[]=$sql_tmp;
					}
					$params_sql_or_group["contributor"]=$params_sql_or;
				
				}
				else
				{					
					foreach($contribs as $contrib)
					{
						
						$params_sql_or[]="EXISTS (SELECT contr.pk FROM v_all_contributions_to_object contr WHERE contr.idcontributor=".$this->gen_pdo_name($idx_param)." AND a.pk=contr.pk)";
						$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$contrib;
						$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_INT;
						$idx_param++;
					}
					$params_sql_or_group["contributor"]=$params_sql_or;
				}
				
			}
			
			if($request->request->has("idobject"))
			{
				$idobjects=$request->get("idobject");
				$params_sql_or=Array();
				foreach($idobjects as $idobject)
				{
					
					$params_sql_or[]="idobject= ".$this->gen_pdo_name($idx_param);                                          
                   
					$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$idobject;
					$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_INT;
					$idx_param++;
				}
				$params_sql_or_group["idobject"]=$params_sql_or;
				
			}
            
            if($request->request->has("keywords"))
			{
				$keywords=$request->get("keywords");
				$params_sql_or=Array();
				foreach($keywords as $keyword)
				{
					$keyword=strtolower($keyword);
					$params_sql_or[]="EXISTS (SELECT keyw.main_pk FROM tv_keyword_hierarchy_to_object keyw WHERE path_word  ~* ".$this->gen_pdo_name($idx_param."reg")." AND a.pk=keyw.main_pk)
                     OR col_1_value ILIKE '%'||".$this->gen_pdo_name($idx_param)."||'%'
                    OR col_2_value  ILIKE '%'||".$this->gen_pdo_name($idx_param)."||'%'
                    OR col_3_value  ILIKE '%'||".$this->gen_pdo_name($idx_param)."||'%'
                    OR col_4_value ILIKE '%'||".$this->gen_pdo_name($idx_param)."||'%'    
                    ";
					$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$keyword;
					$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
                    $params_pdo[$this->gen_pdo_name($idx_param)."reg"]["value"]=".*/".$keyword."/.*";
					$params_pdo[$this->gen_pdo_name($idx_param)."reg"]["type"]=\PDO::PARAM_STR;
					$idx_param++;
				}
				$params_sql_or_group["keywords"]=$params_sql_or;
				
			}
			
			if($request->request->has("wkt_search"))
			{
				$params_sql_or=Array();
				$wkt_search=$request->get("wkt_search");
                if(strlen(trim($wkt_search)))
                {
                    $params_sql_or[]="(ST_INTERSECTS(ST_SETSRID(ST_Point(coord_long, coord_lat),4326), ST_GEOMFROMTEXT(".$this->gen_pdo_name($idx_param).",4326)) 
					OR 
					ST_INTERSECTS(geom, ST_GEOMFROMTEXT(".$this->gen_pdo_name($idx_param).",4326)) 
					)";
                    $params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$wkt_search;
                    $params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
                   
					$params_sql_or_group["wkt_search"]=$params_sql_or;
                }			
            }
			
			if($request->request->has("date_from"))
			{
				$params_sql_or=Array();
				$date_from=$request->get("date_from","");
                if(strlen(trim($date_from)))
                {
                   $date_type=$request->get("date_type","ALL");
				  
				   $year_from=substr($date_from, 0,4);
				  
				   $date_point="EXISTS(SELECT idpt FROM dloccenter loc WHERE date>=:datefrom AND loc.pk=b.fk_localitie)";
				   $contribution_year="EXISTS (SELECT contr.pk FROM v_all_contributions_to_object contr WHERE year>=:year_from AND a.pk=contr.pk)";
				   $contribution_day="EXISTS (SELECT contr.pk FROM v_all_contributions_to_object contr WHERE date>=:datefrom AND a.pk=contr.pk)";
				   if($date_type=="ALL")
				   {
						$params_sql_or[]=$date_point;
						$params_sql_or[]=$contribution_day;
						$params_pdo[":datefrom"]["value"]=$date_from;
						$params_pdo[":datefrom"]["type"]=\PDO::PARAM_STR;
						$params_sql_or[]=$contribution_year;
						$params_pdo[":year_from"]["value"]=$year_from;
						$params_pdo[":year_from"]["type"]=\PDO::PARAM_STR;
						
				   }
				   elseif($date_type=="contribution_year")
				   {
						$params_sql_or[]=$contribution_year;
						$params_pdo[":year_from"]["value"]=$year_from;
						$params_pdo[":year_from"]["type"]=\PDO::PARAM_STR;
				   }
				   elseif($date_type=="contribution_day")
				   {	
						$params_sql_or[]=$contribution_day;
						$params_pdo[":datefrom"]["value"]=$date_from;
						$params_pdo[":datefrom"]["type"]=\PDO::PARAM_STR;
				   }
				   elseif($date_type=="locality")
				   {
					   $params_sql_or[]=$date_point;
					   $params_pdo[":datefrom"]["value"]=$date_from;
					   $params_pdo[":datefrom"]["type"]=\PDO::PARAM_STR;
					   
				   }
				   $params_sql_or_group["date_from"]=$params_sql_or;
                }			
            }
			if($request->request->has("date_to"))
			{
				$params_sql_or=Array();
				$date_to=$request->get("date_to","");
                if(strlen(trim($date_to)))
                {
                   $date_type=$request->get("date_type","ALL");
				  
				   $year_to=substr($date_to, 0,4);
				  
				   $date_point="EXISTS(SELECT idpt FROM dloccenter loc WHERE date<=:dateto AND loc.pk=b.fk_localitie)";
				   $contribution_year="EXISTS (SELECT contr.pk FROM v_all_contributions_to_object contr WHERE year<=:year_to AND a.pk=contr.pk)";
				   $contribution_day="EXISTS (SELECT contr.pk FROM v_all_contributions_to_object contr WHERE date<=:dateto AND a.pk=contr.pk)";
				   if($date_type=="ALL")
				   {
						$params_sql_or[]=$date_point;
						$params_sql_or[]=$contribution_day;
						$params_pdo[":dateto"]["value"]=$date_to;
						$params_pdo[":dateto"]["type"]=\PDO::PARAM_STR;
						$params_sql_or[]=$contribution_year;
						$params_pdo[":year_to"]["value"]=$year_to;
						$params_pdo[":year_to"]["type"]=\PDO::PARAM_STR;
						
				   }
				   elseif($date_type=="contribution_year")
				   {
						$params_sql_or[]=$contribution_year;
						$params_pdo[":year_to"]["value"]=$year_to;
						$params_pdo[":year_to"]["type"]=\PDO::PARAM_STR;
				   }
				   elseif($date_type=="contribution_day")
				   {	
						$params_sql_or[]=$contribution_day;
						$params_pdo[":dateto"]["value"]=$date_to;
						$params_pdo[":dateto"]["type"]=\PDO::PARAM_STR;
				   }
				   elseif($date_type=="locality")
				   {
					   $params_sql_or[]=$date_point;
					   $params_pdo[":dateto"]["value"]=$date_to;
					   $params_pdo[":dateto"]["type"]=\PDO::PARAM_STR;
					   
				   }
				   $params_sql_or_group["date_to"]=$params_sql_or;
                }			
            }
			
			if($request->request->has("stratum"))
			{
				$stratum_array=$request->get("stratum");
				$params_sql_or=Array();
				foreach($stratum_array as $stratum)
				{
					$stratum=strtolower($stratum);
					$params_sql_or[]="EXISTS(
					
					SELECT dloclitho.idpt FROM dloclitho INNER JOIN dloccenter ON 
					dloclitho.idcollection=dloccenter.idcollection 
					AND
					dloclitho.idpt=dloccenter.idpt 
					WHERE LOWER(TRIM(lithostratum))= LOWER(TRIM(".$this->gen_pdo_name($idx_param).")) 
					AND dloccenter.pk=b.fk_localitie 
					
					)";                                          
                   
					$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$stratum;
					$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
					$idx_param++;
				}
				$params_sql_or_group["stratum"]=$params_sql_or;
			}
			
			$query_or_builder=Array();
			
			foreach($params_sql_or_group as $key=>$params_sql_or)
			{
				//print_r($params_sql_or);
					$params_sql[]="(".implode(" OR ", $params_sql_or ).")";
			}
			
			if(count($params_sql)>0)
			{
				$query_where=" WHERE ".implode(" AND ", $params_sql );				
			}
			else
			{
				$query_where="";				
			}
			
			$em = $this->container->get('doctrine')->getEntityManager();
			
								
		    $RAW_QUERY="WITH main_q AS (SELECT DISTINCT a.pk, main_type, col_1_name, col_1_value, col_2_name, col_2_value, col_3_name, col_3_value, col_4_name, col_4_value, idobject, idcollection, string_agg(contributors,';' ) contributors, string_agg(institutions,';' ) institutions, coord_long, coord_lat 
			FROM public.mv_rmca_main_objects_description a
			LEFT JOIN public.mv_rmca_merge_all_objects_vertical_expand b
			ON a.pk=b.main_pk
			LEFT JOIN mv_all_contributions_to_object_agg_merge c
			ON a.pk=c.pk
			".$query_where.'
			GROUP BY a.pk, main_type, col_1_name, col_1_value, col_2_name, col_2_value, col_3_name, col_3_value, col_4_name, col_4_value, idobject, idcollection, coord_long, coord_lat
			)
            SELECT *, count(*) OVER() AS full_count FROM main_q ORDER BY '.$order.' '.$order_dir;
			if(!$display_csv)
			{
				$RAW_QUERY.=' OFFSET :offset LIMIT :limit';
			}
			$RAW_QUERY.=';';
			
			$statement = $em->getConnection()->prepare($RAW_QUERY);
			$order=3;
			
			foreach($params_pdo as $key=>$val)
			{
				$statement->bindParam($key, $val["value"],  $val["type"]); 
			}
			if(!$display_csv)
			{
				$statement->bindParam(":offset", $offset, \PDO::PARAM_INT);
				$statement->bindParam(":limit", $page_size, \PDO::PARAM_INT);
			}
			
			$statement->execute(); 
			if(!$display_csv)
			{
				$results = $statement->fetchAll();
				
				
				$geojson=null;
				$geojson_tmp=$this->init_map_json($results);
				if(count($geojson_tmp["features"])>0)
				{
					$geojson=json_encode($geojson_tmp);
				}
            }
			else
			{
				$results = $statement->fetchAll(\PDO::FETCH_ASSOC);
			}
			if(count($results)>0)
			{
				$headers1=Array();
				$headers2=Array();
				$headers3=Array();
				$headers4=Array();
				foreach($results as $row)
				{
					$headers1[$row["col_1_name"]]=$row["col_1_name"];
					$headers2[$row["col_2_name"]]=$row["col_2_name"];
					$headers3[$row["col_3_name"]]=$row["col_3_name"];
					$headers4[$row["col_4_name"]]=$row["col_4_name"];
				}
				ksort($headers1);
				ksort($headers2);
				ksort($headers3);
				ksort($headers4);
				
				$count_all=$results[0]["full_count"];
				$pagination = array(
				'page' => $current_page,
				'route' => 'search_main',
				'pages_count' => ceil($count_all / $page_size)
				);
				if(!$display_csv)
				{
					$is_modal=false;
					if($request->request->has("is_modal"))
					{
						if($request->get("is_modal"))
						{
							$is_modal=true;
						}
					}
					return $this->render('@App/search_all/search_results_bs.html.twig', array("results"=>$results, "pagination"=>$pagination, "page_size"=>$page_size, "nb_results"=>$count_all, "geojson"=>$geojson, "headers1"=>implode("\r\n", $headers1),"headers2"=>implode("\r\n", $headers2),"headers3"=>implode("\r\n", $headers3), "headers4"=>implode("\r\n", $headers4), "is_modal"=>$is_modal ));
				}
				else
				{
					$returned=Array();
					//print_r($results);
					$returned_str="";
					$new_str=Array();
					$i=0;
					$func=function($l)
					{
						
						return str_replace("\n", " _NEWLINE_ ",str_replace("\r", " _NEWLINE_ ", $l));
					};
					foreach($results as $row)
					{
						if($i==0)
						{
							
							$new_str[]=implode("\t",array_keys($row));
						}
						$row=array_map($func, $row);	
						$new_str[]=implode("\t",$row);
						$i++;
						
					}
					$response = new Response(implode("\r\n", $new_str));
					$response->headers->set('Content-Type', 'text/csv');       
					$response->headers->set('Content-Disposition', 'attachment; filename="testing.csv"');
					return $response;
				}
			}
			else
			{
				return $this->render('@App/no_results_bs.html.twig');
			}
		}
		else
		{
			return $this->render('@App/no_results_bs.html.twig');
		}
		
    }
    
    
	
	public function searchsampleAction(Request $request){
		return $this->render('@App/searchsample.html.twig');  
	}
	
	public function searchmineralAction(Request $request){
		return $this->render('@App/searchmineral.html.twig');  
	}
	
	public function searchcontributionAction(Request $request){
		return $this->render('@App/searchcontribution.html.twig');  
	}
	
	public function searchcontributorAction(Request $request){
		return $this->render('@App/searchcontributor.html.twig');  
	}
	
	public function searchpointsAction(Request $request){
		return $this->render('@App/searchpoints.html.twig');
    }
	
	public function searchdocumentAction(Request $request){
		return $this->render('@App/searchdocument.html.twig');
    }
	
    public function searchsatelliteAction(Request $request){
		return $this->render('@App/searchsatellite.html.twig');
    }
	
	public function searchflightplanAction(Request $request){
		return $this->render('@App/searchflightplan.html.twig');
    }
	
	public function searchstratumAction(Request $request){
		return $this->render('@App/searchstratum.html.twig');
    }
	
	public function searchdrillingAction(Request $request){
		return $this->render('@App/searchdrilling.html.twig');
    }
	
	public function results_searchsampleAction($queryvals,Request $request)
	{
		/*//require_once("@App/debug/PHPDebug.php");
		//$debug = new PHPDebug();
		//$debug->debug("A very simple message");
		 */
		 
		$arrayqueryvals =  explode(",,", $queryvals); 
		$elem =array();
		foreach($arrayqueryvals as $e) {
            $elem1 = explode(":", $e);
			$elem[$elem1[0]]=$elem1[1];
        } 
		
		$querymagnet = "";
								
		switch($elem["lithomagnet"]) {
			 case -3:
				$querymagnet = "m.mesure1 < -10";
				break;
			 case -2:
				$querymagnet = "m.mesure1 < -5.001 AND m.mesure1 > -10";
				break;
			 case -1:
				$querymagnet = "m.mesure1 < -0.001 AND m.mesure1 > -5";
				break;
			case 1:
				$querymagnet = "m.mesure1 > 0 AND m.mesure1 < 50";
				break;
			case 2:
				$querymagnet = "m.mesure1 > 50.001 AND m.mesure1 < 100";
				break;
			case 3:
				$querymagnet = "m.mesure1 > 100.001 AND m.mesure1 < 250";
				break;
			case 4:
				$querymagnet = "m.mesure1 > 250";
				break;
		} 

		$data_array = array(
			array(":collection",	"'%".strtolower($elem["collection"])."%'",	"LOWER(d.idcollection) LIKE ",		"",0),
			array(":searchnum",		$elem["searchnum"],							"d.idsample = ",					"",0),
			array(":code",			"'%".strtolower($elem["code"])."%'",			"LOWER(d.fieldnum) LIKE ",			"",0),
			array(":museumnr",		$elem["museumnr"],							"d.museumnum = ",					"",0),
			array(":museumloc",		"'%".strtolower($elem["museumloc"])."%'",		"LOWER(d.museumlocation) LIKE ",	"",0),
			array(":boxnbr",		"'%".strtolower($elem["boxnbr"])."%'",		"LOWER(d.boxnumber) LIKE ",			"",0),
			array(":descr",			"'%".strtolower($elem["descr"])."%'",			"LOWER(d.sampledescription) LIKE ",	"",0),
			array(":weight",		$elem["weight"],							"d.weight = ",						"",0),
			array(":size",			"'%".strtolower($elem["size"])."%'",			"LOWER(d.size) LIKE ",				"",0),
			array(":dimension",		$elem["dimension"],							"d.dimentioncode = ",				"",0),
			array(":quality",		$elem["quality"],							"d.quality =  ",					"",0),
			array(":radioactivity",	$elem["radioactivity"],						"d.radioactivity = ",				"1",0),
			array(":slimplate",		$elem["slimplate"],							"d.slimplate = ",					"TRUE",0),
			array(":chemanalysis",	$elem["chemanalysis"],						"d.chemicalanalysis = ",			"TRUE",0),
			array(":holotype",		$elem["holotype"],							"d.holotype = ",					"TRUE",0),
			array(":paratype",		$elem["paratype"],							"d.paratype = ",					"TRUE",0),
			array(":loaninfo",		"'%".strtolower($elem["loaninfo"])."%'",		"LOWER(d.loaninformation) LIKE ",	"",0),
			array(":securitylevel",	$elem["securitylevel"],						"d.securitylevel = ",				"",0),
			array(":variousinfo",	strtolower($elem["variousinfo"]),			"LOWER(d.varioussampleinfo) LIKE ",	"",0),
			
			array(":idmineral",		$elem["idmineral"],							"s.idmineral = ",					"",1),
			array(":grademineral",	$elem["grademineral"],						"s.grade = ",						"",1),
			


			array(":classmineral",	"'%".strtolower($elem["classmineral"])."%'",	"(l.rank = 'class' AND  l.fmname LIKE ",											") OR LOWER(l.fmparent) LIKE ",1),
			array(":groupmineral",	"'%".strtolower($elem["groupmineral"])."%'",	"(l.rank = 'group' AND  l.fmname LIKE ",											") OR LOWER(l.fmparent) LIKE ",1),
			array(":mineral",		"'%".strtolower($elem["mineral"])."%'",		"LOWER(l.fmname) LIKE ",															" OR LOWER(l.mname) LIKE ",1),

			array(":formulamineral","'%".strtolower($elem["formulamineral"])."%'","LOWER(l.mformula) LIKE ",			"",1),
			
			array(":lithomineral",	"'%".strtolower($elem["lithomineral"])."%'",	"LOWER(h2.mineral) LIKE ",			"",2),
			array(":lithominnum_from",$elem["lithominnum_from"],				"h2.minnum >= ",					"",2),
			array(":lithominnum_to",$elem["lithominnum_to"],					"h2.minnum <= ",					"",2),
			array(":lithoweight_from",$elem["lithoweight_from"],				"h1.weightsample >= ",				"",2),
			array(":lithoweight_to",$elem["lithoweight_to"],					"h1.weightsample <= ",				"",2),
			array(":lithoobserv",	"'%".strtolower($elem["lithoobserv"])."%'",	"LOWER(h2.observationhm) LIKE ",	"",2),
			
			array(":lithogranulo",	$elem["lithogranulo"],						"g.weighttot != ",					"0",3),
			array(":lithomagnet",	$elem["lithomagnet"],						$querymagnet,						";",3)
		);
		
		/*$RAW_QUERY = "SELECT * FROM dsample d ";
		$mineralsearch = 0;
		for ($x = 0; $x < sizeof($data_array); $x++) {
			if ($data_array[$x][4] == 1 AND ((	(substr($data_array[$x][1],-1) != "%" AND strlen($data_array[$x][1]) != 0) OR 
												(substr($data_array[$x][1],-1) == "%" AND strlen($data_array[$x][1]) != 2)) AND
												($data_array[$x][1] != '%all%'))){
				$mineralsearch = 1;
			}
		}
		if ($mineralsearch == 1){
			$RAW_QUERY = $RAW_QUERY."LEFT JOIN DSamMinerals s ON s.IDCollection = d.IDCollection AND s.IDSample = d.IDSample LEFT JOIN lminerals l ON l.IDMineral = s.IDMineral";
		}*/
		
		$RAW_QUERY = "SELECT d.pk as pk,
							d.idcollection as idcollection, 
							d.idsample as idsample, 
							d.fieldnum as fieldnum, 
							d.museumnum as museumnum, 
							d.museumlocation as museumlocation, 
							d.boxnumber as boxnumber, 
							d.sampledescription as sampledescription,              
							d.weight as weight, 
							d.quantity as quantity, 
							d.size as size, 
							d.dimentioncode as dimentioncode, 
							d.quality::integer as quality, 
							d.slimplate as slimplate, 
							d.chemicalanalysis as chemicalanalysis,
							CASE WHEN d.holotype = TRUE THEN 'H' ELSE '' END	||	CASE WHEN d.paratype = TRUE THEN 'P' ELSE '' END AS type,
							d.holotype as holotype, 
							d.paratype as paratype, 
							d.radioactivity as radioactivity, 
							d.loaninformation as loaninformation, 
							d.securitylevel as securitylevel, 
							d.varioussampleinfo as varioussampleinfo,
							string_agg(distinct l.mname,',') as mname, 
							string_agg(distinct l.mformula,' -- ') as mformula, 
							string_agg(distinct l.fmparent,',') as fmparent, 
							string_agg(distinct l.mparent,',') as mparent,
							string_agg(distinct l.fmname,',') as fmname, 
							string_agg(to_char(h1.weightsample, '999.99') ,',') as weightsample,
							string_agg(distinct (h2.mineral || '(' || h2.minnum::varchar || ')'),', ' ) as mineral2,
							string_agg(h2.minnum::varchar,',') as minnum ,
							string_agg(distinct h1.observationhm,',') as observationhm,
							string_agg(distinct to_char(g.weighttot, '999.99'),',') as weighttot,
							string_agg(to_char(m.weight, '999.99'),',') as mweight,
							string_agg(distinct to_char(m.mesure1, '9999.999'),',')::double precision as mesure1
						FROM dsample d 
						LEFT JOIN DSamMinerals 	s 	ON s.IDCollection = d.IDCollection AND s.IDSample = d.IDSample 
						LEFT JOIN lminerals 	l 	ON l.IDMineral = s.IDMineral 
						LEFT JOIN dsamheavymin 	h1 	ON h1.IDCollection = d.IDCollection AND h1.IDSample = d.IDSample 
						LEFT JOIN dsamheavymin2 h2 	ON h2.IDCollection = d.IDCollection AND h2.IDSample = d.IDSample 
						LEFT JOIN dsamgranulo 	g 	ON g.IDCollection = d.IDCollection AND g.IDSample = d.IDSample
						LEFT JOIN dsammagsusc 	m 	ON m.IDCollection = d.IDCollection AND m.IDSample = d.IDSample";
	   
		$RAW_QUERY = $RAW_QUERY." WHERE";
		for ($x = 0; $x < sizeof($data_array); $x++) {
			if($data_array[$x][3] == "1" OR $data_array[$x][3] == "TRUE" OR $data_array[$x][3] == "0"){  //Case  of checkboxes with value 1 if chosen --> add only =TRUE
				if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] == "1"){
					$andq = " AND ".$data_array[$x][2].$data_array[$x][3];
				}ELSE{
					$andq = '';
				}
			}ELSE{ 
				if($data_array[$x][3] == ";"){  //Case  of combobox with ranges of values --> add only part 1
					if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] != 'all'){
						$andq = " AND ".$data_array[$x][2];
					}ELSE{
						$andq = '';
					}
				}ELSE{ 
					//Case  of integers or strings
				/*	if (((substr($data_array[$x][1],-1) != "%" AND strlen($data_array[$x][1]) != 0) OR 
						(substr($data_array[$x][1],-1) == "%" AND strlen($data_array[$x][1]) != 2)) AND
						($data_array[$x][1] != '%all%') AND	($data_array[$x][1] != 'all')){
						
						$andq = " AND ".$data_array[$x][2].$data_array[$x][0].$data_array[$x][3];
						if (strpos($data_array[$x][3],"OR") == 1 | strpos($data_array[$x][3],"OR") == 2){
							$andq = " AND ".$data_array[$x][2].$data_array[$x][0].$data_array[$x][3].$data_array[$x][0];
						}
					}ELSE{
						$andq = '';
					}*/

					if (
						(
						  (substr($data_array[$x][1],-2) != "%'" AND strlen($data_array[$x][1]) != 0) OR 
						  (substr($data_array[$x][1],-2) == "%'" AND strlen($data_array[$x][1]) != 4)
						) AND
						($data_array[$x][1] != "'%all%'") AND	
						($data_array[$x][1] != 'all') AND 
						($data_array[$x][1] != '01-01-1970')
					   ){
							$andq = " AND ".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3];

							if (strpos($data_array[$x][3],"OR") == 1 | strpos($data_array[$x][3],"OR") == 2){
								$andq = " AND (".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3].$data_array[$x][1].")";
							}
					}ELSE{
						$andq = '';
					}
				}
			}
			$RAW_QUERY = $RAW_QUERY.$andq;
		} 
		$RAW_QUERY = $RAW_QUERY." GROUP BY d.pk, d.idcollection, d.idsample, d.fieldnum, d.museumnum, d.museumlocation, d.boxnumber, d.sampledescription, d.weight, d.quantity, d.size, d.dimentioncode, 
		d.quality, d.slimplate, d.chemicalanalysis, d.holotype, d.paratype, d.radioactivity, d.loaninformation, d.securitylevel, d.varioussampleinfo";
		
		$orderfield = $elem["sortDirection"]; 
		$RAW_QUERY = $RAW_QUERY." ORDER BY ".$orderfield;    
		$RAW_QUERY = str_replace("WHERE AND", "WHERE", $RAW_QUERY);
		$RAW_QUERY = str_replace("WHERE ORDER", " ORDER", $RAW_QUERY);
		$RAW_QUERY = str_replace("WHERE GROUP", "GROUP", $RAW_QUERY);

		echo "<script type='text/javascript'>alert('".$RAW_QUERY."');</script>";

		$em = $this->getDoctrine()->getManager();
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		
		for ($x = 0; $x < sizeof($data_array); $x++) {
			if(strpos($RAW_QUERY, $data_array[$x][0]) !== false){
				$statement->bindValue($data_array[$x][0], $data_array[$x][1]);
			}
		}
		//return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );
        $statement->execute();
        $Arrayresult = $statement->fetchAll();
		//return new Response('<html><body>'.print_r($Arrayresult).'</body></html>' );
		
		//paginator++++++++++++++++++++++++++++++++++
		$paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $Arrayresult,
            $request->query->getInt('page', 1), 	/*current page number*/ 
			$elem["NbrResByPage"] 					/*images per page*/
        );
	/*	$pagination->setParam('sort','type');
		$pagination->setParam('direction', 'desc');

		$sortParams = array("sort"=>"fieldnum", "direction"=>"asc");
		$v = $sortParams['sort'];
		echo "<script type='text/javascript'>alert('$v');</script>";
		if(!$request->query->get('sort') && !$request->query->get('direction')) {
			$pagination->setParam('sort', $sortParams['sort']);
			$pagination->setParam('direction', $sortParams['direction']);
		}*/

		$queryvals = $queryvals.",,nbrres:".sizeof($Arrayresult);
		//$queryvals = $queryvals.",NbrResByPage:".$elem["NbrResByPage"];

		return $this->render('@App/results_searchsample.html.twig', array('queryvals' => $queryvals,'results' => $pagination));  
	}
	
	public function results_searchmineralAction($queryvals,Request $request){
		$arrayqueryvals =  explode(",,", $queryvals); 
		$elem =array();
		foreach($arrayqueryvals as $e) {
            $elem1 = explode(":", $e);
			$elem[$elem1[0]]=$elem1[1];
        } 
		
		$data_array = array(			
			array(":idmineral",		$elem["idmineral"],							"idmineral = ",																	"",1),
			array(":classmineral",	"'%".strtolower($elem["classmineral"])."%'",	"(rank = 'class' AND  fmname LIKE ",											") OR LOWER(fmparent) LIKE ",1),
			array(":groupmineral",	"'%".strtolower($elem["groupmineral"])."%'",	"(rank = 'group' AND  fmname LIKE ",											") OR LOWER(fmparent) LIKE ",1),
			array(":mineral",		"'%".strtolower($elem["mineral"])."%'",		"LOWER(fmname) LIKE ",															" OR LOWER(mname) LIKE ",1),
			array(":formulamineral","'%".strtolower($elem["formulamineral"])."%'","LOWER(mformula) LIKE ",														"",1),
			array(":parentmineral",	"'%".strtolower($elem["parentmineral"])."%'","LOWER(mparent) LIKE ",															" OR LOWER(fmparent) LIKE ",1)
		);
		
		$RAW_QUERY = "SELECT pk,
							idmineral,
							rank,
							mname, 
							fmname,
							mformula, 
							fmparent, 
							mparent						
						FROM lminerals";
	   
		$RAW_QUERY = $RAW_QUERY." WHERE";
		for ($x = 0; $x < sizeof($data_array); $x++) {
			if($data_array[$x][3] == "1" OR $data_array[$x][3] == "TRUE" OR $data_array[$x][3] == "0"){  //Case  of checkboxes with value 1 if chosen --> add only =TRUE
				if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] == "1"){
					$andq = " AND ".$data_array[$x][2].$data_array[$x][3];
				}ELSE{
					$andq = '';
				}
			}ELSE{ 
				if($data_array[$x][3] == ";"){  //Case  of combobox with ranges of values --> add only part 1
					if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] != 'all'){
						$andq = " AND ".$data_array[$x][2];
					}ELSE{
						$andq = '';
					}
				}ELSE{ 
					//Case  of integers or strings
					if (
						(
						  (substr($data_array[$x][1],-2) != "%'" AND strlen($data_array[$x][1]) != 0) OR 
						  (substr($data_array[$x][1],-2) == "%'" AND strlen($data_array[$x][1]) != 4)
						) AND
						($data_array[$x][1] != "'%all%'") AND	
						($data_array[$x][1] != 'all') AND 
						($data_array[$x][1] != '01-01-1970')
					   ){
							$andq = " AND ".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3];

							if (strpos($data_array[$x][3],"OR") == 1 | strpos($data_array[$x][3],"OR") == 2){
								$andq = " AND (".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3].$data_array[$x][1].")";
							}
					}ELSE{
						$andq = '';
					}
				}
			}
			$RAW_QUERY = $RAW_QUERY.$andq;
		} 
		
		$orderfield = $elem["sortDirection"]; 
		$RAW_QUERY = $RAW_QUERY." ORDER BY ".$orderfield;    
		$RAW_QUERY = str_replace("WHERE AND", "WHERE", $RAW_QUERY);
		$RAW_QUERY = str_replace("WHERE ORDER", "ORDER", $RAW_QUERY);
		
		echo "<script type='text/javascript'>alert('$RAW_QUERY');</script>";

		$em = $this->getDoctrine()->getManager();
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		
		for ($x = 0; $x < sizeof($data_array); $x++) {
			if(strpos($RAW_QUERY, $data_array[$x][0]) !== false){
				$statement->bindValue($data_array[$x][0], $data_array[$x][1]);
			}
		}
		
        $statement->execute();
        $Arrayresult = $statement->fetchAll();
		
		//paginator++++++++++++++++++++++++++++++++++
		$paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $Arrayresult,
            $request->query->getInt('page', 1), 	/*current page number*/ 
			$elem["NbrResByPage"] 					/*images per page*/
        );
		
		$queryvals = $queryvals.",,nbrres:".sizeof($Arrayresult);
		
		return $this->render('@App/results_searchmineral.html.twig', array('queryvals' => $queryvals,'results' => $pagination));  
	}
	
	public function results_searchcontributionAction($queryvals,Request $request){
		$arrayqueryvals =  explode(",,", $queryvals); 
		$elem =array();
		foreach($arrayqueryvals as $e) {
            $elem1 = explode(":", $e);
			$elem[$elem1[0]]=$elem1[1];
        } 
		
		$data_array = array(			
			array(":idcontribution",	$elem["idcontribution"],				"c.idcontribution = ",		"",1),
			array(":type",				"'%".strtolower($elem["type"])."%'",		"LOWER(datetype) LIKE ",	"",1),
			array(":year",				$elem["year"],							"year = ",					"",1),
			array(":date_from",			date('m-d-Y', strtotime($elem["date_from"])),"date >= ",					"",2),
			array(":date_to",			date('m-d-Y', strtotime($elem["date_to"])),	"date <= ",					"",2),
			array(":idcontributor",		$elem["idcontributor"],					"o.idcontributor = ",		"",1),
			array(":people",			"'%".strtolower($elem["people"])."%'",	"LOWER(people) LIKE ",		"",1),
			array(":function",			"'%".strtolower($elem["function"])."%'",	"LOWER(peoplefonction) LIKE ",		"",1),
			array(":title",				"'%".strtolower($elem["title"])."%'",		"LOWER(peopletitre) LIKE ",		"",1),
			array(":status",			"'%".strtolower($elem["status"])."%'",	"LOWER(peoplestatut) LIKE ",		"",1),
			array(":institute",			"'%".strtolower($elem["institute"])."%'",	"LOWER(institut) LIKE ",		"",1),
			array(":role",				"'%".strtolower($elem["role"])."%'",		"LOWER(contributorrole) LIKE ",		"",1),
			array(":order",				$elem["order"],							"contributororder = ",					"",1)
		);
				
		$RAW_QUERY = "SELECT c.pk as pkcontribution,
							c.idcontribution,
							datetype,
							year, 
							date,			
							o.pk as pkcontributor,
							o.idcontributor,
							people,
							peoplefonction,
							peopletitre,
							peoplestatut,
							institut,
							contributorrole,
							contributororder
						FROM dcontribution c
						LEFT JOIN dlinkcontribute l ON l.idcontribution = c.idcontribution
						LEFT JOIN dcontributor o ON l.idcontributor = o.idcontributor";
	   
		$RAW_QUERY = $RAW_QUERY." WHERE";
		/*for ($x = 0; $x < sizeof($data_array); $x++) {
			if (((substr($data_array[$x][1],-1) != "%" AND strlen($data_array[$x][1]) != 0) OR 
				(substr($data_array[$x][1],-1) == "%" AND strlen($data_array[$x][1]) != 2)) AND
				($data_array[$x][1] != '%all%') AND	($data_array[$x][1] != 'all') AND ($data_array[$x][1] != '01-01-1970')){
			
				$andq = " AND ".$data_array[$x][2].$data_array[$x][0].$data_array[$x][3];
		   
				if (strpos($data_array[$x][3],"OR") == 1 | strpos($data_array[$x][3],"OR") == 2){
					$andq = " AND ".$data_array[$x][2].$data_array[$x][0].$data_array[$x][3].$data_array[$x][0];
				}
			}ELSE{
				$andq = '';
			}
			$RAW_QUERY = $RAW_QUERY.$andq;
		} */

		for ($x = 0; $x < sizeof($data_array); $x++) {
			if($data_array[$x][3] == "1" OR $data_array[$x][3] == "TRUE" OR $data_array[$x][3] == "0"){  //Case  of checkboxes with value 1 if chosen --> add only =TRUE
				if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] == "1"){
					$andq = " AND ".$data_array[$x][2].$data_array[$x][3];
				}ELSE{
					$andq = '';
				}
			}ELSE{ 
				if($data_array[$x][3] == ";"){  //Case  of combobox with ranges of values --> add only part 1
					if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] != 'all'){
						$andq = " AND ".$data_array[$x][2];
					}ELSE{
						$andq = '';
					}
				}ELSE{ 
					//Case  of integers or strings
					if (
						(
						  (substr($data_array[$x][1],-2) != "%'" AND strlen($data_array[$x][1]) != 0) OR 
						  (substr($data_array[$x][1],-2) == "%'" AND strlen($data_array[$x][1]) != 4)
						) AND
						($data_array[$x][1] != "'%all%'") AND	
						($data_array[$x][1] != 'all') AND 
						($data_array[$x][1] != '01-01-1970')
					   ){
							$andq = " AND ".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3];

							if (strpos($data_array[$x][3],"OR") == 1 | strpos($data_array[$x][3],"OR") == 2){
								$andq = " AND (".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3].$data_array[$x][1].")";
							}
					}ELSE{
						$andq = '';
					}
				}
			}
			$RAW_QUERY = $RAW_QUERY.$andq;
		} 
	
		$orderfield = $elem["sortDirection"]; 
		$RAW_QUERY = $RAW_QUERY." ORDER BY ".$orderfield;    
		$RAW_QUERY = str_replace("WHERE AND", "WHERE", $RAW_QUERY);
		$RAW_QUERY = str_replace("WHERE ORDER", "ORDER", $RAW_QUERY);
		//print_r($RAW_QUERY);
		//echo "<script type='text/javascript'>alert('$RAW_QUERY');</script>";
		//return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );

		$em = $this->getDoctrine()->getManager();
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		
		for ($x = 0; $x < sizeof($data_array); $x++) {
			if(strpos($RAW_QUERY, $data_array[$x][0]) !== false){
				$statement->bindValue($data_array[$x][0], $data_array[$x][1]);
			}
		}
		
        $statement->execute();
        $Arrayresult = $statement->fetchAll();
	//	return new Response('<html><body>'.print_r($Arrayresult).'</body></html>' );
		//paginator++++++++++++++++++++++++++++++++++
		$paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $Arrayresult,
            $request->query->getInt('page', 1), 	/*current page number*/ 
			$elem["NbrResByPage"] 					/*images per page*/
        );
		
		$queryvals = $queryvals.",,nbrres:".sizeof($Arrayresult);
		
		return $this->render('@App/results_searchcontribution.html.twig', array('queryvals' => $queryvals,'results' => $pagination));  
	}
		
	public function results_searchpointsAction($queryvals,Request $request){
		$arrayqueryvals =  explode(",,", $queryvals); 
		$elem =array();
		foreach($arrayqueryvals as $e) {
            $elem1 = explode(":", $e);
			$elem[$elem1[0]]=$elem1[1];
        } 

		$data_array = array(		
			array(":collection",			"'%".strtolower($elem["collection"])."%'",			"LOWER(dlc.idcollection) LIKE ",	"",	0),	
			array(":searchnum",				$elem["searchnum"],									"idpt = ",							"",	0),		
			array(":altitude_from",			$elem["altitude_from"],								"altitude >= ",						"",	2),
			array(":altitude_to",			$elem["altitude_to"],								"altitude <= ",						"",	2),			
			array(":date_from",				date('m-d-Y', strtotime($elem["date_from"])),		"date >= ",							"",	2),
			array(":date_to",				date('m-d-Y', strtotime($elem["date_to"])),			"date <= ",							"",	2),
			array(":fieldnum",				"'%".strtolower($elem["fieldnum"])."%'",			"LOWER(fieldnum) LIKE ",			"",	1),
			array(":place",					"'%".strtolower($elem["place"])."%'",				"LOWER(place) LIKE ",				"",	1),
			array(":country",				"'%".strtolower($elem["country"])."%'",				"LOWER(country) LIKE ",				"",	1),
			array(":geodescription",		"'%".strtolower($elem["geodescription"])."%'",		"LOWER(geodescription) LIKE ",		"",	1),
			array(":positiondescription",	"'%".strtolower($elem["positiondescription"])."%'",	"LOWER(positiondescription) LIKE ",	"",	1),
			array(":variousinfo",			"'%".strtolower($elem["variousinfo"])."%'",			"LOWER(variousinfo) LIKE ",			"",	1) //,
			//array(":docref",				"'%".strtolower($elem["docref"])."%'",				"LOWER(docref) LIKE ",				"",	1)
		);
				
		$RAW_QUERY = "SELECT 	dlc.pk as pkloccenter,
								idcollection,
								coord_lat, 
								coord_long,
								idpt,  
								altitude, 
								date, 
								fieldnum, 
								place, 
								country,
								geodescription, 
								positiondescription, 
								variousinfo, 
								docref, 
								idprecision
						FROM dloccenter dlc";
						
		if ($elem["wkt"] == "n"){
		   $initialWhere = " WHERE ";  
		}else{
		   $initialWhere = " WHERE ST_Intersects(ST_MakePoint(dlc.coord_long, dlc.coord_lat)::geometry,'".$elem["wkt"]."'::geometry)";
		}
		$RAW_QUERY = $RAW_QUERY.$initialWhere;
	

		for ($x = 0; $x < sizeof($data_array); $x++) {
			if($data_array[$x][3] == "1" OR $data_array[$x][3] == "TRUE" OR $data_array[$x][3] == "0"){  //Case  of checkboxes with value 1 if chosen --> add only =TRUE
				if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] == "1"){
					$andq = " AND ".$data_array[$x][2].$data_array[$x][3];
				}ELSE{
					$andq = '';
				}
			}ELSE{ 
				if($data_array[$x][3] == ";"){  //Case  of combobox with ranges of values --> add only part 1
					if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] != 'all'){
						$andq = " AND ".$data_array[$x][2];
					}ELSE{
						$andq = '';
					}
				}ELSE{ 
					//Case  of integers or strings
					if (
						(
						  (substr($data_array[$x][1],-2) != "%'" AND strlen($data_array[$x][1]) != 0) OR 
						  (substr($data_array[$x][1],-2) == "%'" AND strlen($data_array[$x][1]) != 4)
						) AND
						($data_array[$x][1] != "'%all%'") AND	
						($data_array[$x][1] != 'all') AND 
						($data_array[$x][1] != '01-01-1970')
					   ){
							$andq = " AND ".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3];

							if (strpos($data_array[$x][3],"OR") == 1 | strpos($data_array[$x][3],"OR") == 2){
								$andq = " AND (".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3].$data_array[$x][1].")";
							}
					}ELSE{
						$andq = '';
					}
				}
			}
			$RAW_QUERY = $RAW_QUERY.$andq;
		} 
		
		$orderfield = $elem["sortDirection"]; 

		$RAW_QUERY = $RAW_QUERY." ORDER BY ".$orderfield;    
		$RAW_QUERY= preg_replace ( '/WHERE\s+AND/i', 'WHERE' , $RAW_QUERY );
		$RAW_QUERY= preg_replace ( '/WHERE\s+ORDER/i', 'ORDER' , $RAW_QUERY );
		//echo "<script type='text/javascript'>alert('$RAW_QUERY');</script>";
		//return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );

		$em = $this->getDoctrine()->getManager();
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		
		for ($x = 0; $x < sizeof($data_array); $x++) {
			if(strpos($RAW_QUERY, $data_array[$x][0]) !== false){
				$statement->bindValue($data_array[$x][0], $data_array[$x][1]);
			}
		}
		
        $statement->execute();
        $Arrayresult = $statement->fetchAll();

		//paginator++++++++++++++++++++++++++++++++++
		$paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $Arrayresult,
            $request->query->getInt('page', 1), 	/*current page number*/ 
			$elem["NbrResByPage"] 					/*images per page*/
        );
		
		$queryvals = $queryvals.",,nbrres:".sizeof($Arrayresult);

		return $this->render('@App/results_searchpoint.html.twig', array('queryvals' => $queryvals,'results' => $pagination));  		
	}
	
	public function results_searchdocsAction($queryvals,Request $request){
		$arrayqueryvals =  explode(",,", $queryvals); 
		$elem =array();
		foreach($arrayqueryvals as $e) {
            $elem1 = explode(":", $e);
			$elem[$elem1[0]]=$elem1[1];
        } 
		
		$data_array = array(		
			array(":iddoccollection",	"'%".strtolower($elem["iddoccollection"])."%'",		"LOWER(idcollection) LIKE ",		"",	0),	
			array(":iddoc",				$elem["iddoc"],										"iddoc = ",							"",	0),		
			array(":numarchive",		"'%".strtolower($elem["numarchive"])."%'",			"LOWER(numarchive) LIKE ",			"",	1),
			array(":caption",			"'%".strtolower($elem["caption"])."%'",				"LOWER(caption) LIKE ",				"",	1),	
			array(":centralnum",		"'%".strtolower($elem["centralnum"])."%'",			"LOWER(centralnum) LIKE ",			"",	1),
			array(":medium",			"'%".strtolower($elem["medium"])."%'",				"LOWER(medium) LIKE ",				"",	1),
			array(":doclocation",		"'%".strtolower($elem["doclocation"])."%'",			"LOWER(location) LIKE ",			"",	1),
			array(":numericallocation",	"'%".strtolower($elem["numericallocation"])."%'",	"LOWER(numericallocation) LIKE ",	"",	1),
			array(":filename",			"'%".strtolower($elem["filename"])."%'",			"LOWER(filename) LIKE ",			"",	1),
			array(":docinfo",			"'%".strtolower($elem["docinfo"])."%'",				"LOWER(docinfo) LIKE ",				"",	1),
			array(":edition",			"'%".strtolower($elem["edition"])."%'",				"LOWER(edition) LIKE ",				"",	1),
			array(":pubplace",			"'%".strtolower($elem["pubplace"])."%'",			"LOWER(pubplace) LIKE ",			"",	1),
			array(":doccartotype",		"'%".strtolower($elem["doccartotype"])."%'",		"LOWER(doccartotype) LIKE ",		"",	1)
		);
				
		$RAW_QUERY = "SELECT 	pk,
								idcollection,
								iddoc, 
								numarchive,
								caption,  
								centralnum, 
								medium, 
								location, 
								numericallocation, 
								filename,
								docinfo, 
								edition, 
								pubplace, 
								doccartotype
						FROM ddocument";
						
		$RAW_QUERY = $RAW_QUERY." WHERE ";

		for ($x = 0; $x < sizeof($data_array); $x++) {
			if($data_array[$x][3] == "1" OR $data_array[$x][3] == "TRUE" OR $data_array[$x][3] == "0"){  //Case  of checkboxes with value 1 if chosen --> add only =TRUE
				if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] == "1"){
					$andq = " AND ".$data_array[$x][2].$data_array[$x][3];
				}ELSE{
					$andq = '';
				}
			}ELSE{ 
				if($data_array[$x][3] == ";"){  //Case  of combobox with ranges of values --> add only part 1
					if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] != 'all'){
						$andq = " AND ".$data_array[$x][2];
					}ELSE{
						$andq = '';
					}
				}ELSE{ 
					//Case  of integers or strings
					if (
						(
						  (substr($data_array[$x][1],-2) != "%'" AND strlen($data_array[$x][1]) != 0) OR 
						  (substr($data_array[$x][1],-2) == "%'" AND strlen($data_array[$x][1]) != 4)
						) AND
						($data_array[$x][1] != "'%all%'") AND	
						($data_array[$x][1] != 'all') AND 
						($data_array[$x][1] != '01-01-1970')
					   ){
							$andq = " AND ".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3];

							if (strpos($data_array[$x][3],"OR") == 1 | strpos($data_array[$x][3],"OR") == 2){
								$andq = " AND (".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3].$data_array[$x][1].")";
							}
					}ELSE{
						$andq = '';
					}
				}
			}
			$RAW_QUERY = $RAW_QUERY.$andq;
		} 
		
		$orderfield = $elem["sortDirection"]; 

		$RAW_QUERY = $RAW_QUERY." ORDER BY ".$orderfield;    
		$RAW_QUERY= preg_replace ( '/WHERE\s+AND/i', 'WHERE' , $RAW_QUERY );
		$RAW_QUERY= preg_replace ( '/WHERE\s+ORDER/i', 'ORDER' , $RAW_QUERY );
		echo "<script type='text/javascript'>alert('$RAW_QUERY');</script>";
		//return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );

		$em = $this->getDoctrine()->getManager();
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		
		for ($x = 0; $x < sizeof($data_array); $x++) {
			if(strpos($RAW_QUERY, $data_array[$x][0]) !== false){
				$statement->bindValue($data_array[$x][0], $data_array[$x][1]);
			}
		}
		
        $statement->execute();
        $Arrayresult = $statement->fetchAll();

		//paginator++++++++++++++++++++++++++++++++++
		$paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $Arrayresult,
            $request->query->getInt('page', 1), 	/*current page number*/ 
			$elem["NbrResByPage"] 					/*images per page*/
        );
		
		$queryvals = $queryvals.",,nbrres:".sizeof($Arrayresult);

		return $this->render('@App/results_searchdocument.html.twig', array('queryvals' => $queryvals,'results' => $pagination));  		
	}
	
	public function results_searchallAction($queryvals,Request $request)
	{
		$arrayqueryvals =  explode(",,", $queryvals); 
		$elem =array();
		foreach($arrayqueryvals as $e) {
            $elem1 = explode(":", $e);
			$elem[$elem1[0]]=$elem1[1];
        } 

		$querymagnet = "";
								
		switch($elem["lithomagnet"]) {
			 case -3:
				$querymagnet = "sms.mesure1 < -10";
				break;
			 case -2:
				$querymagnet = "sms.mesure1 < -5.001 AND sms.mesure1 > -10";
				break;
			 case -1:
				$querymagnet = "sms.mesure1 < -0.001 AND sms.mesure1 > -5";
				break;
			case 1:
				$querymagnet = "sms.mesure1 > 0 AND sms.mesure1 < 50";
				break;
			case 2:
				$querymagnet = "sms.mesure1 > 50.001 AND sms.mesure1 < 100";
				break;
			case 3:
				$querymagnet = "sms.mesure1 > 100.001 AND sms.mesure1 < 250";
				break;
			case 4:
				$querymagnet = "sms.mesure1 > 250";
				break;
		} 
//inp_searchcoll -- inp_searchsamplecoll   et inp_searchcode  inp_searchfieldnum
		$data_array = array(
			array(":samplecollection",	"'%".strtolower($elem["samplecollection"])."%'","LOWER(ds.idcollection) LIKE ",									"",									0),
			array(":searchnum_sample",	$elem["searchnum_sample"],						"ds.idsample = ",												"",									0),
			array(":fieldnum_sample",	"'%".strtolower($elem["fieldnum_sample"])."%'",	"LOWER(ds.fieldnum) LIKE ",										"",									0),
			array(":museumnr",			$elem["museumnr"],								"ds.museumnum = ",												"",									0),
			array(":museumloc",			"'%".strtolower($elem["museumloc"])."%'",		"LOWER(ds.museumlocation) LIKE ",								"",									0),
			array(":boxnbr",			"'%".strtolower($elem["boxnbr"])."%'",			"LOWER(ds.boxnumber) LIKE ",									"",									0),
			array(":descr",				"'%".strtolower($elem["descr"])."%'",			"LOWER(ds.sampledescription) LIKE ",							"",									0),
			array(":weight",			$elem["weight"],								"ds.weight = ",													"",									0),
			array(":size",				"'%".strtolower($elem["size"])."%'",			"LOWER(ds.size) LIKE ",											"",									0),
			array(":dimension",			$elem["dimension"],								"ds.dimentioncode = ",											"",									0),
			array(":quality",			$elem["quality"],								"ds.quality =  ",												"",									0),
			array(":radioactivity",		$elem["radioactivity"],							"ds.radioactivity = ",											"1",								0),
			array(":slimplate",			$elem["slimplate"],								"ds.slimplate = ",												"TRUE",								0),
			array(":chemanalysis",		$elem["chemanalysis"],							"ds.chemicalanalysis = ",										"TRUE",								0),
			array(":holotype",			$elem["holotype"],								"ds.holotype = ",												"TRUE",								0),
			array(":paratype",			$elem["paratype"],								"ds.paratype = ",												"TRUE",								0),
			array(":loaninfo",			"'%".strtolower($elem["loaninfo"])."%'",		"LOWER(ds.loaninformation) LIKE ",								"",									0),
			array(":securitylevel",		$elem["securitylevel"],							"ds.securitylevel = ",											"",									0),
			array(":variousinfo",		strtolower($elem["variousinfo"]),				"LOWER(ds.varioussampleinfo) LIKE ",							"",									0),
			
			array(":idmineral",			$elem["idmineral"],								"dsm.idmineral = ",												"",									1),
			array(":grademineral",		$elem["grademineral"],							"dsm.grade = ",													"",									1),
			


			array(":classmineral",		"'%".strtolower($elem["classmineral"])."%'",	"(lm.rank = 'class' AND  lm.fmname LIKE ",						") OR LOWER(lm.fmparent) LIKE ",	1),
			array(":groupmineral",		"'%".strtolower($elem["groupmineral"])."%'",	"(lm.rank = 'group' AND  lm.fmname LIKE ",						") OR LOWER(lm.fmparent) LIKE ",	1),
			array(":mineral",			"'%".strtolower($elem["mineral"])."%'",			"LOWER(lm.fmname) LIKE ",										" OR LOWER(lm.mname) LIKE ",		1),

			array(":formulamineral",	"'%".strtolower($elem["formulamineral"])."%'",	"LOWER(lm.mformula) LIKE ",										"",									1),
			
			array(":lithomineral",		"'%".strtolower($elem["lithomineral"])."%'",	"LOWER(shm2.mineral) LIKE ",									"",									2),
			array(":lithominnum_from",	$elem["lithominnum_from"],						"shm2.minnum >= ",												"",									2),
			array(":lithominnum_to",	$elem["lithominnum_to"],						"shm2.minnum <= ",												"",									2),
			array(":lithoweight_from",	$elem["lithoweight_from"],						"shm1.weightsample >= ",										"",									2),
			array(":lithoweight_to",	$elem["lithoweight_to"],						"shm1.weightsample <= ",										"",									2),
			array(":lithoobserv",		"'%".strtolower($elem["lithoobserv"])."%'",		"LOWER(shm2.observationhm) LIKE ",								"",									2),
			
			array(":lithogranulo",		$elem["lithogranulo"],							"sg.weighttot != ",												"0",								3),
			array(":lithomagnet",		$elem["lithomagnet"],							$querymagnet,													";",								3),
			//contributions
			array(":idcontribution",	$elem["idcontribution"],						"dc.idcontribution = ",											"",									1),
			array(":type",				"'%".strtolower($elem["type"])."%'",			"LOWER(dc.datetype) LIKE ",										"",									1),
			array(":year",				$elem["year"],									"dc.year = ",													"",									1),
			array(":date_from",			date('m-d-Y', strtotime($elem["date_from"])),	"dc.date >= ",													"",									2),
			array(":date_to",			date('m-d-Y', strtotime($elem["date_to"])),		"dc.date <= ",													"",									2),
			array(":idcontributor",		$elem["idcontributor"],							"dcr.idcontributor = ",											"",									1),
			array(":people",			"'%".strtolower($elem["people"])."%'",			"LOWER(dcr.people) LIKE ",										"",									1),
			array(":function",			"'%".strtolower($elem["function"])."%'",		"LOWER(dcr.peoplefonction) LIKE ",								"",									1),
			array(":title",				"'%".strtolower($elem["title"])."%'",			"LOWER(dcr.peopletitre) LIKE ",									"",									1),
			array(":status",			"'%".strtolower($elem["status"])."%'",			"LOWER(dcr.peoplestatut) LIKE ",								"",									1),
			array(":institute",			"'%".strtolower($elem["institute"])."%'",		"LOWER(dcr.institut) LIKE ",									"",									1),
			array(":role",				"'%".strtolower($elem["role"])."%'",			"LOWER(contributorrole) LIKE ",									"",									1),
			array(":order",				$elem["order"],									"contributororder = ",											"",									1),
	
			array(":loccollection",		"'%".strtolower($elem["loccollection"])."%'",	"LOWER(dlocc.idcollection) LIKE ",								"",									0),			
			array(":searchidpoint",		$elem["searchidpoint"],							"dlocc.idpt = ",												"",									0),					
			array(":date_from",			date('m-d-Y', strtotime($elem["date_from"])),	"dlocc.date >= ",												"",									2),
			array(":date_to",			date('m-d-Y', strtotime($elem["date_to"])),		"dlocc.date <= ",												"",									2),
			array(":fieldnum",			"'%".strtolower($elem["fieldnum"])."%'",		"LOWER(dlocc.fieldnum) LIKE ",									"",									1),
			array(":place",				"'%".strtolower($elem["place"])."%'",			"LOWER(dlocc.place) LIKE ",										"",									1),
			array(":geodescription",	"'%".strtolower($elem["geodescription"])."%'",	"LOWER(dlocc.geodescription) LIKE ",							"",									1),
			array(":positiondescription","'%".strtolower($elem["positiondescription"])."%'","LOWER(dlocc.positiondescription) LIKE ",					"",									1),
			array(":docref",			"'%".strtolower($elem["docref"])."%'",			"LOWER(dlocc.docref) LIKE ",									"",									1)
		);
		
		$RAW_QUERY = "  SELECT  
    string_agg(distinct dcr.people,',') as people, 
    string_agg(distinct dcr.institut,',') as institut, 
    dc.idcontribution,
	dc.pk AS pk_contribution,
    dc.datetype,
    dc.date AS contribution_date,
    ds.pk AS pk_sample,
    ds.idsample,
    ds.fieldnum,
    ds.museumnum,
    ds.museumlocation,
    ds.boxnumber,
    ds.sampledescription,
    ds.weight,
    ds.quantity,
    ds.size,
    ds.dimentioncode,
    ds.quality::integer AS quality,
    ds.slimplate,
    ds.chemicalanalysis,
        CASE
            WHEN ds.holotype = true THEN 'H'::text
            ELSE ''::text
        END ||
        CASE
            WHEN ds.paratype = true THEN 'P'::text
            ELSE ''::text
        END AS type,
    ds.holotype,
    ds.paratype,
    ds.radioactivity,
    ds.loaninformation,
    ds.securitylevel,
    ds.varioussampleinfo,
    string_agg(distinct lm.mname,',') as mname, 
    string_agg(distinct lm.mformula,' -- ') as mformula, 
    lm.fmname,
	dlocc.idpt,
	dlocc.pk AS pk_point,
    dlocc.coord_lat,
    dlocc.coord_long,
    dlocc.date AS loc_date,
    dlocc.place,
    dlocc.geodescription,
    dlocc.positiondescription,
    dlocsd.descript,
    dlocsd.idstratum,
    dlocl.descriptionstratum,
    dlocl.bottomstratum,
    dlocl.topstratum,
    dlocl.lithostratum,
    string_agg(distinct ddocut.title,',') as title, 
    ddocu.iddoc,
	ddocu.pk AS pk_doc,
    ddocu.medium,
    ddocu.docinfo,
    ddocu.doccartotype,
	s.token
   FROM dcontribution dc
     full outer JOIN dlinkcontribute dlc ON dc.idcontribution = dlc.idcontribution
     full outer JOIN dcontributor dcr ON dcr.idcontributor = dlc.idcontributor
     full outer JOIN dlinkcontsam dlcs ON dc.idcontribution = dlcs.idcontribution
     full outer JOIN dsample ds ON ds.idcollection::text = dlcs.idcollection::text AND ds.idsample = dlcs.id
     full outer JOIN dsamminerals dsm ON dsm.idcollection::text = ds.idcollection::text AND dsm.idsample = ds.idsample
     full outer JOIN lminerals lm ON dsm.idmineral = lm.idmineral
     full outer JOIN dsamheavymin shm1 ON shm1.idcollection::text = ds.idcollection::text AND shm1.idsample = ds.idsample
     full outer JOIN dsamheavymin2 shm2 ON shm2.idcollection::text = ds.idcollection::text AND shm2.idsample = ds.idsample
     full outer JOIN dsamgranulo sg ON sg.idcollection::text = ds.idcollection::text AND sg.idsample = ds.idsample
     full outer JOIN dsammagsusc sms ON sms.idcollection::text = ds.idcollection::text AND sms.idsample = ds.idsample
     full outer JOIN dlinkcontloc dlcloc ON dlcloc.idcontribution = dc.idcontribution
     full outer JOIN dloccenter dlocc ON dlcloc.idcollection::text = dlocc.idcollection::text AND dlcloc.id = dlocc.idpt
     full outer JOIN dloclitho dlocl ON dlocl.idcollection::text = dlocc.idcollection::text AND dlocl.idpt = dlocc.idpt
     full outer JOIN dlinklocsam dllocs ON dlocl.idcollection::text = dllocs.idcollectionloc::text AND dlocl.idpt = dllocs.idpt AND dlocl.idstratum = dllocs.idstratum AND ds.idcollection::text = dllocs.idcollecsample::text AND ds.idsample = dllocs.idsample
     full outer JOIN dlocstatumdesc dlocsd ON dlocl.idcollection::text = dlocsd.idcollection::text AND dlocl.idpt = dlocsd.idpt AND dlocl.idstratum = dlocsd.idstratum
     full outer JOIN dlinkcontdoc dlcd ON dc.idcontribution = dlcd.idcontribution
     full outer JOIN ddocument ddocu ON dlcd.idcollection::text = ddocu.idcollection::text AND dlcd.id = ddocu.iddoc
     full outer JOIN ddoctitle ddocut ON ddocu.idcollection::text = ddocut.idcollection::text AND ddocu.iddoc = ddocut.iddoc
     full outer JOIN dlinkdocloc dldocloc ON dldocloc.idcollecdoc::text = ddocu.idcollection::text AND dldocloc.iddoc = ddocu.iddoc AND dldocloc.idcollecloc::text = dlocc.idcollection::text AND dldocloc.idpt = dlocc.idpt
     full outer JOIN dlinkdocsam dldocs ON dldocs.idcollectiondoc::text = ddocu.idcollection::text AND dldocs.iddoc = ddocu.iddoc AND dldocs.idcollectionsample::text = ds.idcollection::text AND dldocs.idsample = ds.idsample, 
	 unnest(string_to_array(concat(ddocu.idcollection, ' ', dlcloc.idcollection, ' ', ds.idcollection), ' ')) s(token)";
	
	   if ($elem["wkt"] == "n"){
		   $initialWhere = " WHERE s.token <> ''"; // AND (ST_Intersects(ST_MakePoint(dlocc.coord_long, dlocc.coord_lat)::geometry,ST_MakePolygon( ST_GeomFromText('LINESTRING(-180 90,180 90,180 -90,-180 -90,-180 90)'))::geometry) OR coord_lat is null)";  
	   }else{
		   $initialWhere = " WHERE s.token <> '' AND ST_Intersects(ST_MakePoint(dlocc.coord_long, dlocc.coord_lat)::geometry,'".$elem["wkt"]."'::geometry)";
	   }
		$RAW_QUERY = $RAW_QUERY.$initialWhere;
		for ($x = 0; $x < sizeof($data_array); $x++) {
			if($data_array[$x][3] == "1" OR $data_array[$x][3] == "TRUE" OR $data_array[$x][3] == "0"){  //Case  of checkboxes with value 1 if chosen --> add only =TRUE
				if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] == "1"){
					$andq = " AND ".$data_array[$x][2].$data_array[$x][3];
				}ELSE{
					$andq = '';
				}
			}ELSE{ 
				if($data_array[$x][3] == ";"){  //Case  of combobox with ranges of values --> add only part 1
					if (strlen($data_array[$x][1]) != 0 AND $data_array[$x][1] != 'all'){
						$andq = " AND ".$data_array[$x][2];
					}ELSE{
						$andq = '';
					}
				}ELSE{ 
					//Case  of integers or strings
					if (
						(
						  (substr($data_array[$x][1],-2) != "%'" AND strlen($data_array[$x][1]) != 0) OR 
						  (substr($data_array[$x][1],-2) == "%'" AND strlen($data_array[$x][1]) != 4)
						) AND
						($data_array[$x][1] != "'%all%'") AND	
						($data_array[$x][1] != 'all') AND 
						($data_array[$x][1] != '01-01-1970')
					   ){
							$andq = " AND ".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3];

							if (strpos($data_array[$x][3],"OR") == 1 | strpos($data_array[$x][3],"OR") == 2){
								$andq = " AND (".$data_array[$x][2].$data_array[$x][1].$data_array[$x][3].$data_array[$x][1].")";
							}
					}ELSE{
						$andq = '';
					}
				}
			}
			$RAW_QUERY = $RAW_QUERY.$andq;
		} 
		
		$RAW_QUERY = $RAW_QUERY." GROUP BY dc.idcontribution, dc.datetype, contribution_date, dc.pk, ds.pk, ddocu.pk,dlocc.pk, ds.idsample, ds.fieldnum, ds.museumnum, ds.museumlocation, ds.boxnumber, ds.sampledescription, ds.weight, ds.quantity, ds.size, ds.dimentioncode, quality, ds.slimplate, ds.chemicalanalysis, type, ds.holotype, ds.paratype, ds.radioactivity, ds.loaninformation, ds.securitylevel, ds.varioussampleinfo, lm.fmname, dlocc.coord_lat, dlocc.coord_long, loc_date, dlocc.place, dlocc.geodescription, dlocc.positiondescription, dlocsd.descript,  dlocsd.idpt, dlocsd.idstratum, dlocl.descriptionstratum, dlocl.bottomstratum, dlocl.topstratum, dlocl.lithostratum, ddocu.iddoc, ddocu.medium, ddocu.docinfo, ddocu.doccartotype,s.token";
		echo "<script type='text/javascript'>alert('$RAW_QUERY');</script>";
		$orderfield = $elem["sortDirection"]; 
		$RAW_QUERY = $RAW_QUERY." ORDER BY ".$orderfield;    
		$RAW_QUERY = str_replace("WHERE AND", "WHERE", $RAW_QUERY);
		$RAW_QUERY = str_replace("WHERE ORDER", " ORDER", $RAW_QUERY);
		$RAW_QUERY = str_replace("WHERE GROUP", "GROUP", $RAW_QUERY);

		$em = $this->getDoctrine()->getManager();
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		
		for ($x = 0; $x < sizeof($data_array); $x++) {
			if(strpos($RAW_QUERY, $data_array[$x][0]) !== false){
				$statement->bindValue($data_array[$x][0], $data_array[$x][1]);
			}
		}
		//print_r($RAW_QUERY);
        $statement->execute(); 
		$Arrayresult = $statement->fetchAll();
		
		//paginator++++++++++++++++++++++++++++++++++
		$paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $Arrayresult,
            $request->query->getInt('page', 1), 	/*current page number*/ 
			$elem["NbrResByPage"] 					/*images per page*/
        );

		$queryvals = $queryvals.",,nbrres:".sizeof($Arrayresult);
		//$request->getSession()->getFlashBag('parameters')->add('P', $queryvals);
	/*	$session = $this->get('session');
		$session->set('p', array(
			'queryvals' => $queryvals,
		));*/
		return $this->render('@App/results_searchall.html.twig', array('queryvals' => $queryvals,'results' => $pagination));  		
	}
}