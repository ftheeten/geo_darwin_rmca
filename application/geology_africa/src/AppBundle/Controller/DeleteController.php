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
use AppBundle\Entity\Ddocsatellite;
use AppBundle\Entity\Dsamheavymin;
use AppBundle\Entity\Dsamheavymin2;
use AppBundle\Entity\Dcontribution;
use AppBundle\Entity\Dcontributor;
use AppBundle\Entity\Dlinkcontribute;
use AppBundle\Entity\Dlinkcontsam;
use AppBundle\Entity\Dlinkcontloc;
use AppBundle\Entity\Dlinkcontdoc;
use AppBundle\Entity\Dlocdrilling; 
use AppBundle\Entity\DLoclitho;
use AppBundle\Entity\Dlocstratumdesc;
use AppBundle\Entity\Dkeyword;

class DeleteController extends AbstractController
{
	public function deletecontributorAction(Dcontributor $dcontributor, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(DcontributorType::class, $dcontributor, array('entity_manager' => $em,));		
		return $this->delete_general($dcontributor, $form, DcontributorType::class, $request, '@App/addcontributor.html.twig', 'dcontributor' );
	}
	/*
	public function deletecontributionAction(Dcontribution $dcontribution, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(DcontributionType::class, $dcontribution, array('entity_manager' => $em,));		
		return $this->delete_general($dcontribution, $form, DcontributionType::class, $request, '@App/addcontribution.html.twig', 'dcontribution' );
	}*/
	
	protected function delete_fk_general($obj_to_delete, $call_back_template, $pk_template,$options_template=Array())
	{
		$em = $this->getDoctrine()->getManager();
		$em->remove($obj_to_delete);
		$em->flush();
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
	
	public function deleteddocsatellite(Ddocsatellite $ddocsatellite, Request $request)
	{			
		$container_pk=$request->get("container_pk");		
		return $this->delete_fk_general($ddocsatellite, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"satellite-tab"));
	}
	
	public function deletedlinkcontdocAction(Dlinkcontdoc $dlinkcontdoc, Request $request)
	{			
		
		$container_pk=$request->get("container_pk");
		$table=$this->get_request_var($request, "table", "ddocument");
		if($table=="ddocument")
		{
			
			return $this->delete_fk_general($dlinkcontdoc, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"contributions-tab"));
		}
		else if($table=="dcontribution")
		{
			
			return $this->delete_fk_general($dlinkcontdoc, 'app_edit_contribution',$container_pk, array('pk' => $container_pk,"current_tab"=>"document-tab"));
		}		
	}
	
	public function deletedddocsatelliteAction(Ddocsatellite $dlinksat, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dlinksat, 'app_edit_doc',$container_pk, array('pk' => $container_pk,"current_tab"=>"satellite-tab"));
	}
	
	public function deletestratum_from_pointAction(DLoclitho $dloclitho, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dloclitho, 'app_edit_point',$container_pk, array('pk' => $container_pk,"current_tab"=>"stratum"));
	}
	
	public function deletestratum_descriptionAction(Dlocstratumdesc $dlocstratumdesc, Request $request)
	{
		$container_pk=$request->get("container_pk");
		return $this->delete_fk_general($dlocstratumdesc, 'app_edit_point',$container_pk, array('pk' => $container_pk,"current_tab"=>"stratum"));
	}
	

}