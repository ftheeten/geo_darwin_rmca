<?php

// src/AppBundle/Controller/AutocompleteController.php

namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AutocompleteController extends Controller
{
	protected $page_size=20;
    protected $limit_autocomplete=30;
	
	public function template_autocomplete($tablename, $fieldname, $searched, $all_mode=false ,$key_field=null , $where=null, $searched_type=\PDO::PARAM_STR, $comparator="LIKE", $suffix_searched="")
	{
		$em = $this->getDoctrine()->getManager();
		if($key_field ===null)
		{
				$key_field=$fieldname;
		}
        if(strlen($searched)>0||(strlen($searched)==0 && $where!==null))
        {
			
			if(strlen($searched)>0)
			{
				$RAW_QUERY = "SELECT DISTINCT ".$key_field." FROM ".$tablename." where ".$fieldname." ~* :func ". $suffix_searched." ORDER BY ".$fieldname." LIMIT :limit;"; 
            }
			elseif($where!==null)
			{
				$RAW_QUERY = "SELECT DISTINCT ".$key_field." FROM ".$tablename." ". $where. " ORDER BY ".$fieldname." LIMIT :limit;"; 
			}
            $statement = $em->getConnection()->prepare($RAW_QUERY);
			if($where==null)
			{		
					
				$statement->bindParam(":func", $searched, $searched_type );
            }			
			$statement->bindParam(":limit", $this->limit_autocomplete, \PDO::PARAM_INT);
            $statement->execute();			
            $names = $statement->fetchAll();
	
        }
		elseif(strlen($searched)==0 && $all_mode)
		{
			$RAW_QUERY = "SELECT DISTINCT ".$key_field." FROM ".$tablename." ORDER BY ".$fieldname." LIMIT :limit;";
			$statement = $em->getConnection()->prepare($RAW_QUERY);
			$statement->bindParam(":limit", $this->limit_autocomplete, \PDO::PARAM_INT);
            $statement->execute();
            $names = $statement->fetchAll();			
		}
        else
        {
            $names=[[]];
        }
        return new JsonResponse($names);
	}
	
	public function IDContributions_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		
		$arrayqueryvals =  explode("--", $_GET['code']); 		
		$type = $arrayqueryvals[0];
		$year = $arrayqueryvals[1];
		if ($type <> "" AND $year == ""){
			$WHERE_QUERY = "WHERE datetype LIKE '".$type."'";
		};
		if ($type == "" AND $year <> ""){
			$WHERE_QUERY = "WHERE year = ".$year;
		};
		if ($type <> "" AND $year <> ""){
			$WHERE_QUERY = "WHERE datetype LIKE '".$type."' AND year = ".$year;
		};
		
		$RAW_QUERY = "	SELECT coalesce(	idcontribution || '--' || datetype || '--' || to_char(date, 'DD/MM/YYYY') || '--' || year, 
										idcontribution || '--' || datetype || '--' || year,
										idcontribution || '--' || datetype
									) as idfull 
						FROM dcontribution  ".$WHERE_QUERY." ORDER BY idcontribution";
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $idcontr = $statement->fetchAll();
		//return new Response('<html><body>'.print_r($idcontr).'</body></html>');
        return new JsonResponse($idcontr);
    }
	
	public function Code_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$coll = strtolower($_GET['coll']);
		$num = strtolower($_GET['code']);
		if ($coll != "all"){
			$RAW_QUERY = "SELECT fieldnum FROM dsample where lower(fieldnum) LIKE '".$num."%' AND lower(idcollection) = '".$coll."';"; 
		}else{
			$RAW_QUERY = "SELECT fieldnum FROM dsample where lower(fieldnum) LIKE '".$num."%';"; 
		}
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $codes = $statement->fetchAll();
        
        return new JsonResponse($codes);
    }
	
	public function contribrole_autocompleteAction(Request $request){
		$num = strtolower($_GET['code']);
		return $this->template_autocomplete( "dlinkcontribute", "contributorrole", $num, true);
    }
	
	public function Parent_autocompleteAction(Request $request)
	{		
		$num = strtolower($_GET['code']);		
		return $this->template_autocomplete( "lminerals", "mparent", $num);
    }
	
	public function Minname_autocompleteAction(Request $request)
	{		
		$num = strtolower($_GET['code']);		
		return $this->template_autocomplete( "lminerals", "mname", $num);
    }
	
	public function Minfname_autocompleteAction(Request $request)
	{		
		$num = strtolower($_GET['code']);		
		return $this->template_autocomplete( "lminerals", "fmname", $num);
    }
	
	public function Minformula_autocompleteAction(Request $request){
		
		$num = strtolower($_GET['code']);		
        return $this->template_autocomplete( "lminerals", "mformula", $num);
    }
	
	public function Museumloc_autocompleteAction(Request $request)
	{
		$num = strtolower($_GET['code']);
		return $this->template_autocomplete( "dsample", "museumlocation", $num);
    }
	
	
	public function Box_autocompleteAction(Request $request)
	{
		$num = strtolower($_GET['code']);		
		return $this->template_autocomplete( "dsample", "boxnumber", $num);
    }
	
	public function idobject_autocompleteAction(Request $request){
		$num = strtolower($_GET['code']);
		$em = $this->getDoctrine()->getManager();
		$RAW_QUERY = "SELECT DISTINCT idobject FROM mv_rmca_merge_all_objects_vertical_expand where lower(idobject::varchar) LIKE '".$num."%';";
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $codes = $statement->fetchAll();
		return new JsonResponse($codes);		
    }
	
	public function contribtype_autocompleteAction(Request $request)
	{
		return $this->template_autocomplete( "dcontribution", "datetype", $request->query->get("code","") ,true);		
    }
	
	public function contribmainnames_autocompleteAction(Request $request)
	{	
       return $this->template_autocomplete("dcontribution", "name",$request->query->get("code",""), true );		
    }
	
	public function contribnames_autocompleteAction(Request $request)
	{	
       return $this->template_autocomplete("dcontributor", "people",$request->query->get("code",""), false, "people, idcontributor" );		
    }
	
	
	public function contribfunction_autocompleteAction(Request $request)
	{		
		return $this->template_autocomplete("dcontributor", "peoplefonction",$request->query->get("code",""),true);
    }
	
	public function contribstatus_autocompleteAction(Request $request)
	{		
		return $this->template_autocomplete("dcontributor", "peoplestatut",$request->query->get("code",""),true);
    }
	
	public function contribtitle_autocompleteAction(Request $request)
	{		
		return $this->template_autocomplete("dcontributor", "peopletitre",$request->query->get("code",""),true);
    }	
	
	public function contribinstitutions_autocompleteAction(Request $request){
		return $this->template_autocomplete("dcontributor", "institut",$request->query->get("code","") );		
    }
	
	public function keywords_autocompleteAction(Request $request){
	
		return $this->template_autocomplete("mv_merge_keywords", "word", $request->query->get("code",""), false, "*" , null, \PDO::PARAM_INT, "~*","");
    }
	
	public function sattype_autocompleteAction(Request $request)
	{
		return $this->template_autocomplete("ddocsatellite", "sattype",$request->query->get("code",""),false);
	}
	
	public function satorbit_autocompleteAction(Request $request)
	{
		return $this->template_autocomplete("ddocsatellite", "orbit",$request->query->get("code",""),false);
	}
	
	public function satsensor_autocompleteAction(Request $request)
	{
		return $this->template_autocomplete("ddocsatellite", "sensor",$request->query->get("code",""),false);
	}
	
	public function satmoderadar_autocompleteAction(Request $request)
	{
		return $this->template_autocomplete("ddocsatellite", "moderadar",$request->query->get("code",""),false);
	}
	
	public function satrowframe_autocompleteAction(Request $request)
	{
		return $this->template_autocomplete("ddocsatellite", "rowframe",$request->query->get("code",""),false);
	}
	
	public function lithostratum_autocompleteAction(Request $request)
	{
		return $this->template_autocomplete("dloclitho", "lithostratum",$request->query->get("code",""),false);
	}


}