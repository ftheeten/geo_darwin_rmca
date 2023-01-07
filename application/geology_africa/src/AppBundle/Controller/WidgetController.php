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
use AppBundle\Entity\Dsamgranulo;
use AppBundle\Entity\Dsammagsusc;
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
use AppBundle\Entity\Dsamarays;
use AppBundle\Entity\Dsamslimplate;
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
use AppBundle\Entity\Dlinklocsam;
use AppBundle\Entity\Dlinkdocsam;
use AppBundle\Form\SearchAllForm;



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
	
    public function widget_point_to_docAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","point_to_doc");		
		//$form=$this->createForm(SearchAllForm::class, null);		
		return $this->render('@App/dloccenter/detail_point_to_document.html.twig', array("index"=>$index,
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));		
	}
	
	public function widget_point_to_contribAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","point_to_contrib");		
		//$form=$this->createForm(SearchAllForm::class, null);		
		return $this->render('@App/dloccenter/detail_point_to_contribution.html.twig', array("index"=>$index,
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
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));	
	}
	
	public function widget_sample_to_mineralAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","sample_detail_dsammineral");
		return $this->render('@App/dsample/dsample_detail_mineral.html.twig', array("index"=>$index, 
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));			
		
	}
	
	public function widget_sample_to_granuloAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","sample_detail_granulo");
		return $this->render('@App/dsample/dsample_detail_granulo.html.twig', array("index"=>$index, 
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));			
		
	}
	
	public function widget_sample_to_heavyminAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","sample_detail_heavymin");
		return $this->render('@App/dsample/dsample_detail_heavymin.html.twig', array("index"=>$index, 
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));			
		
	}
	
    public function widget_sample_to_arayAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","sample_detail_aray");
		return $this->render('@App/dsample/dsample_detail_aray.html.twig', array("index"=>$index, 
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));			
		
	}
	
	public function widget_sample_to_slimplateAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","sample_detail_slimplate");
		return $this->render('@App/dsample/dsample_detail_slimplate.html.twig', array("index"=>$index, 
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));			
		
	}
	
	public function widget_sample_to_locAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","sample_detail_dlinklocsam");
		$search_form=$this->createForm(SearchAllForm::class, null);	
		return $this->render('@App/dsample/dsample_detail_loc.html.twig',
			array(
				"index"=>$index, 
				"ctrl_prefix"=>$ctrl_prefix, 
				'read_mode'=>$this->enable_read_write($request, "write"),
				'search_form'=>$search_form->createView(),
				));			
		
	}
	
	public function widget_sample_to_contribAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","sample_detail_dlinkcontsam");
		return $this->render('@App/dsample/dsample_detail_contrib.html.twig', array("index"=>$index, 
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write")));			
		
	}
	
	public function widget_sample_to_docAction(Request $request)
	{
		$index=$request->get("index","1");
		$ctrl_prefix=$request->get("ctrl_prefix","sample_detail_dlinkdocsam");
		$search_form=$this->createForm(SearchAllForm::class, null);	
		return $this->render('@App/dsample/dsample_detail_doc.html.twig', array("index"=>$index, 
			"ctrl_prefix"=>$ctrl_prefix, 'read_mode'=>$this->enable_read_write($request, "write"),
			'search_form'=>$search_form->createView(),));			
		
	}
	
	

}