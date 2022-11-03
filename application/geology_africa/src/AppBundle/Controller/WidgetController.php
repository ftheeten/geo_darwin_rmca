<?php

// src/AppBundle/Controller/AutocompleteController.php

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
use AppBundle\Entity\Ddocscale;
use AppBundle\Entity\Ddocfilm;
use AppBundle\Entity\Ddocsatellite;
use AppBundle\Entity\Ddocmap;
use AppBundle\Entity\Ddocarchive;
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


class WidgetController extends AbstractController
{

	public function widget_satelliteAction(Request $request)
	{
		$index=$request->get("index","1");
		$default_val=$request->query->get("default_val","");
		$ctrl_prefix=$request->get("ctrl_prefix","sat_");
		$target_obj=NULL;
		if($default_val!=="")
		{
			$target_obj=$this->getDoctrine()
			->getRepository(Ddocsatellite::class)
			 ->findOneBy(array("pk" => $default_val));
		}
		return $this->render('@App/foreignkeys/satellite.html.twig', array("index"=>$index, "default_val"=>$target_obj, "ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));
	}
	
	public function widget_mapAction(Request $request)
	{
		$index=$request->get("index","1");
		$default_val=$request->query->get("default_val","");
		$ctrl_prefix=$request->get("ctrl_map","map_");
		$target_obj=NULL;
		if($default_val!=="")
		{
			$target_obj=$this->getDoctrine()
			->getRepository(Ddocmap::class)
			 ->findOneBy(array("pk" => $default_val));
		}
		return $this->render('@App/documents/map_detail.html.twig', array("index"=>$index, "default_val"=>$target_obj, "ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));
	}
	
	public function widget_archiveAction(Request $request)
	{
		$index=$request->get("index","1");
		$default_val=$request->query->get("default_val","");
		$ctrl_prefix=$request->get("ctrl_map","archive_");
		$target_obj=NULL;
		if($default_val!=="")
		{
			$target_obj=$this->getDoctrine()
			->getRepository(Ddocarchive::class)
			 ->findOneBy(array("pk" => $default_val));
		}
		return $this->render('@App/documents/archive_detail.html.twig', array("index"=>$index, "default_val"=>$target_obj, "ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));
	}
	
	
	
	public function widget_scaleAction(Request $request)
	{
		$index=$request->get("index","1");
		$default_val=$request->query->get("default_val","");
		$ctrl_prefix=$request->get("ctrl_prefix","scale_");
		$target_obj=NULL;
		if($default_val!=="")
		{
			$target_obj=$this->getDoctrine()
			->getRepository(Ddocscale::class)
			 ->findOneBy(array("pk" => $default_val));
		}
		return $this->render('@App/documents/scale_detail.html.twig', array("index"=>$index, "default_val"=>$target_obj, "ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));
	}
	
	public function widget_filmAction(Request $request)
	{
		$index=$request->get("index","1");
		$default_val=$request->query->get("default_val","");
		$ctrl_prefix=$request->get("ctrl_prefix","film_");
		$target_obj=NULL;
		if($default_val!=="")
		{
			$target_obj=$this->getDoctrine()
			->getRepository(Ddocfilm::class)
			 ->findOneBy(array("pk" => $default_val));
		}
		return $this->render('@App/documents/film_detail.html.twig', array("index"=>$index, "default_val"=>$target_obj, "ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));
	}
	
	
	public function widget_contributordetailAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","contribdetail");
		$default_val=$request->query->get("default_val","");
		$target_obj=NULL;
		print("TEST");
		if($default_val!=="")
		{
			$target_obj=$this->getDoctrine()
			->getRepository(Dlinkcontribute::class)
			 ->findOneBy(array("pk" => $default_val));
		}
		
		
		return $this->render('@App/contributions/detail_contribution_to_contributor.html.twig', array("index"=>$index, 
			"default_val"=>$target_obj,
			"ctrl_prefix"=>$ctrl_prefix,
			'read_mode'=>$this->enable_read_write($request, "write")));	
	}
	
	public function widget_keywordAction(Request $request)
	{
		$index=$request->get("index","1");
		$default_val=$request->query->get("default_val","");
		if($default_val!=="")
		{
			$target_obj=$this->getDoctrine()
			->getRepository(Dkeyword::class)
			 ->findOneBy(array("pk" => $default_val));		
		}
		else
		{
			$target_obj=new Ddoctitle();
		}		
		return $this->render('@App/foreignkeys/dkeyword.html.twig', array("index"=>$index, "default_val"=>$target_obj, 'read_mode'=>$this->enable_read_write($request, "write")));
	}
	
	public function widget_titleAction(Request $request)
	{
		$index=$request->get("index","1");		
		$default_val=$request->query->get("default_val","");
		if($default_val!=="")
		{
			$target_obj=$this->getDoctrine()
			->getRepository(Ddoctitle::class)
			 ->findOneBy(array("pk" => $default_val));		
		}
		else
		{
			$target_obj=new Ddoctitle();
		}
		return $this->render('@App/foreignkeys/ddoctitle.html.twig', array("index"=>$index, "default_val"=>$target_obj, 'read_mode'=>$this->enable_read_write($request, "write")));
	}
	
		
	public function widget_contrib_to_docAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","contrib_to_doc");		
		//$form=$this->createForm(SearchAllForm::class, null);		
		return $this->render('@App/contributions/detail_contribution_to_doc.html.twig', array("index"=>$index, 
			//"default_val"=>$default_val,
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));		
	}
	
	public function widget_point_to_stratumAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","point_to_stratum");		
		//$form=$this->createForm(SearchAllForm::class, null);		
		return $this->render('@App/dloccenter/detail_point_to_stratum.html.twig', array("index"=>$index, 
			//"default_val"=>$default_val,
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));		
	}
	
	public function widget_point_to_stratum_descriptionAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","point_to_stratum_description");		
		//$form=$this->createForm(SearchAllForm::class, null);		
		return $this->render('@App/dloccenter/detail_point_to_stratum_description.html.twig', array("index"=>$index, 
			//"default_val"=>$default_val,
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));		
	}
	
	
	public function widget_doc_to_contribAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","doc_to_contrib");		
		//$form=$this->createForm(SearchAllForm::class, null);		
		return $this->render('@App/documents/detail_doc_to_contribution.html.twig', array("index"=>$index, 
			//"default_val"=>$default_val,
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));		
	}
	
	public function widget_doc_to_dloccenterAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","doc_to_loc");
		$default_val=$request->query->get("default_val","");
		$tmp_loclink=NULL;
		$tmp_localities=NULL;
		if(is_numeric($default_val))
		{
			$tmp_loclink=$this->getDoctrine()
			->getRepository(Dlinkdocloc::class)
			 ->findOneBy(array('pk' => $default_val));
			 if($tmp_loclink!==null)
			 {
				$tmp_localities=$this->getDoctrine()
				->getRepository(DLoccenter::class)
				 ->findOneBy(array('idpt' => $tmp_contriblink->getIdpt(), 'idcollection'=>$tmp_contriblink->getIdcollecloc()));
				$tmp_localities->setDescriptionDB($this->getDoctrine());
			}
		}
		return $this->render('@App/documents/detail_doc_to_dloccenter.html.twig', array("index"=>$index, 
			"default_val"=>$default_val,
			"ctrl_prefix"=>$ctrl_prefix, "default_val"=>$default_val, "Dlinkdocloc"=>$tmp_loclink, "DLoccenter"=>$tmp_localities, 'read_mode'=>$this->enable_read_write($request, "write")));		
	}
	
	public function widget_doc_to_flightplanAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","doc_to_flightplan");		
		//$form=$this->createForm(SearchAllForm::class, null);		
		return $this->render('@App/documents/flightplan_detail.html.twig', array("index"=>$index, 
			//"default_val"=>$default_val,
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));	
	}

}