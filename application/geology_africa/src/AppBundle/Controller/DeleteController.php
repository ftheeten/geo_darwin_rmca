<?php

// src/AppBundle/Controller/AutocompleteController.php

namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;

use AppBundle\Entity\Codecollection;
use AppBundle\Entity\Dsample;
use AppBundle\Entity\Dsamminerals;
use AppBundle\Entity\Dsammagsusc;
use AppBundle\Entity\Dsamgranulo;
use AppBundle\Entity\Lminerals;
use AppBundle\Entity\LmineralsHierarchy;
use AppBundle\Entity\LPrecision;
use AppBundle\Entity\LMedium;
use AppBundle\Entity\DLoccenter;
use AppBundle\Entity\Ddocument;
use AppBundle\Entity\Ddoctitle;
use AppBundle\Entity\Ddocsatellite;
use AppBundle\Entity\Ddocmap;
use AppBundle\Entity\Ddocarchive;
use AppBundle\Entity\Ddocscale;
use AppBundle\Entity\Ddocfilm;
use AppBundle\Entity\Ddocaerphoto;
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
use AppBundle\Entity\Dlocstratumdesc;
use AppBundle\Entity\Dkeyword;
use AppBundle\Entity\Docplanvol;
use AppBundle\Entity\Dlinklocsam;
use AppBundle\Entity\Dlinkdocsam;


use AppBundle\Form\DsampleType;



use AppBundle\Form\LmineralsType;
use AppBundle\Form\LmineralsHierarchyType;



class DeleteController extends AbstractController
{
	
	
	public function deletecontributionAction(Dcontribution $dcontribution, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(DcontributionType::class, $dcontribution, array('entity_manager' => $em,));		
		return $this->delete_general($dcontribution, $form, DcontributionType::class, $request, '@App/addcontribution.html.twig', 'dcontribution' );
	}
	
	protected function delete_fk_general($obj_to_delete, $call_back_template, $pk_template,$options_template=Array(), $form=null, $redirect_template=null, $error_template_options=[])
	{
		try {
			$em = $this->getDoctrine()->getManager();
			$em->remove($obj_to_delete);
			$em->flush();
		} catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
			if($form!==null)
			{
				
				$form->addError(new FormError($e->getMessage()));
				
			}
			$error_template_options["form"]=$form->createView();
			return $this->render($redirect_template, $error_template_options	);
		} catch(\Doctrine\DBAL\Exception\ConstraintViolationException $e ) {
			if($form!==null)
			{
				
				$form->addError(new FormError($e->getMessage()));
			}
			$error_template_options["form"]=$form->createView();
			return $this->render($redirect_template, $error_template_options	);
		} catch(\Doctrine\DBAL\Exception\TableNotFoundException $e ) {
			if($form!==null)
			{
				$form->addError(new FormError($e->getMessage()));
			}
			$error_template_options["form"]=$form->createView();
			return $this->render($redirect_template, $error_template_options	);
		} catch(\Doctrine\DBAL\Exception\ConnectionException $e ) {
			if($form!==null)
			{
				$form->addError(new FormError($e->getMessage()));
			}
			$error_template_options["form"]=$form->createView();
			return $this->render($redirect_template, $error_template_options	);
		} catch(\Doctrine\DBAL\Exception\DriverException $e ) {
			if($form!==null)
			{
				$form->addError(new FormError($e->getMessage()));
			}
			$error_template_options["form"]=$form->createView();
			return $this->render($redirect_template, $error_template_options	);
		}
		
		 return $this->redirectToRoute($call_back_template, $options_template);
		
	}
	
	public function deletedkeywordAction(Dkeyword $dkeyword, Request $request)
	{			
		$container_pk=$request->get("container_pk");		
		return $this->delete_fk_general($dkeyword, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"keywords-tab"));
	}
	
	public function deleteddoctitleAction(Ddoctitle $ddoctitle, Request $request)
	{			
		$container_pk=$request->get("container_pk");		
		return $this->delete_fk_general($ddoctitle, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"keywords-tab"));
	}
	
	
	
    public function deleteddocscaleAction(Ddocscale $ddocscale, Request $request)
	{			
		$container_pk=$request->get("container_pk");		
		return $this->delete_fk_general($ddocscale, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"scale-tab"));
	}
	
	 public function deleteddocfilmAction(Ddocfilm $ddocfilm, Request $request)
	{			
		$container_pk=$request->get("container_pk");		
		return $this->delete_fk_general($ddocfilm, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"film-tab"));
	}
	
	
	
	public function deletedlinkcontdocAction(Dlinkcontdoc $dlinkcontdoc, Request $request)
	{			
		
		$container_pk=$request->get("container_pk");		
			
		return $this->delete_fk_general($dlinkcontdoc, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"contributions-tab"));
			
	}
	               
    public function deletedlinkdoclocAction(Dlinkdocloc $dlinkdocloc, Request $request)
	{			
		
		$container_pk=$request->get("container_pk");		
			
		return $this->delete_fk_general($dlinkdocloc, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"location-tab"));
			
	}
	
	  public function deletedlinkdoclocfrompointAction(Dlinkdocloc $dlinkdocloc, Request $request)
	{			
		
		$container_pk=$request->get("container_pk");		
			
		return $this->delete_fk_general($dlinkdocloc, 'app_edit_point',$container_pk, array('pk' => $container_pk,"current_tab"=>"documentpoint-tab"));
			
	}
	
	
	public function deletedlinkcontributionAction(Dlinkcontribute $dlinkcontribute, Request $request)
	{			
		
		$container_pk=$request->get("container_pk");
		
				
		return $this->delete_fk_general($dlinkcontribute, 'app_edit_contribution',$container_pk, array('pk' => $container_pk,"current_tab"=>"document-tab"));
				
	}
	
	
	public function deleteddocsatelliteAction(Ddocsatellite $dlinksat, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dlinksat, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"satellite-tab"));
	}
	
	public function deletedddocmapAction(Ddocmap $ddocmap, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($ddocmap, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"map-tab"));
	}
	
    public function deletedddocarchiveAction(Ddocarchive $ddocarchive, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($ddocarchive, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"archive-tab"));
	}
	
	
	
	
	
	public function deletestratum_from_pointAction(DLoclitho $dloclitho, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dloclitho, 'app_edit_point',$container_pk, array('pk' => $container_pk,"current_tab"=>"mainpoint"));
	}
	
	public function deletestratum_descriptionAction(Dlocstratumdesc $dlocstratumdesc, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dlocstratumdesc, 'app_edit_point',$container_pk, array('pk' => $container_pk,"current_tab"=>"mainpoint-tab"));
	}
	
	public function deletecontributorAction(Dcontributor $dcontributor, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(DcontributorType::class, $dcontributor, array('entity_manager' => $em,));		
		return $this->delete_general($dcontributor, $form, DcontributorType::class, $request, '@App/addcontributor.html.twig', 'dcontributor' );
	}
	
	public function deleteflightplanAction(Docplanvol $ddocplanvol, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(DocplanvolType::class, $ddocplanvol, array('entity_manager' => $em,));		
		return $this->delete_general($ddocplanvol, $form, DocplanvolType::class, $request, '@App/addplanvol.html.twig', 'docplanvol' );
	}
	
	public function deleteddocaerphotoAction(Ddocaerphoto $ddocaerphoto, Request $request)
	{
		$container_pk=$request->get("container_pk");
		
				
		return $this->delete_fk_general($ddocaerphoto, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"flightplan-tab"));
	}
	
	public function delete_mineralAction(Lminerals $lmineral, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		/*$em->remove($lmineral);
		$em->flush();
		return $this->redirectToRoute('app_search_mineralgs', Array());*/
		$form = $this->createForm(LmineralsType::class, $lmineral, array('entity_manager' => $em,));
		 $error_template_options=[
			'lmineral'=>$lmineral,
			'originaction'=>'add_beforecommit',
			'read_mode'=>"create",
			'ranks'=>[""=> "unspecified", "mineral"=>"mineral", "class"=> "class", "groupe"=>"group"]
		 ];
		return $this->delete_fk_general($lmineral, 'app_search_mineralgs', null, [],$form, '@App/lminerals/mineralform.html.twig',  $error_template_options );
	}
	
		public function delete_mineralhierarchyAction(LmineralsHierarchy $lmineralhierarchy, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		/*$em->remove($lmineral);
		$em->flush();
		return $this->redirectToRoute('app_search_mineralgs', Array());*/
		$form = $this->createForm(LmineralsHierarchyType::class, $lmineralhierarchy, array());
		 $error_template_options=[
			'lmineralhierarchy'=>$lmineralhierarchy,
			'originaction'=>'add_beforecommit',
			'read_mode'=>"create",
			
		 ];
		return $this->delete_fk_general($lmineralhierarchy, 'app_search_mineralhierarchiesgs', null, [],$form, '@App/lminerals/mineralhierarchyform.html.twig',  $error_template_options );
	}
	
	public function deletesampleAction(Dsample $dsample, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(DsampleType::class, $dsample, array('entity_manager' => $em,));		
		return $this->delete_general($dsample, $form, DsampleType::class, $request, '@App/dsample/dsampleform.html.twig', 'dsample' );
	}
	
	
	public function delete_mineral_from_sampleAction(Dsamminerals $dsamminerals, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dsamminerals, 'app_edit_samplegs',$container_pk, array('pk' => $container_pk,"current_tab"=>"mainsample-tab"));
	}
	
	public function delete_granulo_from_sampleAction(Dsamgranulo $dsamgranulo, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dsamgranulo, 'app_edit_samplegs',$container_pk, array('pk' => $container_pk,"current_tab"=>"mainsample-tab"));
	}
	
	
    public function delete_heavymin_from_sampleAction(Dsamheavymin $dsamheavymin, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dsamheavymin, 'app_edit_samplegs',$container_pk, array('pk' => $container_pk,"current_tab"=>"mainsample-tab"));
	}
	
    public function delete_aray_from_sampleAction(Dsamarays $dsamarays, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dsamarays, 'app_edit_samplegs',$container_pk, array('pk' => $container_pk,"current_tab"=>"mainsample-tab"));
	}
	
	public function delete_slimplate_from_sampleAction(Dsamslimplate $dsamslimplate, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dsamslimplate, 'app_edit_samplegs',$container_pk, array('pk' => $container_pk,"current_tab"=>"mainsample-tab"));
	}
	
    public function delete_contrib_from_pointAction(Dlinkcontloc $dlinkcontloc, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dlinkcontloc, 'app_edit_point',$container_pk, array('pk' => $container_pk,"current_tab"=>"mainpoint-tab"));
	}


}