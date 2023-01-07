<?php

// src/AppBundle/SampleController.php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Dsample;
use AppBundle\Entity\TDataLog;
use AppBundle\Entity\Codecollection;
use AppBundle\Entity\Dsamminerals;
use AppBundle\Entity\Lminerals;
use AppBundle\Entity\Dloclitho;
use AppBundle\Entity\Ddocument;
use AppBundle\Entity\Dcontribution;
use AppBundle\Entity\Dsamgranulo;
use AppBundle\Entity\Dlinklocsam;
use AppBundle\Entity\Dlinkcontsam;
use AppBundle\Entity\Dlinkdocsam;
use AppBundle\Form\DsampleType;
use AppBundle\Form\SearchAllForm;




use Symfony\Component\Form\FormError;


class SampleController extends AbstractController
{
	protected $limit_autocomplete=30;
	
	//2022
	

	
	
	
	public function addsamplegsAction(Request $request)
	{
		$this->set_sql_session();
		$allow=$this->check_collection_rights_general('AppBundle\Controller\SampleController');
		if($allow)
		{		
			$dsample=new Dsample();
			$em = $this->getDoctrine()->getManager();
			$form = $this->createForm(DsampleType::class, $dsample, array('entity_manager' => $em,));
			if ($request->isMethod('POST')) 
			{
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) 
				{
					try 
					{	
						
						$em->persist($dsample);
						/*$dlinksamples=$this->handle_many_to_many_relation_general(
							$request,
							$dsample,
							"sample_detail_dsammineral_id_mineral_",
							Dsample::class,
							"pk",
							Dsamminerals::class,
							Array("getIdmineral"=>"setIdmineral"),
							Array("sample_detail_dsammineral_grade_"=>"setGrade"),
							Array("setIdcollectionobj"=>$dsample)
						);
						foreach($dlinksamples as $obj)
						{
							$em->persist($obj);
						}*/
						
						$em->flush();
						return $this->redirectToRoute('app_edit_mineralgs', array('pk' => $dsample->getPk()));
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
			
			
			
			return $this->render('@App/dsample/dsampleform.html.twig', array(
					'form' => $form->createView(),
					'dsample' => $dsample,
					'originaction'=>'add_beforecommit',
					'view_detail'=>true,
					'read_mode'=>"create",
					"action"=>"add",
					"ranks"=> [""=> "unspecified", "mineral"=>"mineral", "class"=> "class", "groupe"=>"group"],
					"hierarchies"=>$this->get_hierarchy_mineral()					
					
				)
			);
			
		}
		else
		{
			return $this->render('@App/collnoaccess.html.twig');
		}
	}
	
	public function editsamplegsAction(Dsample $dsample, Request $request)
	{  
	
		$this->set_sql_session();
		if (!$dsample) {
			throw $this->createNotFoundException('No location found' );
		}
		else
		{
			$em = $this->getDoctrine()->getManager();
			$this->set_sql_session();
			$idcol = $dsample->getIdcollection();
			$idsample = $dsample->getIdsample();	

			$form = $this->createForm(DsampleType::class, $dsample, array('entity_manager' => $em,));
			if ($request->isMethod('POST')) 
			{
				$form->handleRequest($request);

				if ($form->isSubmitted() && $form->isValid()) 
				{
					try 
					{	
					
						//minerals
						$dlinksamples=$this->handle_many_to_many_relation_general(
							$request,
							$dsample,
							"sample_detail_dsammineral_id_mineral_",
							Lminerals::class,
							"pk",
							Dsamminerals::class,
							Array("getIdmineral"=>"setIdmineral"),
							Array(
							"sample_detail_dsammineral_pk_"=>"setPk",
							"sample_detail_dsammineral_id_grade_"=>"setGrade"),
							Array("setIdcollectionobj"=>$dsample)
						);
						
						if($dlinksamples!==null)
						{
							$dsample->initNewDsamminerals($em, $dlinksamples);
						}
						
						//localities (via loc litho)
						$dlinklocsam=$this->handle_many_to_many_relation_general(
							$request,
							$dsample,
							"sample_detail_dlinklocsam_dloc_litho_pk_",
							Dloclitho::class,
							"pk",
							Dlinklocsam::class,
							Array("getIdcollection"=>"setIdcollecsample", "getIdsample"=>"setIdsample"),
							Array(
							"sample_detail_dlinklocsam_pk_"=>"setPk",
							"sample_detail_dlinklocsam_id_pt_"=>"setIdpt",
							"sample_detail_dlinklocsam_id_collectionloc_"=>"setIdcollectionloc",
							"sample_detail_dlinklocsam_id_stratum_"=>"setIdstratum",
							),
							Array("setIdcollecsampleobj"=>$dsample)
						);
						//
						$granulo_http=$this->get_request_var($request,"sample_detail_granulo_pk",null);
						if($granulo_http!==null)
						{
							$granulo=$this->getHTTPOneToManyByIndex($request, "sample_detail_granulo_pk",
								Dsamgranulo::class,
								[
									"sample_detail_granulo_pk"=>"setPk",
									"sample_detail_granulo_weight_tot"=>"setWeighttot",
									"sample_detail_granulo_weight_sand"=>"setWeightsand",
									"sample_detail_granulo_weight_ab_2000"=>"setWAbove2000",
									"sample_detail_granulo_weight_2000"=>"setW2000",
									"sample_detail_granulo_weight_1400"=>"setW1400",
									"sample_detail_granulo_weight_1000"=>"setW1000",
									"sample_detail_granulo_weight_710"=>"setW710",
									"sample_detail_granulo_weight_500"=>"setW500",
									"sample_detail_granulo_weight_355"=>"setW355",
									"sample_detail_granulo_weight_250"=>"setW250",
									"sample_detail_granulo_weight_180"=>"setW180",
									"sample_detail_granulo_weight_125"=>"setW125",
									"sample_detail_granulo_weight_90"=>"setW90",
									"sample_detail_granulo_weight_63"=>"setW63",
									"sample_detail_granulo_description"=>"setDescription",
									"sample_detail_granulo_pc"=>"setPc",
									"sample_detail_granulo_pccum"=>"setPccum",
									"sample_detail_granulo_date"=>"setDate"	,
																	
								],
								["setIdcollectionobj"=>$dsample]
								);	
								$dsample->initNewDsamgranulo($em, $granulo);
						}
						//heavy mins
						$heavymin_http=$this->get_request_var($request,"sample_detail_heavymin_pk",null);
						if($heavymin_http!==null)
						{
							$heavymin=$this->getHTTPOneToManyByIndex($request, "sample_detail_heavymin_pk",
								Dsamgranulo::class,
								[
									"sample_detail_heavymin_pk"=>"setPk",
									"sample_detail_heavymin_weight_hm"=>"setWeighthm",
									"sample_detail_heavymin_weight_sample"=>"setWeightsample","sample_detail_heavymin_observation_hm"=>"setObservationhm",																
								],
								["setIdcollectionobj"=>$dsample]
								);	
								$dsample->initNewDsamheavymin($em, $heavymin);
						}
						// a-rays
						$samaray_http=$this->get_request_var($request,"sample_detail_aray_pk",null);
						if($samaray_http!==null)
						{
							$samaray=$this->getHTTPOneToManyByIndex($request, "sample_detail_aray_pk",
								Dsamgranulo::class,
								[
									"sample_detail_aray_pk"=>"setPk",
									"sample_detail_aray_alpharay"=>"setAlpharay",
									"sample_detail_aray_betaray"=>"setBetaray",
									"sample_detail_aray_gammaray"=>"setGammaray",
									"sample_detail_aray_xray"=>"setXray",																
								],
								["setIdcollectionobj"=>$dsample]
								);	
								$dsample->initNewDsamarays($em, $samaray);
						}
						//plate
						$plate_http=$this->get_request_var($request,"sample_detail_slimplate_pk",null);
						if($samaray_http!==null)
						{
							$slimplates=$this->getHTTPOneToManyByIndex($request, "sample_detail_slimplate_pk",
								Dsamgranulo::class,
								[
									"sample_detail_slimplate_pk"=>"setPk",
									"sample_detail_slimplate_num_plate"=>"setNumplate",
									"sample_detail_slimplate_plate_description"=>"setPlatedescription",
									"sample_detail_slimplate_plate_variousinfo"=>"setPlatevariousinfo",																
								],
								["setIdcollectionobj"=>$dsample]
								);	
								$dsample->initNewDsamslimplate($em, $slimplates);
						}
						//contribution
						$dlinkcontribute=$this->handle_many_to_many_relation_general(
						$request,
						$dsample,
						"sample_to_contrib_id_contrib_",
						Dcontribution::class,
						"pk",
						Dlinkcontsam::class,
						Array("getIdcontribution"=>"setIdcontribution"),
						Array(),
						array("setIdcollectionobj"=>$dsample)
						);
						if($dlinkcontribute!==null)
						{
							if(count($dlinkcontribute)>0)
							{
								
								$dsample->initNewDLinkcontloc($em, $dlinkcontribute);
									//reattach after update
									
								//throw new UndefinedOptionsException();
							}
						}
						//doc
						$dlinkdocument=$this->handle_many_to_many_relation_general(
						$request,
						$dsample,
						"point_to_doc_id_doc_",
						Ddocument::class,
						"pk",
						Dlinkdocsam::class,
						Array("getIdcollection"=>"setIdcollectionsample", "getIdsample"=>"setIdsample"),
						Array(),
						array("setIdcollectionsampleobj"=>$dsample)
						);
						if($dlinkdocument!==null)
						{
							if(count($dlinkdocument)>0)
							{
								
								$dsample->initNewDlinkdocsam($em, $dlinkdocument);
									//reattach after update
									
								//throw new UndefinedOptionsException();
							}
						}
						$em->flush();
						$this->addFlash('success','DATA RECORDED IN DATABASE!');   
					}
					catch(\Doctrine\DBAL\DBALException $e ) {
						$form->addError(new FormError($e->getMessage()));
					}
                    catch(\Doctrine\DBAL\DBALException\UniqueConstraintViolationException $e ) {
						$form->addError(new FormError($e->getMessage()));
					}
                    catch(\Doctrine\DBAL\DBALException\ConstraintViolationException $e ) {
						$form->addError(new FormError($e->getMessage()));
					}
					catch(Exception $e ) {
						$form->addError(new FormError($e->getMessage()));
					}
				}
			}
			$rightoncollection = $this->container->get('AppBundle\Controller\SampleController')->getusercoll_right('4,15,26',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
			
			$col_obj_key=$target_obj=$this->getDoctrine()
					->getRepository(Codecollection::class)
						->findOneBy(array("codecollection" => $idcol));
			$pk_coll=$col_obj_key->getPk();
			
			$view_detail=$this->container->get('AppBundle\Controller\SampleController')->getusercoll_right($pk_coll,['Curator','Collection_manager']);
			
						
			if ($rightoncollection == true)
			{
				$logs=$em->getRepository(TDataLog::class)
						 ->findContributions("dsample", $dsample->getPk());
				
				$current_tab=$this->get_request_var($request, "current_tab");
				$search_form=$this->createForm(SearchAllForm::class, null);	
				return $this->render("@App/dsample/dsampleform.html.twig", array(
						'dsample' => $dsample,
						'form' => $form->createView(),
						"dsamminerals"=> $dsample->initDsamminerals($em),
						"dlinklocsam"=> $dsample->initDlinklocsam($em),
						"dsamgranulo"=> $dsample->initDsamgranulo($em),
						"dsamheavymin"=> $dsample->initDsamheavymin($em),
						"dsamarays"=> $dsample->initDsamarays($em),
						"dsamslimplate"=> $dsample->initDsamslimplate($em),
						'dlinkcontsam' => $dsample->initDLinkcontsam($em),
						'dlinkdocsam' => $dsample->initDlinkdocsam($em),
						'origin'=>'edit',
						'originaction'=>'edit',
						'current_tab'=>$current_tab,
						'logs'=>$logs,
						'view_detail'=>$view_detail,
						'read_mode'=>$this->enable_read_write($request),
						"action"=>"edit",
						"ranks"=> [""=> "unspecified", "mineral"=>"mineral", "class"=> "class", "groupe"=>"group"] ,
						"hierarchies"=>$this->get_hierarchy_mineral(),
						'search_form'=>$search_form->createView(),
				
				));
			}
			else
			{
				return $this->render('@App/collnoaccess.html.twig');
			}
		}
	}
	
	/*public function searchsampleAction(Request $request)
	{
	
		$RAW_QUERY = "SELECT * FROM lminerals_hierarchy ORDER BY pk";
		$em = $this->getDoctrine()->getManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->execute();
		$data = $statement->fetchAll();
		
		$collections=$this->get_sample_collections();
		
		return $this->render('@App/dsample/dsample_search.html.twig', array("hierarchies"=>$data , "collections"=>$collections));  
	}*/
	
	public function searchsampleAction(Request $request)
	{
		return $this->render('@App/search_all/searchstratumgs.html.twig');
	}
	
	
	public function main_searchAction(Request $request)
    {
		$display_csv=false;
		if(strtolower($request->get("csv","false"))=="true")
		{
			$display_csv=true;
		}
		$current_page=$request->get("current_page",1);
        $page_size=$request->get("page_size",$this->page_size);
		$offset=(((int)$current_page)-1)* (int)$page_size;
		
		$idminerals_tmp=$request->get("min",[]);
		
		$idminerals=[];
		foreach($idminerals_tmp as $val)
		{
			$tmp=explode(',',$val);
			
			if(array_reduce($tmp, function ($result, $item) { return $result && is_numeric($item); }, true))
			{
				$idminerals=array_merge($idminerals, $tmp);
			}
		}
		
		
		$hierarchy=$request->get("hierarchy","");
		$has_child=$request->get("child_min","false");
		
		$idsample=$request->get("idsample",[]);
		$collection=$request->get("collection","");
		
		$idx_param=1;
		
		$params_sql=[];
		$params_sql_or_group=[];
	
		
		$params_pdo=Array();
		 $RAW_QUERY="SELECT *, count(*) OVER() AS full_count FROM v_dsample_to_min a ";
		if(count($idminerals)>0)
		{
			
			if(trim($has_child)=="true")
			{
				$idminerals=array_map(function($value) { return '/'.$value."/"; },$idminerals );
				$list_pattern=implode("|", $idminerals);
				$sql_tmp="EXISTS (SELECT b.pk FROM dsamminerals b WHERE hierarch_min_desc ~* ".$this->gen_pdo_name($idx_param)." AND a.idcollection=b.idcollection AND a.idsample=b.idsample  )";		
				//$params_sql_or_group[]=$sql_tmp;
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$list_pattern;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;
				$idx_param++;
				$params_sql_or_group[]=$sql_tmp;
			}
			else
			{
				$sql_tmp="EXISTS (SELECT b.pk FROM dsamminerals b WHERE idmineral =ANY ('{".implode(",",$idminerals)."}') AND a.idcollection=b.idcollection AND a.idsample=b.idsample  )";		
				$params_sql_or_group[]=$sql_tmp;
			}
			
		 }
		
		if($collection!="")
		{
			$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$collection;
			$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;	
			$params_sql[] = " idcollection =" .$this->gen_pdo_name($idx_param);
			$idx_param++;
		}
		
		if(count($idsample)>0)
		{
			$sql_elems=[];
			foreach ($idsample as $value) 
			{
				$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$value;
				$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_STR;	
				$sql_elems[] = " idsample =" .$this->gen_pdo_name($idx_param);
				$idx_param++;
			}
			
			$sql_tmp=implode(" OR ", $sql_elems);
			$params_sql_or_group[]=$sql_tmp;
		}
		foreach($params_sql_or_group as $key=>$params_sql_or)
		{
			$params_sql[]="(".implode(" OR ", $params_sql_or_group ).")";
		}
			
		if(count($params_sql)>0)
		{
				$query_where=" WHERE ".implode(" AND ", $params_sql );				
		}
		else
		{
				$query_where="";				
		}
		$RAW_QUERY.=$query_where;
		$query_where=" ORDER BY idcollection, idsample, pk ";	
		if(!$display_csv)
		{
				$RAW_QUERY.=' OFFSET :offset LIMIT :limit';
		}
		$RAW_QUERY.=';';
		
		$em = $this->getDoctrine()->getManager();	
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		
		
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
		$results=[];		
		if(!$display_csv)
		{
			$results = $statement->fetchAll();
			if(count($results)>0)
			{
				$count_all=$results[0]["full_count"];
				$pagination = array(
					'page' => $current_page,
					'route' => 'search_main',
					'pages_count' => ceil($count_all / $page_size)
				);
				return $this->render('@App/dsample/search_results_dsample.html.twig', array("results"=>$results, "pagination"=>$pagination, "page_size"=>$page_size, "nb_results"=>$count_all));
			}
		}
		return $this->render('@App/no_results_bs.html.twig');
	}
	
}
	
	