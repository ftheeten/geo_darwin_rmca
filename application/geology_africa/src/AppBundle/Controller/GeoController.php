<?php

// src/AppBundle/Controller/GeoController.php

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
use AppBundle\Entity\Ddocaerphoto;
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
use AppBundle\Entity\Dlocstratumdesc;
use AppBundle\Entity\Dkeyword;

use AppBundle\Entity\Docplanvol;
use AppBundle\Entity\TDataLog;
use AppBundle\Form\DsampleType;
use AppBundle\Form\DsampleEditType;
use AppBundle\Form\LmineralsType;
use AppBundle\Form\LmineralsEditType;
use AppBundle\Form\DcontributionType;
use AppBundle\Form\DcontributorType;
use AppBundle\Form\DcontributionEditType;
use AppBundle\Form\DdocumentEditType;
use AppBundle\Form\DdocumentType;
use AppBundle\Form\PointType;
use AppBundle\Form\PointEditType;
use AppBundle\Form\StratumType;
use AppBundle\Form\CodecollectionEditType;
use AppBundle\Form\CollectionType;
use AppBundle\Form\DocplanvolType;
use AppBundle\Form\EntityManager;
use AppBundle\Form\SearchAllForm;


use Symfony\Component\Form\FormError;

//debug 
use Symfony\Component\HttpFoundation\Response;

class GeoController extends AbstractController
{	
	public function add_general($class_name, $form_class,Request $request,$redirect_add, $redirect_edit, $field_to_remove=null )
	{
		$collection_rights=$this->check_collection_rights_general();
		if ($collection_rights == true)
		{		
			$reflect = new \ReflectionClass($class_name);
			$object = $reflect->newInstance();  
			$em = $this->getDoctrine()->getManager();
			$form = $this->createForm($form_class, $object, array('entity_manager' => $em,));
			if($field_to_remove!==null)
			{
				
				$form->remove($field_to_remove);
			}
			if ($request->isMethod('POST')) 
			{
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
					try {
						$em->persist($object);
						$em->flush();
						return $this->redirectToRoute($redirect_edit, array("pk"=>$object->getPk() ));
						
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
			return $this->render($redirect_add, array(
				'form' => $form->createView(),
				'originaction'=>'add_beforecommit',
				'read_mode' => 'create'
			));
		}
		else
		{
			return $this->render('@App/collnoaccess.html.twig');
		}
	}
	
	
	
	public function checkRightsGeneral($controller_name='AppBundle\Controller\GeoController')
	{
		$rightoncollection = $this->container->get($controller_name)->getusercoll_right('1,2,3,7,9',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
		return $rightoncollection;
	}
	
	
	public function addsampleAction(Request $request){
		$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('6,12,13',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
		if ($rightoncollection == true){
			$dsample = new Dsample();
			$dcontribution = new Dcontribution();
			
			//$dsample->setIdsample(1000);
			$em = $this->getDoctrine()->getManager();

			/*$sammineral1 = new Dsamminerals();
			$sammineral1->setGrade(1);
			$dsample->getSamminerals()->add($sammineral1);*/
			
			$form = $this->createForm(DsampleType::class, $dsample, array('entity_manager' => $em,));
			$form2 = $this->createForm(DcontributionType::class, $dcontribution, array('entity_manager' => $em,));
			
			if ($request->isMethod('POST')) {
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
					try {
						$em->persist($dsample);
						$em->flush();
						$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
						return $this->redirectToRoute('app_edit_sample', array('pk' => $dsample->getPk()));
					} catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
						echo "<script type='text/javascript'>alert('Record already exists with these values of collection and ID !');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction already exist" );
					} catch(\Doctrine\DBAL\Exception\ConstraintViolationException $e ) {
						echo "<script type='text/javascript'>alert('There is a constraint violation with that transaction !');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Bad request on Transaction" );
					} catch(\Doctrine\DBAL\Exception\TableNotFoundException $e ) {
						echo "<script type='text/javascript'>alert('Table not found !');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction Table not found" );
					} catch(\Doctrine\DBAL\Exception\ConnectionException $e ) {
						echo "<script type='text/javascript'>alert('Problem of connection with database !');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction Table not found" );
					} catch(\Doctrine\DBAL\Exception\DriverException $e ) {
						echo "<script type='text/javascript'>alert('There is a syntax error with one field !');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction Table not found" );
					}
				}elseif ($form->isSubmitted() && !$form->isValid() ){
					echo "<script type='text/javascript'>alert('error in form');</script>";
				}
			}
			
			return $this->render('@App/addsample.html.twig', array(
					'form' => $form->createView(),
					'form2' => $form2->createView(),
				//	'Mineral_form' => $form2->createView(),
					'originaction'=>'add_beforecommit'
				));
		}else{
			return $this->render('@App/collnoaccess.html.twig');
		}
    }

	
	public function saveusers($idcoll,$actionsusersstr,$request){
		$actionscollusers[] = null;
		$idcollusers[] = null;
		$idusers[]= null;
		$origin_error = "In users, ";
		$em = $this->getDoctrine()->getManager();				
						
		if ($actionsusersstr != ""){
			//print_r($actionsusersstr);
			$arrayid_collids =  explode(",", $actionsusersstr); 
			$elem =array();
			$i=0;
			foreach($arrayid_collids as $e) {
				if ($e != ""){
					$elem = explode("-", $e);
					$idline[$i] = $elem[0];
					$idcollusers[$i] = $elem[1];
					$actionscollusers[$i] = $elem[2];
					$i++;
				}
			} 
		}

		$RAW_QUERYcollusers = "SELECT * FROM fos_user_collections where collection_id = ".$idcoll.";";
		$statement = $em->getConnection()->prepare($RAW_QUERYcollusers);
		$statement->execute();
		$linkcollusers = $statement->fetchAll();
		foreach($linkcollusers as $linkcollusers_obj){
			$idusers[]=$linkcollusers_obj['user_id'];
		}

		for ($y = 0; $y < sizeof($actionscollusers); $y++) {
			if ($idcollusers[$y]!= ""){
				if ($actionscollusers[$y] != "D"){
					$actionscollusers[$y] = "I";
					$role = $request->get('inp_addnewuserRole'.$idline[$y]);
					for ($z = 0; $z < count($idusers); $z++) {
						if($idusers[$z] == $idcollusers[$y]){
								$actionscollusers[$y] = "U";
								break;
						}
					}
				}
				
				if ($actionscollusers[$y] == "U"){
					$RAW_QUERY = "UPDATE fos_user_collections SET role = '".$role."' WHERE user_id = ".$idcollusers[$y]." AND collection_id = ".$idcoll.";";
					//print_r($RAW_QUERY);
				}
				if ($actionscollusers[$y] == "I"){
					$RAW_QUERY = "INSERT INTO fos_user_collections (collection_id, user_id, role) VALUES (".$idcoll.",".$idcollusers[$y].",'".$role."');";
				//	print_r($RAW_QUERY);
				}
				if ($actionscollusers[$y] == "D"){
					$RAW_QUERY = "DELETE FROM fos_user_collections WHERE collection_id = '".$idcoll."' and user_id = ".$idcollusers[$y]." ;";
				//	print_r($RAW_QUERY);
				}
				if ($actionscollusers[$y] == "I" | $actionscollusers[$y] == "U" | $actionscollusers[$y] == "D"){
					$statement = $em->getConnection()->prepare($RAW_QUERY);
					$statement->execute();
				}
			}
		}
	}
	
	public function addContributionAction(Request $request)
	{
	    $this->set_sql_session();
		$this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('1,2,3,7,9',['Curator','Validator','Encoder','Collection_manager']); 
		$rightoncollection=$this->check_collection_rights_general();
		if($rightoncollection)
		{
			$dcontribution = new Dcontribution();			
			$em = $this->getDoctrine()->getManager();
			$form = $this->createForm(DcontributionType::class, $dcontribution, array('entity_manager' => $em,));
			if ($request->isMethod('POST')) 
			{
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) 
				{
					try 
					{					
						$em->persist($dcontribution);
						if(strlen($form->get("date_year")->getData())>0)
						{
						$dcontribution->setDateByElementsForm(
							$form,
							$form->get("date_year")->getData(),
							$form->get("date_month")->getData(),
							$form->get("date_day")->getData());
						}
						$em->flush();					
						
						$link_array_contribs=$this->handle_many_to_many_relation_general(
							$request,
							$dcontribution,
							"contribdetail_id_contrib_",
							Dcontributor::class,
							"pk",
							Dlinkcontribute::class,
							Array("getIdContributor"=>"setIdContributor"),
							Array("contribdetail_role_contrib_"=>"setContributorrole", "contribdetail_order_contrib_"=>"setContributororder"),
							Array("setIdContribution"=>$dcontribution->getIdContribution())							
						);
						foreach($link_array_contribs as $dlink_obj)
						{
							$em->persist($dlink_obj);
						}
						$em->flush();
						//print("submit_2");
						return $this->redirectToRoute('app_edit_contribution', array('pk' => $dcontribution->getPk()));
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
			//Doctrine query seems asynchronous so need to pass the method to get fk directly to the template
			return $this->render('@App/contributions/contributionform.html.twig', array(
					'form' => $form->createView(),
					'dcontribution' => $dcontribution,
					'originaction'=>'add_beforecommit',
					'read_mode'=>"create"
				)
			);
		}
		else
		{
			return $this->render('@App/collnoaccess.html.twig');
		}
	}
	

	
	public function editContributionAction(Dcontribution $dcontribution, Request $request)
	{			
		$em = $this->getDoctrine()->getManager();	
		$this->set_sql_session();
		if (!$dcontribution) {
			throw $this->createNotFoundException('No document found' );
		}
		else
		{	
			
			
			$form = $this->createForm(DcontributionType::class, $dcontribution, array('entity_manager' => $em,));
			$this->set_date_ctrl_general($form, $dcontribution->getDate(),  $dcontribution->getDateformat(),"date_year","date_month","date_day");
			
			if ($request->isMethod('POST')) 
			{
			//print("POST");
				
				$form->handleRequest($request);
				

				if ($form->isSubmitted() && $form->isValid()) {
					try 
					{
					
						//echo "<script>console.log('dans valid' );</script>";
						//$dlinkcontribute=$this->handle_contribution_relations($request, $dcontribution);
						$dlinkcontribute=$this->handle_many_to_many_relation_general(
							$request,
							$dcontribution,
							"contribdetail_id_contrib_",
							Dcontributor::class,
							"pk",
							Dlinkcontribute::class,
							Array("getIdContributor"=>"setIdContributor"),
							Array("contribdetail_role_contrib_"=>"setContributorrole", "contribdetail_order_contrib_"=>"setContributororder",
							"contribdetail_pk_"=>"setPk"),
							Array("setIdContribution"=>$dcontribution->getIdContribution())
						);
						
						if($dlinkcontribute!==null)
						{
							
							$dcontribution->initNewDlinkcontribute($em, $dlinkcontribute);
							//reattach after update
							
						}
						
						$dlinkdocument=$this->handle_many_to_many_relation_general(
							$request,
							$dcontribution,
							"contrib_to_doc_id_doc_",
							Ddocument::class,
							"pk",
							Dlinkcontdoc::class,
							Array("getIddoc"=>"setId", "getIdcollection"=>"setIdCollection"),
							//important map PK of link, that must be on the HTML FORM
							Array("contrib_to_doc_id_link_"=>"setPk"),	
							array("setIdcontribution"=>$dcontribution->getIdContribution())
							);
						if($dlinkdocument!==null)
						{
							$dcontribution->initNewDlinkdocument($em, $dlinkdocument);
						}
						if(strlen($form->get("date_year")->getData())>0)
						{
							$dcontribution->setDateByElementsForm(
								$form,
								$form->get("date_year")->getData(),
								$form->get("date_month")->getData(),
								$form->get("date_day")->getData());
						}
						$em->flush();
						//print("DONE");
						
						$this->addFlash('success','DATA RECORDED IN DATABASE!');  
						
						//
						
					}
					catch(\Doctrine\DBAL\DBALException $e ) 
					{
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
					//return $this->redirectToRoute('app_edit_contribution', array('pk' => $dcontribution->getPk()));
					
				}elseif ($form->isSubmitted() && !$form->isValid() )
				{
					//echo "<script type='text/javascript'>alert('error in form');</script>";
				}
			}
			
			$rightoncollection1 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('1',['Curator','Validator','Collection_manager']); //'Viewer'
			$rightoncollection2 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('2',['Curator','Validator','Collection_manager']);
			$rightoncollection3 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('3',['Curator','Validator','Collection_manager']);
			$rightoncollection4 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('7',['Curator','Validator','Collection_manager']);
			$rightoncollection5 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('9',['Curator','Validator','Collection_manager']);
			if ($rightoncollection1 == true && $rightoncollection2 == true && $rightoncollection3 == true && $rightoncollection4 == true && $rightoncollection5 == true)
			{
			
				$search_form=$this->createForm(SearchAllForm::class, null);		
				//Doctrine query seems asynchronous so need to pass the method to get fk directly to the template
				$current_tab=$this->get_request_var($request, "current_tab", "main-tab");
				$logs=$em->getRepository(TDataLog::class)
						 ->findContributions("dcontribution", $dcontribution->getPk());
				$this->validate_form($request,$em,"dcontribution",$dcontribution->getPk());
				return $this->render('@App/contributions/contributionform.html.twig', array(
					'dcontribution' => $dcontribution,
					'form' => $form->createView(),
					'search_form'=>$search_form->createView(),
					'origin'=>'edit',
					'originaction'=>'edit',
					'dlinkcontribute'=>$dcontribution->initDlinkcontribute($em),
					'dlinkcontdoc'=>$dcontribution->initDlinkcontdoc($em),
					'current_tab'=>$current_tab,
					'logs'=>$logs,
					'read_mode'=>$this->enable_read_write($request)
				));
			}else{
				return $this->render('@App/collnoaccess.html.twig');
			}
		}
	}	
	
	
	public function addcollectionAction(Request $request){
		$codecollection = new Codecollection();
			
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(CollectionType::class, $codecollection, array('entity_manager' => $em,));
		
		$RAW_QUERYroles = "SELECT * FROM fos_role;";
		$statement = $em->getConnection()->prepare($RAW_QUERYroles);
		$statement->execute();
		$arrayroles = $statement->fetchAll();
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				try {
					$em->persist($codecollection);
					$em->flush();

					$m=0;
					$idcoll = $codecollection->getPk();
				
					if ($idcoll != ""){	
						$actionsusersstr = $request->get('newcontributors');
						$this->saveusers($idcoll,$actionsusersstr,$request);
					}  
					
					$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
				
					return $this->redirectToRoute('app_edit_collection', array('id' => $codecollection->getPk()));
				} catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
					echo "<script type='text/javascript'>alert('Record already exists with these values !');</script>";
				} catch(\Doctrine\DBAL\Exception\ConstraintViolationException $e ) {
					echo "<script type='text/javascript'>alert('There is a constraint violation with that transaction !');</script>";
				} catch(\Doctrine\DBAL\Exception\TableNotFoundException $e ) {
					echo "<script type='text/javascript'>alert('Table not found !');</script>";
				} catch(\Doctrine\DBAL\Exception\ConnectionException $e ) {
					echo "<script type='text/javascript'>alert('Problem of connection with database !');</script>";
				} catch(\Doctrine\DBAL\Exception\DriverException $e ) {
					echo "<script type='text/javascript'>alert('There is a syntax error with one field !');</script>";
				}
			}elseif ($form->isSubmitted() && !$form->isValid() ){
				echo "<script type='text/javascript'>alert('error in form');</script>";
			}
		}
		
        return $this->render('@App/collectionedit.html.twig', array(
            'edit_form' => $form->createView(),
			'originaction'=>'add_beforecommit',
			'arrayroles' => $arrayroles,
        ));
    }
	
	public function addmineralAction(Request $request){
		$lminerals = new Lminerals();

		$em = $this->getDoctrine()->getManager();

		$form = $this->createForm(LmineralsType::class, $lminerals, array('entity_manager' => $em,));
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				try {
					$em->persist($lminerals);
					$em->flush();
					return $this->redirectToRoute('app_edit_mineral', array('pk' => $lminerals->getPk()));
				} catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
					$lminerals->setPk(0);
					echo "<script type='text/javascript'>alert('Record already exists with these values !');</script>";
					//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction already exist" );
				} catch(\Doctrine\DBAL\Exception\ConstraintViolationException $e ) {
					$lminerals->setPk(0);
					echo "<script type='text/javascript'>alert('There is a constraint violation with that transaction !');</script>";
					//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Bad request on Transaction" );
				} catch(\Doctrine\DBAL\Exception\TableNotFoundException $e ) {
					$lminerals->setPk(0);
					echo "<script type='text/javascript'>alert('Table not found !');</script>";
					//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction Table not found" );
				} catch(\Doctrine\DBAL\Exception\ConnectionException $e ) {
					$lminerals->setPk(0);
					echo "<script type='text/javascript'>alert('Problem of connection with database !');</script>";
					//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction Table not found" );
				}
			}elseif ($form->isSubmitted() && !$form->isValid() ){
				$lminerals->setPk(0);
				echo "<script type='text/javascript'>alert('error in form');</script>";
			}
		}

        return $this->render('@App/addmineral.html.twig', array(
			'lminerals' => $lminerals,
            'Mineral_form' => $form->createView(),
        ));
    }	

	
	public function adddocumentAction(Request $request){
		$this->set_sql_session();
		$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('1,2,3,7,9',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
        //$list_keywords=Array();
		if ($rightoncollection == true){
			$ddocument = new Ddocument();

			$em = $this->getDoctrine()->getManager();

			$form = $this->createForm(DdocumentType::class, $ddocument, array('entity_manager' => $em,));
            
            
			if ($request->isMethod('POST')) {
			
            
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
					try {
						
						$keywords=$request->get("widget_keywords",null);
						if($keywords!==null)
						{
							foreach($keywords as $key)
							{
								$keyobj=new Dkeyword();
								$keyobj->setKeyword($key);
								$keyobj->setKeywordlevel("1");
								$keyobj->setIdcollectionobj($ddocument);
								//$ddocument->addDkeyword($keyobj);
								$em->persist($keyobj);
							}
						}
						
						$titles=$request->get("widget_titles",null);
						if($titles!==null)
						{
							$i=1;
							foreach($titles as $title)
							{
								$titleobj=new Ddoctitle();
								$titleobj->setTitle($title);
								$titleobj->setTitlelevel($i);
								$titleobj->setIdcollection($ddocument->getIdCollection());
								$titleobj->setIddoc($ddocument->getIddoc());
								//$ddocument->addDkeyword($keyobj);
								$em->persist($titleobj);
								$i++;
							}
						}
						$em->persist($ddocument);
						
						
						$link_array_contribs=$this->handle_many_to_many_relation_general(
							$request,
							$ddocument,
							"doc_to_contrib_id_contrib_",
							Dcontribution::class,
							"pk",
							Dlinkcontdoc::class,
							Array("getIdcontribution"=>"setIdcontribution"),
							Array(),
							array("setrelationidcollection"=>$ddocument)
							);
						foreach($link_array_contribs as $dlink_obj)
						{
							$em->persist($dlink_obj);
						}
						
						$em->flush();
						$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
						return $this->redirectToRoute('app_edit_doc', array('pk' => $ddocument->getPk()));
						
					}
					catch(\Doctrine\DBAL\DBALException $e ) {
						$form->addError(new FormError($e->getMessage()));
					}
					catch(Exception $e ) {
						$form->addError(new FormError($e->getMessage()));
					}
				}elseif ($form->isSubmitted() && !$form->isValid() ){
					//$ddocument->setPk(0);
					//echo "<script type='text/javascript'>alert('error in form');</script>";
				}
			}
			
			return $this->render('@App/documents/adddocument.html.twig', array(
				'ddocument' => $ddocument,
				'form' => $form->createView(),
				'originaction'=>'add_beforecommit',
				'read_mode'=>"create"
				
			));
		}else{
			return $this->render('@App/collnoaccess.html.twig');
		}
    }
	
	public function addpointsAction(Request $request){
		$this->set_sql_session();
		$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('4,15,26',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
		if ($rightoncollection == true){
			$dloccenter = new DLoccenter();
			$dcontribution = new Dcontribution();
			
			//$editvals = "";
			$em = $this->getDoctrine()->getManager();
			$form = $this->createForm(PointEditType::class, $dloccenter, array('entity_manager' => $em,));
			$form2 = $this->createForm(DcontributionType::class, $dcontribution, array('entity_manager' => $em,));
			
			if ($request->isMethod('POST')) {
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
					try 
					{
						$em->persist($dloccenter);
						$em->flush();
						$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
						return $this->redirectToRoute('app_edit_point', array('pk' => $dloccenter->getPk()));
						//return $this->redirectToRoute('app_edit_point', array('editvals' => $editvals,'pk' => $dloccenter->getPk()));
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
				}elseif ($form->isSubmitted() && !$form->isValid() ){
					$dloccenter->setPk(0);
					echo "<script type='text/javascript'>alert('error in form');</script>";
				}
			}

			return $this->render('@App/dloccenter/addpoints.html.twig', array(
				'dloccenter' => $dloccenter,
				'point_form' => $form->createView(),
				'form2' => $form2->createView(),
				'originaction'=>'add_beforecommit',
				'read_mode'=>"create"
			));
		}else{
			return $this->render('@App/collnoaccess.html.twig');
		}
    }
	
	/*public function addstratumAction(Request $request){
		$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('4,15,26',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
		if ($rightoncollection == true){
			$dloclitho = new DLoclitho();

			$em = $this->getDoctrine()->getManager();
			$form = $this->createForm(StratumType::class, $dloclitho, array('entity_manager' => $em,));
			
			if ($request->isMethod('POST')) {
				//print_r($request->get('---'.'appbundle_dloclitho_alternance'.'---'));
				echo "<script type='text/javascript'>alert('++++'+'".$request->get('inp_Bottomstratum')."'+'---');</script>";
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
					try {
						$em->persist($dloclitho);
						$em->flush();
						$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
						return $this->redirectToRoute('app_edit_point', array('pk' => $dloclitho->getPk()));
						//return $this->redirectToRoute('app_edit_point', array('editvals' => $editvals,'pk' => $dloccenter->getPk()));
					} catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
						echo "<script type='text/javascript'>alert('Record already exists with these values !');</script>";
					} catch(\Doctrine\DBAL\Exception\ConstraintViolationException $e ) {
						echo "<script type='text/javascript'>alert('There is a constraint violation with that transaction !');</script>";
					} catch(\Doctrine\DBAL\Exception\TableNotFoundException $e ) {
						echo "<script type='text/javascript'>alert('Table not found !');</script>";
					} catch(\Doctrine\DBAL\Exception\ConnectionException $e ) {
						echo "<script type='text/javascript'>alert('Problem of connection with database !');</script>";
					} catch(\Doctrine\DBAL\Exception\DriverException $e ) {
						echo "<script type='text/javascript'>alert('There is a syntax error with one field !');</script>";
					}
				}elseif ($form->isSubmitted() && !$form->isValid() ){
					$dloclitho->setPk(0);
					echo "<script type='text/javascript'>alert('error in form');</script>";
				}
			}
			
			return $this->render('@App/addstratum.html.twig', array(
				'dloclitho' => $dloclitho,
				'stratum_form' => $form->createView(),
				'originaction'=>'add_beforecommit'
			));
		}else{
			return $this->render('@App/collnoaccess.html.twig');
		}
    }
	*/
	
	public function adddrillingAction(Request $request){
		$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('4,15,26',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
		if ($rightoncollection == true){
			return $this->render('@App/adddrilling.html.twig');
		}else{
			return $this->render('@App/collnoaccess.html.twig');
		}
    }
		

	
	public function editsampleAction(Dsample $dsample, Request $request){				
		if (!$dsample) {
			throw $this->createNotFoundException('No sample found' );
		}else{
			$RAW_QUERY = "";
			$grades[]=null;
			$ids[]=null;
			$idsHM[]=null;
			$mins[]=null;
			$HMnames[]=null;
			$actions[] = null;
			$actionsHM[] = null;
			$actionheavymin = "";
			$grades_originals[]=null;
			$ids_originals[]=null;
			$newgrades = "";
			$arraylminerals[]=null;
			$actionscontributions[] = null;
			$arraydcontributors[]=null;
			$heavymin[]=null;
			$samheavymin2 =null;
			$origin_error = "";
			$em = $this->getDoctrine()->getManager();
			
			$idcol = $dsample->getIdcollection();
			$idsamp = $dsample->getIdsample();
							
			//Minerals
			$sammineral = $this->getDoctrine()
			->getRepository(Dsamminerals::class)
			 ->findBy(array('idcollection' => $idcol, 
							'idsample' => $idsamp
						   ));
			
			foreach($sammineral as $dsammineral_obj){
				$grades_originals[]=$dsammineral_obj->getGrade();
				$ids_originals[]=$dsammineral_obj->getIdMineral()->getIdMineral();
				$lminerals = $this->getDoctrine()
				->getRepository(Lminerals::class)
				->findBy(array(	'idmineral' => $dsammineral_obj->getIdMineral()	));
				$arraylminerals[]=$lminerals;
			}
			
			//Suscept. magnet.
			$sammagsusc = $this->getDoctrine()
			->getRepository(Dsammagsusc::class)
			 ->findBy(array('idcollection' => $idcol, 
							'idsample' => $idsamp
						   ));
			if ($sammagsusc != null){
				$actionmagsusc = "U";
			}else{
				$actionmagsusc = "I";
			}
			
			//granulo
			$samgranulo = $this->getDoctrine()
			->getRepository(Dsamgranulo::class)
			 ->findBy(array('idcollection' => $idcol, 
							'idsample' => $idsamp
						   ));
			if ($samgranulo != null){
				$actiongranulo = "U";
			}else{
				$actiongranulo = "I";
			}
			
		/*	foreach($samgranulo as $samgranulo_obj){
				echo "<script type='text/javascript'>alert('".$samgranulo_obj->getDate()."');</script>";
			}*/
					

			//HeavyMinerals
			//$weight_exist = true;
			$samheavymin = $this->getDoctrine()
			->getRepository(Dsamheavymin::class)
			 ->findBy(array('idcollection' => $idcol, 
							'idsample' => $idsamp
						   ));
						   
			if (count($samheavymin) > 0 ){
				foreach($samheavymin as $samheavymin_obj){
					if ($samheavymin_obj->getWeighthm() == null){
						$actionheavymin = "I";
						//$weight_exist = false;
					}else{
						$actionheavymin = "U";


					}
						$RAW_QUERY = "SELECT * FROM dsamheavymin2 where idcollection = '".$idcol."' AND idsample = ".$idsamp.";";
						$statement = $em->getConnection()->prepare($RAW_QUERY);
						$statement->execute();
						$samheavymin2 = $statement->fetchAll();
						
						foreach($samheavymin2 as $samheavymin2_obj){
							$HMnames[]=$samheavymin2_obj['mineral'];
						}
				}
			}else{
				$actionheavymin = "I";
			}
			
			//Contributions
			$samcontribution = $this->getDoctrine()
			->getRepository(DLinkContSam::class)
			 ->findBy(array('idcollection' => $idcol, 
							'id' => $idsamp
						   ));
			
			foreach($samcontribution as $samcontribution_obj){
				$idcontrib = $samcontribution_obj->getIdContribution()->getIdContribution();
				
				$idscontr_originals[]=$samcontribution_obj->getIdContribution()->getIdContribution();
				$RAW_QUERYdcontributions = "SELECT c.pk as pkcontribution,
							c.idcontribution as idcontribution,
							datetype,
							year, 
							date,			
							o.pk as pkcontributor,
							o.idcontributor as idcontributor,
							people,
							peoplefonction,
							peopletitre,
							peoplestatut,
							institut,
							contributorrole,
							contributororder
						FROM dcontribution c
						LEFT JOIN dlinkcontribute l ON l.idcontribution = c.idcontribution
						LEFT JOIN dcontributor o ON l.idcontributor = o.idcontributor
						WHERE c.idcontribution = ".$idcontrib."
						ORDER by idcontribution;";
				$statement = $em->getConnection()->prepare($RAW_QUERYdcontributions);
				$statement->execute();
				$arraydcontributors1 = $statement->fetchAll();
				
				if ($arraydcontributors != null){
					array_push($arraydcontributors,$arraydcontributors1);
				}else{
					$arraydcontributors = $arraydcontributors1;
				}
			}
						
			$dcontribution = new Dcontribution();
			$form = $this->createForm(DsampleEditType::class, $dsample, array('entity_manager' => $em,));
			$form2 = $this->createForm(DcontributionType::class, $dcontribution, array('entity_manager' => $em,));
			
			if ($request->isMethod('POST')) {
				$form->handleRequest($request);
				//return new Response('<html><body>'.print_r($request->request->keys()).' '.$request->get('newgrades').'</body></html>' );
				if ($form->isSubmitted() && $form->isValid()) {
					//return new Response('<html><body>'.print_r ("poids=".$request->get('inp_mmagsuscWeight')).'</body></html>' );

					try {
						$em->flush();
						//return new Response('<html><body>'.$request->get('newhminerals').'</body></html>' );
						$id_grades = $request->get('newgrades');
						$arrayid_grades =  explode(",", $id_grades); 
						$elem =array();
						
						$i=0;
						foreach($arrayid_grades as $e) {
							if ($e != ""){
								$elem = explode("-", $e);
								$ids[$i] = $elem[0];
								$grades[$i] = $elem[1];
								$actions[$i] = $elem[2];
								$i++;
							}
						} 
						//$m=0;
						//$n=0;

						for ($y = 0; $y < sizeof($ids); $y++) {
							if ($actions[$y] == "U"){
								$RAW_QUERY = "UPDATE dsamminerals SET grade = ".$grades[$y]." WHERE idmineral = ".$ids[$y]." AND idcollection = '".$idcol."' AND idsample = ".idsamp.';';
							}
							if ($actions[$y] == "I"){
								$RAW_QUERY = "INSERT INTO dsamminerals (idcollection,idsample,idmineral,grade) VALUES ('".$idcol."',".$idsamp.",".$ids[$y].",".$grades[$y].');';
							}
							if ($actions[$y] == "D"){
								$RAW_QUERY = "DELETE FROM dsamminerals WHERE idcollection = '".$idcol."' and idsample = ".$idsamp." and idmineral = ".$ids[$y]." ;";
							}
							if ($actions[$y] == "I" | $actions[$y] == "U" | $actions[$y] == "D"){
								$statement = $em->getConnection()->prepare($RAW_QUERY);
								$statement->execute();
								
							}
						}
						
						if ($request->get('inp_granuloWeightTot') > 0){	
							$origin_error = "In granulometry, ";						
							$weightTot = $request->get('inp_granuloWeightTot');
							$weightsand = $request->get('inp_granuloWeightSand');
							$wAbove2000 = $request->get('inp_granulop2000');
							$w2000 = $request->get('inp_granulo-2000');
							$w1400 = $request->get('inp_granulo-1400');
							$w1000 = $request->get('inp_granulo-1000');
							$w710 = $request->get('inp_granulo-710');
							$w500 = $request->get('inp_granulo-500');
							$w355 = $request->get('inp_granulo-355');
							$w250 = $request->get('inp_granulo-250');
							$w180 = $request->get('inp_granulo-180');
							$w125 = $request->get('inp_granulo-125');
							$w90 = $request->get('inp_granulo-90');
							$w63 = $request->get('inp_granulo-63');
							$description = $request->get('inp_granuloDescr');
							$date = $request->get('inp_granuloDate');

							if ($date == "") {
								$date = 'NULL';
							}else{
								$date = "'".$date."'";
							}
							
							if ($actiongranulo == "U"){
								$RAW_QUERY = "UPDATE dsamgranulo SET weighttot = ".$weightTot.",weightsand = ".$weightsand.",w_above_2000 = ".$wAbove2000.",w_2000 = ".$w2000.",w_1400 = ".$w1400.",w_1000 = ".$w1000.",w_710 = ".$w710.",w_500 = ".$w500.",w_355 = ".$w355.",w_250 = ".$w250.",w_180 = ".$w180.",w_125 = ".$w125.",w_90 = ".$w90.",w_63 = ".$w63.",description = '".$description."', date = ".$date." WHERE idcollection = '".$idcol."' AND idsample = ".$idsamp.';';
							}
							if ($actiongranulo == "I"){
								$RAW_QUERY = "INSERT INTO dsamgranulo (idcollection,idsample,weighttot,weightsand,w_above_2000,w_2000,w_1400,w_1000,w_710,w_500,w_355,w_250,w_180,w_125,w_90,w_63,description,date) VALUES ('".$idcol."',".$idsamp.",".$weightTot.",".$weightsand.",".$wAbove2000.",".$w2000.",".$w1400.",".$w1000.",".$w710.",".$w500.",".$w355.",".$w250.",".$w180.",".$w125.",".$w90.",".$w63.",'".$description."',".$date.");";
							}
							if ($actiongranulo == "I" | $actiongranulo == "U" ){ 
								//return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );
								$statement = $em->getConnection()->prepare($RAW_QUERY);
								$statement->execute();
							}
						}
						
						if ($request->get('inp_mmagsuscWeight') > 0){			
							$origin_error = "In magnetic susceptibility, ";
							$weight = $request->get('inp_mmagsuscWeight');
							$Mesure1 = $request->get('inp_mmagsuscMesure1');
							$Mesure2 = $request->get('inp_mmagsuscMesure2');
							$Mesure3 = $request->get('inp_mmagsuscMesure3');
							$Mesure4 = $request->get('inp_mmagsuscMesure4');
							$Mesure5 = $request->get('inp_mmagsuscMesure5');
							$Mesure6 = $request->get('inp_mmagsuscMesure6');
							$exp = $request->get('inp_mmagsuscExp');
							
							if ($actionmagsusc == "U"){
								$RAW_QUERY = "UPDATE dsammagsusc SET weight = ".$weight.",mesure1 = ".$Mesure1.",mesure2 = ".$Mesure2.",mesure3 = ".$Mesure3.",mesure4 = ".$Mesure4.",mesure5 = ".$Mesure5.",mesure6 = ".$Mesure6.",exponent = ".$exp." WHERE idcollection = '".$idcol."' AND idsample = ".$idsamp.';';
							}
							if ($actionmagsusc == "I"){
								$RAW_QUERY = "INSERT INTO dsammagsusc (idcollection,idsample,weight,mesure1,mesure2,mesure3,mesure4,mesure5,mesure6,exponent) VALUES ('".$idcol."',".$idsamp.",".$weight.",".$Mesure1.",".$Mesure2.",".$Mesure3.",".$Mesure4.",".$Mesure5.",".$Mesure6.",".$exp.");";
							}
							if ($actionmagsusc == "I" | $actionmagsusc == "U" ){ 
							//return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );
								$statement = $em->getConnection()->prepare($RAW_QUERY);
								$statement->execute();
							}
						}
			
						if ($request->get('inp_HMWeightSample') > 0){
							//return new Response('<html><body>'.print_r($request->get('inp_HMWeightSample')).'</body></html>' );
							$origin_error = "In heavy minerals(weights), ";
							$WeightSample = $request->get('inp_HMWeightSample');
							$WeightHM = $request->get('inp_HMWeightHM');
							$HMDescript = $request->get('inp_HMDescript');
							
							if ($actionheavymin == "U"){
								$RAW_QUERY = "UPDATE dsamheavymin SET weightsample = ".$WeightSample.",weighthm = ".$WeightHM.",observationhm = '".$HMDescript."' WHERE idcollection = '".$idcol."' AND idsample = ".$idsamp.';';
							}
							if ($actionheavymin == "I"){
								$RAW_QUERY = "INSERT INTO dsamheavymin (idcollection,idsample,weightsample,weighthm,observationhm) VALUES ('".$idcol."',".$idsamp.",".$WeightSample.",".$WeightHM.",'".$HMDescript."');";
							}
							if ($actionheavymin == "I" | $actionheavymin == "U" ){ 
								//return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );
								$statement = $em->getConnection()->prepare($RAW_QUERY);
								$statement->execute();
							}
						}
						//return new Response('<html><body>'.$request->get('newhminerals').'</body></html>' );
						//if ($request->get('inp_HMname0') != ""){	
						if ($request->get('newhminerals') != ""){	
							$origin_error = "In heavy minerals, ";
							$actionshmstr = $request->get('newhminerals');
							//return new Response('<html><body>'.print_r($actionshmstr).'</body></html>' );
							if ($actionshmstr != ""){
								
								$arrayid_mins =  explode(",", $actionshmstr); 
								$elem =array();
								$i=0;
								foreach($arrayid_mins as $e) {
									if ($e != ""){
										$elem = explode("-", $e);
										$idsHM[$i] = $elem[0];
										$mins[$i] = $elem[1];
										$grainsarr[$i] = $elem[2];
										$HMdescrarr[$i] = $elem[3];
										$actionsHM[$i] = $elem[4];
										$i++;
									}
								} 
								//$actionheavymin2 = "D";
							}
							//return new Response('<html><body>'.$request->get('inp_HMgrains'.$idsHM[$y]).'</body></html>' );
						//	$RAW_QUERYSSSS = "";
							for ($y = 0; $y < sizeof($actionsHM); $y++) {
								//if ($request->get('inp_HMname'.$ids[$y]) != ""){
								if ($mins[$y]!= ""){
									/*$actionsHM[$y] = "I";
									$mineral = $mins[$y];
									$grains = $request->get('inp_HMgrains'.$ids[$y]);
									$observ = $request->get('inp_HMobs'.$ids[$y]);
									
									for ($z = 0; $z < count($HMnames); $z++) {
										if($HMnames[$z] == $mineral){
											if ($actionsHM[$y] != "D"){
												$actionsHM[$y] = "U";
												break;
											}
										}
									}*/
								//	return new Response('<html><body>'.$request->get('inp_HMgrains'.$idsHM[$y]).'</body></html>' );
									if ($actionsHM[$y] != "D"){
										$actionsHM[$y] = "I";
										//$grains = $request->get('inp_HMgrains'.$idsHM[$y]);
										$grains = $grainsarr[$y];
										//$observ = $request->get('inp_HMobs'.$idsHM[$y]);
										$observ = $HMdescrarr[$y];
										for ($z = 0; $z < count($HMnames); $z++) {
											
											if($HMnames[$z] == $mins[$y]){
												//return new Response('<html><body>'.$mins[$y].'</body></html>' );
												$actionsHM[$y] = "U";
												break;
											}
										}
									}
									
									if ($actionsHM[$y] == "U"){
										$RAW_QUERY = "UPDATE dsamheavymin2 SET minnum = ".$grains.",observationhm = '".$observ."' WHERE idcollection = '".$idcol."' AND idsample = ".$idsamp." AND mineral = '".$mins[$y]."';";
									}
									if ($actionsHM[$y] == "I"){
										$RAW_QUERY = "INSERT INTO dsamheavymin2 (idcollection, idsample, mineral, minnum, observationhm) VALUES ('".$idcol."',".$idsamp.",'".$mins[$y]."',".$grains.",'".$observ."');";
									}
									if ($actionsHM[$y] == "D"){
										$RAW_QUERY = "DELETE FROM dsamheavymin2 WHERE idcollection = '".$idcol."' and idsample = ".$idsamp." and mineral = '".$mins[$y]."' ;";
									}
								//	$RAW_QUERYSSSS = $RAW_QUERYSSSS.$RAW_QUERY;
								//	return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );
									if ($actionsHM[$y] == "I" | $actionsHM[$y] == "U" | $actionsHM[$y] == "D"){
									//	print_r($RAW_QUERY);
									//	return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );
										$statement = $em->getConnection()->prepare($RAW_QUERY);
										$statement->execute();
									}
									
								}
							}
							//return new Response('<html><body>'.print_r($RAW_QUERYSSSS).'</body></html>' );
						}  
						
						if ($request->get('newcontributions') != ""){	
							$id_contributions = $request->get('newcontributions');
							$arrayid_contributions =  explode(",", $id_contributions); 
							$elem =array();
							$i=0;
							foreach($arrayid_contributions as $e) {
								if ($e != ""){
									$elem = explode("-", $e);
									$ids[$i] = $elem[1];
									$actionscontributions[$i] = $elem[2];
									$i++; 
								}
							} 
							$sumy = "";
							for ($y = 0; $y < sizeof($ids); $y++) {
								$sumy = $sumy." ".$ids[$y];
							}
							//return new Response('<html><body>'.print_r($sumy).'</body></html>' );
							for ($y = 0; $y < sizeof($ids); $y++) {
								if ($actionscontributions[$y] == "I"){
									$RAW_QUERY = "INSERT INTO dlinkcontsam (idcollection,id,idcontribution) VALUES ('".$idcol."',".$idsamp.",".$ids[$y].');';
								}
								if ($actionscontributions[$y] == "D"){
									$RAW_QUERY = "DELETE FROM dlinkcontsam WHERE idcollection = '".$idcol."' and id = ".$idsamp." and idcontribution = ".$ids[$y]." ;";
								}
								if ($actionscontributions[$y] == "I" |$actionscontributions[$y] == "D"){
									$statement = $em->getConnection()->prepare($RAW_QUERY);
									$statement->execute();
								}
							}
						}
						
					//	return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );
						$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
						return $this->redirectToRoute('app_edit_sample', array('pk' => $dsample->getPk()));
					} catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
						$message = $origin_error."record already exists with these values of collection and ID !";
						//return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );
						echo "<script type='text/javascript'>alert('$message');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction already exist" );
					} catch(\Doctrine\DBAL\Exception\ConstraintViolationException $e ) {
						$message = $origin_error."there is a constraint violation with that transaction !";
						echo "<script type='text/javascript'>alert('$message');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Bad request on Transaction" );
					} catch(\Doctrine\DBAL\Exception\TableNotFoundException $e ) {
						$message = $origin_error."table not found !";
						echo "<script type='text/javascript'>alert('$message');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction Table not found" );
					} catch(\Doctrine\DBAL\Exception\ConnectionException $e ) {
						echo "<script type='text/javascript'>alert('Problem of connection with database !');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction Table not found" );
					}
				}elseif ($form->isSubmitted() && !$form->isValid() ){
					echo "<script type='text/javascript'>alert('error in form');</script>";
				}
			}

			$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('6,12,13',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
			if ($rightoncollection == true){
				if ($grades_originals != null AND isset($arraydcontributors[1])){
					//return new Response('<html><body>ok ok</body></html>' );
					return $this->render('@App/editsample.html.twig', array(
						'dsample' => $dsample,
						'form' => $form->createView(),
						'form2' => $form2->createView(),
						'grades' => $grades_originals,
						'arraylminerals' => $arraylminerals,
						'sammagsusc' => $sammagsusc,
						'samgranulo' => $samgranulo,
						'samheavymin' => $samheavymin,
						'samheavymin2' => $samheavymin2,
						'arrayLinkcontributions' => $arraydcontributors,
						'originaction'=>'edit'
					));
				}elseif ($grades_originals == null AND isset($arraydcontributors[1])){
				//	return new Response('<html><body>nok ok</body></html>' );
					return $this->render('@App/editsample.html.twig', array(
						'dsample' => $dsample,
						'form' => $form->createView(),
						'form2' => $form2->createView(),
						'arraylminerals' => $arraylminerals,
						'sammagsusc' => $sammagsusc,
						'samgranulo' => $samgranulo,
						'samheavymin' => $samheavymin,
						'samheavymin2' => $samheavymin2,
						'arrayLinkcontributions' => $arraydcontributors,
						'originaction'=>'edit'
					));
				}elseif ($grades_originals != null AND !isset($arraydcontributors[1])){
				//	return new Response('<html><body>ok nok</body></html>' );
					return $this->render('@App/editsample.html.twig', array(
						'dsample' => $dsample,
						'form' => $form->createView(),
						'form2' => $form2->createView(),
						'grades' => $grades_originals,
						'arraylminerals' => $arraylminerals,
						'sammagsusc' => $sammagsusc,
						'samgranulo' => $samgranulo,
						'samheavymin' => $samheavymin,
						'samheavymin2' => $samheavymin2,
						'originaction'=>'edit'
					));
				}elseif ($grades_originals == null AND !isset($arraydcontributors[1])){
				//	return new Response('<html><body>nok nok</body></html>' );
					return $this->render('@App/editsample.html.twig', array(
						'dsample' => $dsample,
						'form' => $form->createView(),
						'form2' => $form2->createView(),
						'arraylminerals' => $arraylminerals,
						'sammagsusc' => $sammagsusc,
						'samgranulo' => $samgranulo,
						'samheavymin' => $samheavymin,
						'samheavymin2' => $samheavymin2,
						'originaction'=>'edit'
					));
				}
			}else{
				return $this->render('@App/collnoaccess.html.twig');
			}
		}
    }	
	
	public function editmineralAction(Lminerals $lminerals, Request $request){
		$em = $this->getDoctrine()->getManager();
		
		if (!$lminerals) {
			throw $this->createNotFoundException('No mineral found' );
		}else{
			
			$form = $this->createForm(LmineralsEditType::class, $lminerals, array('entity_manager' => $em,));
			
			if ($request->isMethod('POST')) {
				$form->handleRequest($request);

				if ($form->isSubmitted() && $form->isValid()) {
					try {
						$em->flush();
						$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
						return $this->redirectToRoute('app_edit_mineral', array('pk' => $lminerals->getPk()));
					} catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
						echo "<script type='text/javascript'>alert('Record already exists with these values of collection and ID !');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction already exist" );
					} catch(\Doctrine\DBAL\Exception\ConstraintViolationException $e ) {
						echo "<script type='text/javascript'>alert('There is a constraint violation with that transaction !');</script>";
						throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Bad request on Transaction" );
					} catch(\Doctrine\DBAL\Exception\TableNotFoundException $e ) {
						echo "<script type='text/javascript'>alert('Table not found !');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction Table not found" );
					} catch(\Doctrine\DBAL\Exception\ConnectionException $e ) {
						echo "<script type='text/javascript'>alert('Problem of connection with database !');</script>";
						//throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, "Transaction Table not found" );
					}
				}elseif ($form->isSubmitted() && !$form->isValid() ){
					echo "<script type='text/javascript'>alert('error in form');</script>";
				}
			}
			
			$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('13',['Curator','Validator','Collection_manager']); //'Viewer'
			if ($rightoncollection == true){
				return $this->render('@App/editmineral.html.twig', array(
					'lminerals' => $lminerals,
					'Mineral_form' => $form->createView(),
					'origin'=>'edit'
				));
			}else{
				return $this->render('@App/collnoaccess.html.twig');
			}
		}
    }
	
	public function editpointAction(Dloccenter $dloccenter, Request $request)
	{  
		$this->set_sql_session();
		if (!$dloccenter) {
			throw $this->createNotFoundException('No location found' );
		}else{
			
			$em = $this->getDoctrine()->getManager();
			$this->set_sql_session();
			$idcol = $dloccenter->getIdcollection();
			$idloc = $dloccenter->getIdpt();		
			

			$form = $this->createForm(PointEditType::class, $dloccenter, array('entity_manager' => $em,));
			
			
			if ($request->isMethod('POST')) 
			{
				$form->handleRequest($request);

				if ($form->isSubmitted() && $form->isValid()) 
				{
					try 
					{
						//print("test_1");
						$dloclitho_http=$this->get_request_var($request,"point_to_stratum_lithostratum",null);
						if($dloclitho_http!==null)
						{
							//print("test_2");	
							//print("save_sat");
							//date not handled
							$dloclitho=$this->getHTTPOneToManyByIndex($request, "point_to_stratum_lithostratum",DLoclitho::class,
								[
									"point_to_stratum_pk"=>"setPk",
									"point_to_stratum_lithostratum"=>"setLithostratum",
									"point_to_stratum_orderstratum"=>"setIdstratum",
									"point_to_stratum_alternance"=>"setAlternance",
									"point_to_stratum_topstratum"=>"setTopstratum"
									,
									"point_to_stratum_bottomstratum"=>"setBottomstratum",
									"point_to_stratum_descriptionstratum"=>"setDescriptionstratum"
									
								],
								["setIdcollection"=>$dloccenter->getIdcollection(), "setIdpt"=>$dloccenter->getIdpt(),]
								
								);
							//print_r($dloclitho);
							//modify pk before fk and do not take id stratum in constraint to allow modification !!
							$dloccenter->initNewDloclitho($em, $dloclitho);
							if(count($dloclitho)>0)
							{	
								//attach description
								foreach($dloclitho as $key=>$obj)
								{
									//print_r($key);
									//print_r($obj);
									$desc_prefix="point_to_stratum_description_".$key."_description";
									$desc_pk_prefix="point_to_stratum_description_".$key."_pk";
									//print($desc_prefix);
									$desc_http=$this->get_request_var($request,$desc_prefix,null);
									$desc_pk=$this->get_request_var($request,$desc_pk_prefix,null);
									if($desc_http!==null && $desc_pk!=null)
									{
										$array_desc=Array();
										$i=1;
										foreach($desc_http as $desc)
										{
											//print_r($obj);
											$tmp_desc=new Dlocstratumdesc();
											$tmp_desc->setDescript($desc);
											$tmp_desc->setIdcollection($obj->getIdcollection());
											$tmp_desc->setIdpt($obj->getIdpt());
										    $tmp_desc->setIdstratum($obj->getIdstratum());
											 $tmp_desc->setPk($desc_pk[$i]);
											$array_desc[]=$tmp_desc;
											$i++;
										}
										$obj->initNewDlocstratumdesc($em, $array_desc);
										
									}
								}
								
							}
							//documents
							$dlinkdocument=$this->handle_many_to_many_relation_general(
							$request,
							$dloccenter,
							"point_to_doc_id_doc_",
							Ddocument::class,
							"pk",
							Dlinkdocloc::class,
							Array("getIddoc"=>"setIddoc", "getIdcollection"=>"setIdCollecdoc"),
							//important map PK of link, that must be on the HTML FORM
							Array("point_to_doc_id_link_"=>"setPk"),	
							array("setrelationidloc"=>$dloccenter)
							);
							if($dlinkdocument!==null)
							{
								$dloccenter->initNewDLinkdocloc($em, $dlinkdocument);
							}
							//contribution
							$dlinkcontribute=$this->handle_many_to_many_relation_general(
							$request,
							$dloccenter,
							"point_to_contrib_id_contrib_",
							Dcontribution::class,
							"pk",
							Dlinkcontloc::class,
							Array("getIdcontribution"=>"setIdcontribution"),
							Array(),
							array("setIdPtObj"=>$dloccenter)
							);
							if($dlinkcontribute!==null)
							{
								if(count($dlinkcontribute)>0)
								{
									
									$dloccenter->initNewDLinkcontloc($em, $dlinkcontribute);
									//reattach after update
									
								//throw new UndefinedOptionsException();
								}
							}
						}
						$em->flush();
						//drilling
						$txtdiameter = "";					
						$this->addFlash('success','DATA RECORDED IN DATABASE!');   //$dloccenter->getCoordLong()
						
						//return $this->redirectToRoute('app_edit_point', array('pk' => $dloccenter->getPk()));
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
			
			$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('4,15,26',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
			if ($rightoncollection == true){
				//$editvals = "lat:".$dloccenter->getCoordLat().",,long:".$dloccenter->getCoordLat()	;
				//echo "<script>alert('dans controller fin:".$editvals."');</script>";

				$longarr =array();
				$longarr[0]=$dloccenter->getCoordLong();
				$latarr =array();
				$latarr[0]=$dloccenter->getCoordLat();
				$logs=$em->getRepository(TDataLog::class)
						 ->findContributions("dloccenter", $dloccenter->getPk());
				//$this->validate_form($request, $em,"dloccenter",$dloccenter->getPk());
				$current_tab=$this->get_request_var($request, "current_tab");
				return $this->render('@App/dloccenter/editpoint.html.twig', array(
						'latcoord' => $latarr,
						'longcoord' => $longarr,
						'dloccenter' => $dloccenter,
						'point_form' => $form->createView(),
						'dloclitho'	=>	$dloccenter->initDloclitho($em),
                        'dlinkdocloc' => $dloccenter->initDLinkdocloc($em),	
						'dlinkcontloc' => $dloccenter->initDLinkcontloc($em),							
						'origin'=>'edit',
						'originaction'=>'edit',
						'current_tab'=>$current_tab,
						'logs'=>$logs,
						'read_mode'=>$this->enable_read_write($request)
					));
			}else{
				return $this->render('@App/collnoaccess.html.twig');
			}
		}
    }
	
	
	

	public function editdocAction(Ddocument $ddocument, Request $request)
	{
		
		$em = $this->getDoctrine()->getManager();	
		$this->set_sql_session();
		if (!$ddocument) {
			throw $this->createNotFoundException('No document found' );
		}else{

						
			
			$keywords=$ddocument->initDkeywords($em);
			$titles=$ddocument->initDdoctitles($em);
			$dlinkcontribute=$ddocument->initDLinkcontdoc($em);
			$dlinkdocloc=$ddocument->initDLinkdocloc($em);
			$satellites=$ddocument->initDdocsatellite($em);

			
			$form = $this->createForm(DdocumentEditType::class, $ddocument, array('entity_manager' => $em,));

			
			if ($request->isMethod('POST')) 
			{
			//print("POST");
				$temp= $request->get('appbundle_ddocument_medium');
				echo "<script>console.log('$temp' );</script>";
				$form->handleRequest($request);
				echo "<script>console.log('avantvalid' );</script>";

				if ($form->isSubmitted() && $form->isValid()) {
					try {
						//print("submit");
						//echo "<script>console.log('dans valid' );</script>";
						$keywords_pk=$request->get("widget_keywords_pk",null);
						$keywords=$request->get("widget_keywords",null);
						if($keywords!==null)
						{
							$array_keywords=[];
							$i=0;
							foreach($keywords as $keyw)
							{
								$keyobj=new Dkeyword();
								$keyobj->setKeyword($keyw);
								$keyobj->setKeywordlevel($i+1);
								$keyobj->setIdcollection($ddocument->getIdCollection());
								$keyobj->setId($ddocument->getIddoc());
								if(array_key_exists($i,$keywords_pk ))
								{
									if(is_numeric($keywords_pk[$i]))
									{
										$keyobj->setPk($keywords_pk[$i]);
									}
								}
								$i++;
								$array_keywords[]= $keyobj;
								
							}
							
							$keywords=$ddocument->initNewDkeywords($em, $array_keywords);
							//throw new UndefinedOptionsException();
						}
						
						
						$titles=$request->get("widget_titles",null);
						$titles_pk=$request->get("widget_titles_pk",null);
						//print_r($titles_pk);
						if($titles!==null)
						{
							$array_titles=Array();
							$i=0;
							foreach($titles as $title)
							{
								$titleobj=new Ddoctitle();
								$titleobj->setTitle($title);
								$titleobj->setTitlelevel($i+1);
								$titleobj->setIdcollection($ddocument->getIdCollection());
								$titleobj->setIddoc($ddocument->getIddoc());
								if(array_key_exists($i,$titles_pk ))
								{
									if(is_numeric($titles_pk[$i]))
									{
										$titleobj->setPk($titles_pk[$i]);
									}
								}
								$i++;
								$array_titles[]= $titleobj;
							}
							$titles=$ddocument->initNewDdoctitles($em, $array_titles);
							
							
							
							
							//throw new UndefinedOptionsException();
						}
						
						$scale_pk=$request->get("widget_scale_pk",null);
						$scale=$request->get("widget_scale",null);
						
						if($scale!==null)
						{
							$array_scale=[];
							$i=1;
							foreach($scale as $sc)
							{
								$keyobj=new Ddocscale();
								$keyobj->setScale($sc);
								$keyobj->setIdcollection($ddocument->getIdCollection());
								$keyobj->setIddoc($ddocument->getIddoc());
								if(array_key_exists($i,$scale_pk ))
								{
									
									if(is_numeric($scale_pk[$i]))
									{
										$keyobj->setPk($scale_pk[$i]);
									}
								}
								
								$array_scale[$i]= $keyobj;
								$i++;
								
								
							}
							
							$scale=$ddocument->initNewDdocscale($em, $array_scale);
							//throw new UndefinedOptionsException();
						}
						
						$film_pk=$request->get("widget_film_pk",null);
						$film=$request->get("widget_film",null);
						
						if($film!==null)
						{
							$array_film=[];
							$i=1;
							foreach($film as $fl)
							{
								$keyobj=new Ddocfilm();
								$keyobj->setFilm($fl);
								$keyobj->setIdcollection($ddocument->getIdCollection());
								$keyobj->setIddoc($ddocument->getIddoc());
								if(array_key_exists($i,$film_pk ))
								{
									if(is_numeric($film_pk[$i]))
									{
										$keyobj->setPk($film_pk[$i]);
									}
								}
								
								$array_film[]= $keyobj;
								$i++;
								
							}
							
							$film=$ddocument->initNewDdocfilm($em, $array_film);
							//throw new UndefinedOptionsException();
						}
						$dlinkcontribute=$this->handle_many_to_many_relation_general(
							$request,
							$ddocument,
							"doc_to_contrib_id_contrib_",
							Dcontribution::class,
							"pk",
							Dlinkcontdoc::class,
							Array("getIdcontribution"=>"setIdcontribution"),
							Array(),
							array("setrelationidcollection"=>$ddocument)
							);
						if($dlinkcontribute!==null)
						{
							if(count($dlinkcontribute)>0)
							{
								//print("EDIT_TEST");
								$ddocument->initNewDLinkcontdocs($em, $dlinkcontribute);
								//reattach after update
								
							//throw new UndefinedOptionsException();
							}
						}
						//doc to point
						$dlinkdocloc=$this->handle_many_to_many_relation_general(
							$request,
							$ddocument,
							"doc_to_loc_id_dloccenter_",
							DLoccenter::class,
							"pk", 
							Dlinkdocloc::class,
							["getIdcollection"=> "setIdcollecloc","getIdpt"=>"setIdpt" ],
							["doc_to_loc_id_link_"=>"setPk"],
							["setrelationidcollection"=> $ddocument]
							
						);
						if($dlinkdocloc!==null)
						{
							if(count($dlinkdocloc)>0)
							{
								//print("EDIT_TEST");
								$ddocument->initNewDLinkdocloc($em, $dlinkdocloc);
								//reattach after update
								
							//throw new UndefinedOptionsException();
							}
						}
						
						
						$ddocaerphoto=$this->handle_many_to_many_relation_general(
							$request,
							$ddocument,
							"doc_to_flightplan_id_flightplan_",
							Docplanvol::class,
							"pk",
							Ddocaerphoto::class,
							Array("getFid"=>"setFid"
							),
							Array("doc_to_flightplan_pid_"=>"setPid","doc_to_flightplan_pk_"=>"setPk"),
							array("setIdCollection"=>$ddocument->getIdCollection(), "setIdDoc"=> $ddocument->getIddoc())
							);
						if($ddocaerphoto!==null)
						{
							if(count($ddocaerphoto)>0)
							{
								//print("EDIT_TEST");
								$ddocument->initNewDdocaerphoto($em, $ddocaerphoto);
								//reattach after update
								
							//throw new UndefinedOptionsException();
							}
						}
						$satellite_http=$request->get("sat_satnum",null);
						if($satellite_http!==null)
						{
							//print("save_sat");
							//date not handled
							$satellites=$this->getHTTPOneToManyByIndex($request, "sat_satnum",Ddocsatellite::class,
								[
									"sat_satpk"=>"setPk",
									"sat_satnum"=>"setSatnum",
									"sat_sattype"=>"setSattype",
									"sat_sensor"=>"setSensor",
									"sat_moderadar"=>"setModeradar",
									"sat_orbit"=>"setOrbit",
									"sat_pathtrack"=>"setPathtrack",
									"sat_rowframe"=>"setRowframe",
								],
								["setIdcollection"=>$ddocument->getIdcollection(), "setIddoc"=>$ddocument->getIddoc(),]
								
								);
							//print_r($satellites);
							if(count($satellites)>0)
							{
								$ddocument->initNewDdocsatellite($em, $satellites);
							}
						}
						$map_http=$request->get("map_projection",null);
						if($map_http!==null)
						{
							
							//date not handled
							$maps=$this->getHTTPOneToManyByIndex($request, "map_projection",Ddocmap::class,
								[
									"map_mappk"=>"setPk",
									"map_projection"=>"setProjection",
									"map_sheetnumber"=>"setSheetnumber",
									
								],
								["setIdcollection"=>$ddocument->getIdcollection(), "setIddoc"=>$ddocument->getIddoc(),]
								,
								["map_oncartesius"=>"setOncartesius",]
								);
							//print_r($satellites);
							if(count($maps)>0)
							{
								$ddocument->initNewDdocmap($em, $maps);
							}
						}
						$archive_http=$request->get("archive_set",null);
						if($archive_http)
						{
							$archives=$this->getHTTPOneToManyByIndex($request, "archive_pk",Ddocarchive::class,
								[
									"archive_pk"=>"setPk",
									"archive_extension"=>"setExtension",
									"archive_sample"=>"setSample",
									"archive_yearlow"=>"setYearlow",
									"archive_yearhigh"=>"setYearhigh",
									
								],
								["setIdcollection"=>$ddocument->getIdcollection(), "setIddoc"=>$ddocument->getIddoc(),]
								,
								[
									"archive_geology"=>"setGeology",
									"archive_geochemisty"=>"setGeochemistry",
									"archive_geophysics"=>"setGeophysics",
									"archive_exploration"=>"setExporation",
									"archive_production"=>"setProduction",
									"archive_reserves"=>"setReserves",
									"archive_exploitation"=>"setExploitation",
									"archive_processing"=>"setProcessing",
									"archive_management"=>"setManagement",
									"archive_report"=>"setReport",
									"archive_drillingcores"=>"setDrilingcores",
									"archive_maps"=>"setMaps",
									"archive_paleontology"=>"setPaleontology",
									"archive_sedimentology"=>"setSedimentology",
									"archive_economy"=>"setEconomy",
									"archive_sgeology"=>"setSgeology",
									"archive_smineralogy"=>"setSmineralogy",
									"archive_spaleontology"=>"setSpaleontology",
									"archive_sconcentre"=>"setSconcentre",
								]
								);
							if(count($archives)>0)
							{
								$ddocument->initNewDdocarchive($em, $archives);
							}
						}
						
						//$dlinkcontribute=$ddocument->initDLinkcontdoc($em);	
						$em->flush();
						//print("DONE");
						
						$this->addFlash('success','DATA RECORDED IN DATABASE!');  
						
						
						
					}catch(\Doctrine\DBAL\DBALException $e ) {
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
					
				}elseif ($form->isSubmitted() && !$form->isValid() ){
					//echo "<script type='text/javascript'>alert('error in form');</script>";
				}
			}
			
			$rightoncollection1 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('1',['Curator','Validator','Collection_manager']); //'Viewer'
			$rightoncollection2 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('2',['Curator','Validator','Collection_manager']);
			$rightoncollection3 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('3',['Curator','Validator','Collection_manager']);
			$rightoncollection4 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('7',['Curator','Validator','Collection_manager']);
			$rightoncollection5 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('9',['Curator','Validator','Collection_manager']);
			if ($rightoncollection1 == true && $rightoncollection2 == true && $rightoncollection3 == true && $rightoncollection4 == true && $rightoncollection5 == true)
			{				
				$current_tab=$this->get_request_var($request, "current_tab", "main-tab");
				//print("request_tab[$current_tab]");
				//$mode=$this->enable_read_write($request);
				$logs=$em->getRepository(TDataLog::class)
						 ->findContributions("ddocument", $ddocument->getPk());
				$this->validate_form($request, $em,"ddocument",$ddocument->getPk());
				return $this->render('@App/documents/editdoc.html.twig', array(
					'ddocument' => $ddocument,
					'form' => $form->createView(),
					'origin'=>'edit',
					'originaction'=>'edit',
					'current_tab'=> $current_tab,
					'keywords'=>$keywords,
					'titles'=>$ddocument->initDdoctitles($em),
					'dlinkcontdoc'=>$ddocument->initDLinkcontdoc($em),
					'dlinkdocloc'=>$ddocument->initDLinkdocloc($em),
					'ddocaerphoto'=>$ddocument->initDdocaerphoto($em),
					'satellites'=>$ddocument->initDdocsatellite($em),
					'scales'=>$ddocument->initDdocscale($em),
					'films'=>$ddocument->initDdocfilm($em),
					'maps'=>$ddocument->initDdocmap($em),
					'archives'=>$ddocument->initDdocarchive($em),
					'logs'=>$logs,
					'read_mode'=>$this->enable_read_write($request)
				));
			}else{
				return $this->render('@App/collnoaccess.html.twig');
			}
		}
    }
	

	
	public function errorcollectionAction(){
		return $this->render('@App/collnoaccess.html.twig');
	}
	
	public function viewsampleAction(Dsample $dsample){
		$grades[]=null;
		$arraylminerals[]=null;
		$arraydsammagsusc[]=null;
		$arraydsamgranulo[]=null;
		$arraysamheavymin[]=null;
		$arraydcontributors[]=null;
		$idcol = $dsample->getIdcollection();
		$idsamp = $dsample->getIdsample();
		$em = $this->getDoctrine()->getManager();
		
		//minerals------
		$sammineral = $this->getDoctrine()
        ->getRepository(Dsamminerals::class)
		 ->findBy(array('idcollection' => $idcol, 
						'idsample' => $idsamp
					   ));
	
		foreach($sammineral as $dsammineral_obj){
			$grades[]=$dsammineral_obj->getGrade();
			$lminerals = $this->getDoctrine()
			->getRepository(Lminerals::class)
			->findBy(array(	'idmineral' => $dsammineral_obj->getIdMineral()	));
			$arraylminerals[]=$lminerals;
		}
		
		//Magnetic susc----
		$arraydsammagsusc = $this->getDoctrine()
        ->getRepository(DSamMagSusc::class)
		 ->findBy(array('idcollection' => $idcol, 
						'idsample' => $idsamp
					   ));
					   
		//granulo----
		$arraydsamgranulo = $this->getDoctrine()
        ->getRepository(Dsamgranulo::class)
		 ->findBy(array('idcollection' => $idcol, 
						'idsample' => $idsamp
					   ));
					   
		//heavyminerals----
		$arraysamheavymin = $this->getDoctrine()
		->getRepository(Dsamheavymin::class)
		 ->findBy(array('idcollection' => $idcol, 
						'idsample' => $idsamp
					   ));
					   
		//heavyminerals2----
		$RAW_QUERY = "SELECT * FROM dsamheavymin2 where idcollection = '".$idcol."' AND idsample = ".$idsamp.";";
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->execute();
		$arraysamheavymin2 = $statement->fetchAll();
		//return new Response('<html><body>'.print_r($arraysamheavymin2).'</body></html>' );
		
		//Contributions------
		$samcontribution = $this->getDoctrine()
		->getRepository(DLinkContSam::class)
		 ->findBy(array('idcollection' => $idcol, 
						'id' => $idsamp
					   ));
		
		foreach($samcontribution as $samcontribution_obj){
			$idcontrib = $samcontribution_obj->getIdContribution()->getIdContribution();
			
			$idscontr_originals[]=$samcontribution_obj->getIdContribution()->getIdContribution();
			$RAW_QUERYdcontributions = "SELECT c.pk as pkcontribution,
						c.idcontribution as idcontribution,
						datetype,
						year, 
						date,			
						o.pk as pkcontributor,
						o.idcontributor as idcontributor,
						people,
						peoplefonction,
						peopletitre,
						peoplestatut,
						institut,
						contributorrole,
						contributororder
					FROM dcontribution c
					LEFT JOIN dlinkcontribute l ON l.idcontribution = c.idcontribution
					LEFT JOIN dcontributor o ON l.idcontributor = o.idcontributor
					WHERE c.idcontribution = ".$idcontrib."
					ORDER by idcontribution;";
			$statement = $em->getConnection()->prepare($RAW_QUERYdcontributions);
			$statement->execute();
			$arraydcontributors1 = $statement->fetchAll();
			
			if ($arraydcontributors != null){
				array_push($arraydcontributors,$arraydcontributors1);
			}else{
				$arraydcontributors = $arraydcontributors1;
			}
		}
						   
		return $this->render('@App/viewsample.html.twig', array(
            'dsample' => $dsample,
			'grades' => $grades,
			'arraylminerals' => $arraylminerals,
			'arraydsammagsusc' => $arraydsammagsusc,
			'arraydsamgranulo' => $arraydsamgranulo,
			'arraysamheavymin' => $arraysamheavymin,
			'arraysamheavymin2' => $arraysamheavymin2,
			'arrayLinkcontributions' => $arraydcontributors,
        ));
    }
	
	public function viewmineralAction(Lminerals $lminerals){
		return $this->render('@App/viewmineral.html.twig', array(
            'lminerals' => $lminerals,
        ));
    }
	
	public function viewcontributionAction(Dcontribution $dcontribution){
		$em = $this->getDoctrine()->getManager();
		$roles[]=null;
		$orders[]=null;
		$arraydcontributors[]=null;
		$dcontributors[]=null;
		$idcontr = $dcontribution->getIdcontribution();
		
		//contributors------
		$RAW_QUERY = "SELECT * FROM dlinkcontribute where idcontribution = ".$idcontr.";";
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->execute();
		$linkcontribute = $statement->fetchAll();
	
		foreach($linkcontribute as $linkcontribute_obj){
			$roles[]=$linkcontribute_obj["contributorrole"];
			$orders[]=$linkcontribute_obj["contributororder"];
			$dcontributors = $this->getDoctrine()
			->getRepository(Dcontributor::class)
			->findBy(array(	'idcontributor' => $linkcontribute_obj["idcontributor"]	));
			$arraydcontributors[]=$dcontributors;
		}
				
		//
		if(!isset($arraydcontributors[1])){
			//return new Response('<html><body>null'.print_r($arraydcontributors).'</body></html>' );
			return $this->render('@App/viewcontribution.html.twig', array(
				'dcontribution' => $dcontribution,
			));
		}else{
		//	return new Response('<html><body>not null'.print_r($arraydcontributors).'</body></html>' );
			return $this->render('@App/viewcontribution.html.twig', array(
				'dcontribution' => $dcontribution,
				'roles' => $roles,
				'orders' => $orders,
				'arraycontributors' => $arraydcontributors
			));
		}
    }
	
	public function viewdocAction(Ddocument $ddocuments){
		$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('1,2,3,7,9',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
		if ($rightoncollection == true){
			return $this->render('@App/viewdoc.html.twig', array(
				'ddocument' => $ddocuments,
			));
		}else{
			return $this->render('@App/collnoaccess.html.twig');
		}
    }
	
	public function viewpointAction(Dloccenter $dloccenter){
		$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('4,15,26',['Curator','Validator','Encoder','Collection_manager','Viewer']); 
		if ($rightoncollection == true){
			return $this->render('@App/viewpoint.html.twig', array(
				'dloccenter' => $dloccenter,
			));
		}else{
			return $this->render('@App/collnoaccess.html.twig');
		}
    }
	
	public function viewsammineralsAction(Dsamminerals $dsamminerals){
		return $dsamminerals;
	}
	
	
	
	public function listcodecollectionAction($querygroup,Request $request){
		$em = $this->getDoctrine()->getManager();
        $RAW_QUERY = "SELECT * FROM codecollection where codecollection.zoneutilisation LIKE '".$querygroup."';";
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $codescollection = $statement->fetchAll();
        
        return new JsonResponse($codescollection);
    }
	
	public function listdocmediumAction(Request $request){
		$em = $this->getDoctrine()->getManager();
        $RAW_QUERY = "SELECT medium FROM lmedium;";
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $media = $statement->fetchAll();
        
        return new JsonResponse($media);
    }
	
	public function getallcoll_users(){
		$em = $this->getDoctrine()->getManager();
		$RAW_QUERY = "SELECT 	uc.pk as uc_pk,
								c.pk as c_pk,
								u.id as u_id,
								uc.collection_id as ID_Collection, 
								uc.user_id as ID_User, 
								codecollection,  
								collection,  
								typeobjets,  
								zoneutilisation,  
								username,  
								first_name,  
								last_name,  
								email, 
								CASE 
								  WHEN enabled = 't' THEN 'yes'
								  WHEN enabled = 'f'  THEN 'no'
								  ELSE ''
								END as enabled, 
								uc.role
					FROM codecollection c
					left join fos_user_collections uc on c.pk = uc.collection_id
					left join duser u on u.id = uc.user_id
					order by codecollection;";
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $codescollection = $statement->fetchAll();
		return $codescollection;
	}
	
	public function listcollectionAction(Request $request){
		$codescollection = $this->container->get('AppBundle\Controller\GeoController')->getallcoll_users();

        return $this->render('@App/collectionindex.html.twig', array(
            'collections' => $codescollection,
        ));
    }
	
	public function editcollectionAction(Request $request, Codecollection $codecollection) {
		if (!$codecollection) {
			throw $this->createNotFoundException('No collection found' );
		}else{
			$arrayusers[] = null;
			
			$em = $this->getDoctrine()->getManager();
			$editForm = $this->createForm(CodecollectionEditType::class, $codecollection); 
			
			$idcoll = $codecollection->getPk();
			$RAW_QUERYusers = "SELECT * FROM fos_user_collections where collection_id = ".$idcoll.";";
			$statement = $em->getConnection()->prepare($RAW_QUERYusers);
			$statement->execute();
			$arraylinkusers = $statement->fetchAll();
			
			$RAW_QUERYroles = "SELECT * FROM fos_role;";
			$statement = $em->getConnection()->prepare($RAW_QUERYroles);
			$statement->execute();
			$arrayroles = $statement->fetchAll();
		
			if (count($arraylinkusers) > 0 ){
				foreach($arraylinkusers as $arraylinkusers_obj){
					$Iduser = $arraylinkusers_obj["user_id"];
					
					if ($Iduser == null){
						$actionlinkuser = "I";
					}else{
						$actionlinkuser = "U";
					}
				}
				$RAW_QUERYIduser = "SELECT 	uc.pk as uc_pk,
								c.pk as c_pk,
								u.id as u_id,
								uc.collection_id as ID_Collection, 
								uc.user_id as ID_User, 
								codecollection,  
								collection,  
								typeobjets,  
								zoneutilisation, 
								id,  
								username,  
								first_name,  
								last_name,  
								email, 
								CASE 
								  WHEN enabled = 't' THEN 'yes'
								  WHEN enabled = 'f'  THEN 'no'
								  ELSE ''
								END as enabled, 
								uc.role
					FROM fos_user_collections uc
					left join codecollection c on c.pk = uc.collection_id
					left join duser u on u.id = uc.user_id
					where uc.collection_id = '".$idcoll."';";
					//"SELECT * FROM duser u left join fos_user_collections uc on uc.user_id = u.id where uc.collection_id = '".$idcoll."';";
						
				$statement = $em->getConnection()->prepare($RAW_QUERYIduser);
				$statement->execute();
				$arrayusers = $statement->fetchAll();
			
				foreach($arrayusers as $arrayusers_obj){
					$idusers[]=$arrayusers_obj['u_id'];
				}
			}else{
				$actionlinkuser = "I";
			}
					
			if ($request->isMethod('POST')) {
				$editForm->handleRequest($request);
				if ($editForm->isSubmitted() && $editForm->isValid()) {
					try {
						$em->persist($codecollection);
						$em->flush();

						$m=0;
						$idcoll = $codecollection->getPk();
						if ($idcoll != ""){	
							$actionsusersstr = $request->get('newcontributors');
							$this->saveusers($idcoll,$actionsusersstr,$request);
						}  
						
						$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
					
						return $this->redirectToRoute('app_edit_collection', array('id' => $codecollection->getPk()));
					
						//return $this->redirectToRoute('app_list_collections');
					} catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
						echo "<script type='text/javascript'>alert('Record already exists with these values !');</script>";
					} catch(\Doctrine\DBAL\Exception\ConstraintViolationException $e ) {
						echo "<script type='text/javascript'>alert('There is a constraint violation with that transaction !');</script>";
					} catch(\Doctrine\DBAL\Exception\TableNotFoundException $e ) {
						echo "<script type='text/javascript'>alert('Table not found !');</script>";
					} catch(\Doctrine\DBAL\Exception\ConnectionException $e ) {
						echo "<script type='text/javascript'>alert('Problem of connection with database !');</script>";
					} catch(\Doctrine\DBAL\Exception\DriverException $e ) {
						echo "<script type='text/javascript'>alert('There is a syntax error with one field !');</script>";
					}
				}elseif ($editForm->isSubmitted() && !$editForm->isValid() ){
					echo "<script type='text/javascript'>alert('error in form');</script>";
				}
			}
		
			if(!isset($arrayusers[0])){
				return $this->render('@App/collectionedit.html.twig', array(
				'codecollection' => $codecollection,
				'arrayroles' => $arrayroles,
				'edit_form' => $editForm->createView(),
				'originaction'=>'edit'
				));
			}else{
				return $this->render('@App/collectionedit.html.twig', array(
					'codecollection' => $codecollection,
					'arrayroles' => $arrayroles,
					'edit_form' => $editForm->createView(),
					'arraylinkusers' => $arraylinkusers,
					'arraycollusers' => $arrayusers,
					'originaction'=>'edit'
				));
			}
		}
	}
	
	public function listHMAction(Request $request){
		$em = $this->getDoctrine()->getManager();

        $RAW_QUERY = "SELECT mineral FROM DSamHeavyMin2 GROUP BY mineral ORDER BY mineral;";
        
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $minclasses = $statement->fetchAll();
        
        return new JsonResponse($minclasses);
    }
	
	public function listmineralsAction($querygroup,Request $request){
		$em = $this->getDoctrine()->getManager();

        $RAW_QUERY = "SELECT * FROM lminerals where rank = '".$querygroup."' ORDER BY rank,fmname;";
        
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $minclasses = $statement->fetchAll();
        
        return new JsonResponse($minclasses);
    }
	
	public function listmineralparentsAction(Request $request){
		$em = $this->getDoctrine()->getManager();

      	$RAW_QUERY = "SELECT mparent from (SELECT DISTINCT mparent FROM lminerals where mparent != ''  UNION SELECT DISTINCT fmparent FROM lminerals where fmparent != '' AND fmparent != '-') as a ORDER BY a.mparent ;";
        
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $minclasses = $statement->fetchAll();
        
        return new JsonResponse($minclasses);
    }
	
	public function listlithomineralsAction(Request $request){
		$em = $this->getDoctrine()->getManager();

        $RAW_QUERY = "SELECT DISTINCT mineral FROM dsamheavymin2 ORDER BY mineral;";
        
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $minclasses = $statement->fetchAll();
        
        return new JsonResponse($minclasses);
    }
	

	
	public function DataContributionsAction(Request $request){
		$em = $this->getDoctrine()->getManager();		
		$idcontrib = $_GET['code'];
		$RAW_QUERY = "SELECT c.pk as pkcontribution,
							c.idcontribution as idcontribution,
							datetype,
							year, 
							date,			
							o.pk as pkcontributor,
							o.idcontributor as idcontributor,
							people,
							peoplefonction,
							peopletitre,
							peoplestatut,
							institut,
							contributorrole,
							contributororder
						FROM dcontribution c
						LEFT JOIN dlinkcontribute l ON l.idcontribution = c.idcontribution
						LEFT JOIN dcontributor o ON l.idcontributor = o.idcontributor
						WHERE c.idcontribution = ".$idcontrib."
						ORDER by people;";
//return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>');
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $alldata = $statement->fetchAll();
		
        return new JsonResponse($alldata);
    }	
    
	
	public function contribdata_comboboxAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$code = strtolower($_GET['code']);
		switch ($code) { 
			case ($code==1):
				$RAW_QUERY = "SELECT DISTINCT datetype as valdata FROM dcontribution ORDER BY datetype;"; 
				break;
			case ($code==2):
				$RAW_QUERY = "SELECT DISTINCT people as valdata FROM dcontributor ORDER BY people;"; 
				break;
			case ($code==3):
				$RAW_QUERY = "SELECT DISTINCT contributorrole as valdata FROM dlinkcontribute ORDER BY contributorrole;"; 
				break;
			case ($code==4):
				$RAW_QUERY = "SELECT DISTINCT peoplefonction as valdata FROM dcontributor ORDER BY peoplefonction;"; 
				break;
			case ($code==5):
				$RAW_QUERY = "SELECT DISTINCT peopletitre as valdata FROM dcontributor ORDER BY peopletitre;"; 
				break;
			case ($code==6):
				$RAW_QUERY = "SELECT DISTINCT peoplestatut as valdata FROM dcontributor ORDER BY peoplestatut;"; 
				break;
			case ($code==7):
				$RAW_QUERY = "SELECT DISTINCT institut as valdata FROM dcontributor ORDER BY institut;"; 
				break;
			case ($code==8):
				$RAW_QUERY = "SELECT DISTINCT year as valdata FROM dcontribution ORDER BY year;";
				break;
		};
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $types = $statement->fetchAll();
        
        return new JsonResponse($types);
    }
	
	public function docsdata_comboboxAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$code = strtolower($_GET['code']);
		switch ($code) { 
			case ($code==1):
				$RAW_QUERY = "SELECT DISTINCT medium as valdata FROM ddocument ORDER BY medium;"; 
				break;
			case ($code==2):
				$RAW_QUERY = "SELECT DISTINCT doccartotype as valdata FROM ddocument ORDER BY doccartotype;"; 
				break;
		};
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $types = $statement->fetchAll();
        
        return new JsonResponse($types);
    }   

	
	public function LastSampleIDAction($querygroup,Request $request){
		$id = 0;
		$em = $this->getDoctrine()->getManager();
		$RAW_QUERY = "SELECT idsample FROM dsample WHERE idcollection = '".$querygroup."' ORDER BY idsample DESC LIMIT 1;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $id = $statement->fetchAll();
		
		return new JsonResponse($id);
	}
	
	public function LastLocationIDAction($querygroup,Request $request){
		$id = 0;
		$em = $this->getDoctrine()->getManager();
		$RAW_QUERY = "SELECT idpt FROM dloccenter WHERE idcollection = '".$querygroup."' ORDER BY idpt DESC LIMIT 1;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $id = $statement->fetchAll();
		
		return new JsonResponse($id);
	}
	
	public function LastDocumentIDAction($querygroup,Request $request){
		$id = 0;
		$em = $this->getDoctrine()->getManager();
		$RAW_QUERY = "SELECT iddoc FROM ddocument WHERE idcollection = '".$querygroup."' ORDER BY iddoc DESC LIMIT 1;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $id = $statement->fetchAll();
		
		return new JsonResponse($id);
	}
	
	public function LastcontribidAction(Request $request){
		$id = 0;
		$em = $this->getDoctrine()->getManager();
		$RAW_QUERY = "SELECT idcontribution FROM dcontribution ORDER BY idcontribution DESC LIMIT 1;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $id = $statement->fetchAll();
		
		return new JsonResponse($id);
	}
	
	public function LastcontributoridAction(Request $request){
		$id = 0;
		$em = $this->getDoctrine()->getManager();
		$RAW_QUERY = "SELECT idcontributor FROM dcontributor ORDER BY idcontributor DESC LIMIT 1;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $id = $statement->fetchAll();
		
		return new JsonResponse($id);
	}
	
	public function LastMineralIDAction(Request $request){
		$id = 0;
		$em = $this->getDoctrine()->getManager();
		$RAW_QUERY = "SELECT idmineral FROM lminerals ORDER BY idmineral DESC LIMIT 1;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $id = $statement->fetchAll();
		
		return new JsonResponse($id);
	}	
	
	
	// note
	//sudo "php bin/console doctrine:generate:form AppBundle:Dcontributor"
	//to create the form entity from console
	public function addcontributorAction(Request $request)
	{
	    $this->set_sql_session();	
		return $this->add_general(Dcontributor::class, DcontributorType::class,$request,'@App/addcontributor.html.twig', 'app_edit_contributor' );		
	}
	
	public function editcontributorAction(Dcontributor $dcontributor, Request $request)
	{	
	//print("test");
		$this->set_sql_session();		
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(DcontributorType::class, $dcontributor, array('entity_manager' => $em,));		
		return $this->edit_general($dcontributor, $form,"Dcontributor", $request, '@App/addcontributor.html.twig', 'dcontributor',$dcontributor->getIdContributor() );
    }
	
	
	
	
	public function addplanvolAction(Request $request)
	{
	    $this->set_sql_session();	
		return $this->add_general(Docplanvol::class, DocplanvolType::class,$request,'@App/addplanvol.html.twig', 'app_edit_flightplan' );		
	}
	
	public function editplanvolAction(Docplanvol $docplanvol, Request $request)
	{	
	//print("test");
		$this->set_sql_session();		
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(DocplanvolType::class, $docplanvol, array('entity_manager' => $em,));		
		return $this->edit_general($docplanvol, $form,"docplanvol", $request, '@App/addplanvol.html.twig', 'docplanvol',$docplanvol->getPk()		);
    }
	
	
	
    
	
	public function editmineralgsAction(Lminerals $mineral, Request $request)
	{  
	
		$this->set_sql_session();
		if (!$dsample) {
			throw $this->createNotFoundException('No location found' );
		}
		else
		{
			$em = $this->getDoctrine()->getManager();
			$this->set_sql_session();
			
			$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('4,15,26',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
			if ($rightoncollection == true)
			{
				$logs=$em->getRepository(TDataLog::class)
						 ->findContributions("lmineral", $mineral->getPk());
				
				$current_tab=$this->get_request_var($request, "current_tab");

				return $this->render("@App/mineral/editmineral.html.twig", array(
						'limineral' => $mineral,
						'sample_form' => $form->createView(),
						'origin'=>'edit',
						'originaction'=>'edit',
						'current_tab'=>$current_tab,
						'logs'=>$logs,
						'read_mode'=>$this->enable_read_write($request)
				
				));
			}
			else
			{
				return $this->render('@App/collnoaccess.html.twig');
			}
		}
	}
	
	
}


