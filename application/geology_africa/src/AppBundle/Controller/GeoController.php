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
use AppBundle\Entity\Dsamheavymin;
use AppBundle\Entity\Dsamheavymin2;
use AppBundle\Entity\Dcontribution;
use AppBundle\Entity\Dcontributor;
use AppBundle\Entity\Dlinkcontribute;
use AppBundle\Entity\Dlinkcontsam;
use AppBundle\Entity\Dlinkcontloc;
use AppBundle\Entity\Dlocdrilling; 
use AppBundle\Entity\DLoclitho;
use AppBundle\Form\DsampleType;
use AppBundle\Form\DsampleEditType;
use AppBundle\Form\LmineralsType;
use AppBundle\Form\LmineralsEditType;
use AppBundle\Form\DcontributionType;
use AppBundle\Form\DcontributionEditType;
use AppBundle\Form\DdocumentEditType;
use AppBundle\Form\DdocumentType;
use AppBundle\Form\PointType;
use AppBundle\Form\PointEditType;
use AppBundle\Form\StratumType;
use AppBundle\Form\CodecollectionEditType;
use AppBundle\Form\CollectionType;
use AppBundle\Form\EntityManager;
use AppBundle\Form\SearchAllForm;

use AppBundle\Entity\Dkeyword;

use Symfony\Component\Form\FormError;

//debug 
use Symfony\Component\HttpFoundation\Response;

class GeoController extends Controller
{
	
	protected $page_size=20;
    protected $limit_autocomplete=30;
	
    public function indexAction(Request $request){
		
		return $this->render('@App/home.html.twig');
		$this->set_sql_session();
    }
	
	/*
	protected function handle_subform($entityManager, $fk_obj, $array_params)
	{
		foreach( $array_params as $method=>$val)
		{
			print($method);
			 call_user_func_array(array($fk_obj, $method ), array($val));
		}
		
		$entityManager->persist($fk_obj);
	}
	
	
	protected function handle_subform_main($entityManager, $form_vars, $pk_obj, $fk_obj,$fk_class_name, $obj_params,$additional_params, $val_method)
	{
		if($form_vars!==null)
		{
			foreach($form_vars as $val)
			{
				//$reflection = new $fk_class_name(); 
				$params=Array();
				
				foreach( $obj_params as $fk_side)
				{
					
					//call_user_func_array(array($reflection, $fk_side ), array($pk_obj));
				}
				foreach( $additional_params as $fk_side=> $val2)
				{
					$params[$fk_side]=$val2;
				}
				$params[$val_method]=$val;
				$this->handle_subform($entityManager,$reflection, $params);
			}
		}
	}*/
	
	protected function set_sql_session()
	{
		$em = $this->getDoctrine()->getManager();		
		$uk=$this->getUser()->getId();
		$RAW_QUERY = "SET  session.geo_darwin_user TO ".$uk.";";
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->execute(); 
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
	
	public function savecontributors($idcontrib,$actionscontribstr,$request){
		//return new Response('<html><body>'.print_r($actionscontribstr).print_r($actionscontribstr).'</body></html>' );
		$actionscontrib[] = null;
		$idcontribs[] = null;
		$idcontributors[]= null;
		$origin_error = "In contributors, ";
		$em = $this->getDoctrine()->getManager();				
						
		if ($actionscontribstr != ""){
			
			$arrayid_contrids =  explode(",", $actionscontribstr); 
			$elem =array();
			$i=0;
			foreach($arrayid_contrids as $e) {
				if ($e != ""){
					$elem = explode("-", $e);
					$idline[$i] = $elem[0];
					$idcontribs[$i] = $elem[1];
					$actionscontrib[$i] = $elem[2];
					$i++;
				}
			} 
		}
		
		$RAW_QUERYcontributors = "SELECT * FROM dlinkcontribute where idcontribution = '".$idcontrib."';";
		$statement = $em->getConnection()->prepare($RAW_QUERYcontributors);
		$statement->execute();
		$linkcontribs = $statement->fetchAll();
		foreach($linkcontribs as $linkcontrib_obj){
			$idcontributors[]=$linkcontrib_obj['idcontributor'];
		}
		
		$RAW_QUERYcontributors = "SELECT idcontributor FROM dcontributor;";
		$statement = $em->getConnection()->prepare($RAW_QUERYcontributors);
		$statement->execute();
		$contributors = $statement->fetchAll();
		foreach($contributors as $contributors_obj){
			$idallcontributors[]=$contributors_obj['idcontributor'];
		}
		
		for ($y = 0; $y < sizeof($actionscontrib); $y++) {
			if ($idcontribs[$y]!= ""){
				if ($actionscontrib[$y] != "D"){
					$actionscontrib[$y] = "I";
					$role = $request->get('inp_addnewcontributorRole'.$idline[$y]);
					$order = $request->get('inp_addnewcontributorOrder'.$idline[$y]);
					if ($order == ""){
						$order = "0";
					}
					$people = $request->get('inp_addnewcontributorName'.$idline[$y]);
					$function = $request->get('inp_addnewcontributorFunction'.$idline[$y]);
					$title = $request->get('inp_addnewcontributorTitle'.$idline[$y]);
					$statut = $request->get('inp_addnewcontributorStatus'.$idline[$y]);
					$institut = $request->get('inp_addnewcontributorInstitut'.$idline[$y]);
					for ($z = 0; $z < count($idcontributors); $z++) {
						if($idcontributors[$z] == $idcontribs[$y]){
								$actionscontrib[$y] = "U";
								break;
						}
					}
					
					$actionscontributor[$y] = "I";
					for ($z = 0; $z < count($idallcontributors); $z++) {
						if($idallcontributors[$z] == $idcontribs[$y]){
								$actionscontributor[$y] = "U";
								break;
						}
					}
				}
				
				if ($actionscontributor[$y] == "U"){
					$RAW_QUERYdcontributor = "UPDATE dcontributor SET people = '".$people."',peoplefonction = '".$function."',peopletitre = '".$title."',peoplestatut = '".$statut."',institut = '".$institut."' WHERE idcontributor = ".$idcontribs[$y].";";
					
				}   
				if ($actionscontributor[$y] == "I"){
					$RAW_QUERYdcontributor = "INSERT INTO dcontributor (idcontributor, people, peoplefonction, peopletitre, peoplestatut, institut) VALUES (".$idcontribs[$y].",'".$people."','".$function."','".$title."','".$statut."','".$institut."');";
				}

				if ($actionscontributor[$y] == "I" | $actionscontributor[$y] == "U"){
					//echo "<script type='text/javascript'>alert('RAW_QUERYdcontributor= $RAW_QUERYdcontributor');</script>";
					$statement = $em->getConnection()->prepare($RAW_QUERYdcontributor);
					$statement->execute();
				}
//return new Response('<html><body>'.print_r($RAW_QUERYdcontributor).'</body></html>' );
				
				if ($actionscontrib[$y] == "U"){
					$RAW_QUERY = "UPDATE dlinkcontribute SET contributorrole = '".$role."',contributororder = ".$order." WHERE idcontribution = ".$idcontrib." AND idcontributor = ".$idcontribs[$y].";";
				}
				if ($actionscontrib[$y] == "I"){
					$RAW_QUERY = "INSERT INTO dlinkcontribute (idcontribution, idcontributor, contributorrole, contributororder) VALUES (".$idcontrib.",".$idcontribs[$y].",'".$role."',".$order.");";
				}
				if ($actionscontrib[$y] == "D"){
					$RAW_QUERY = "DELETE FROM dlinkcontribute WHERE idcontribution = '".$idcontrib."' and idcontributor = ".$idcontribs[$y]." ;";
				}
				if ($actionscontrib[$y] == "I" | $actionscontrib[$y] == "U" | $actionscontrib[$y] == "D"){
					//echo "<script type='text/javascript'>alert('RAW_QUERY= $RAW_QUERY');</script>";
					$statement = $em->getConnection()->prepare($RAW_QUERY);
					$statement->execute();
				}
			}
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
	
	public function addcontributionAction(Request $request){
		$dcontribution = new Dcontribution();
			
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(DcontributionType::class, $dcontribution, array('entity_manager' => $em,));
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				try {
					$em->persist($dcontribution);
					$em->flush();

					$m=0;
					$idcontrib = $dcontribution->getIdcontribution();
				
					if ($idcontrib != ""){	
						$actionscontribstr = $request->get('newcontributors');
						//return new Response('<html><body>'.print_r($actionscontribstr).'</body></html>' );
						$this->savecontributors($idcontrib,$actionscontribstr,$request);
					}  
					
					$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
				
					return $this->redirectToRoute('app_edit_contribution', array('pk' => $dcontribution->getPk()));
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
		
		return $this->render('@App/addcontribution.html.twig', array(
			'form2' => $form->createView(),
			'originaction'=>'add_beforecommit'
		));
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
        $list_keywords=Array();
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
						$em->persist($ddocument);
						
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
			
			return $this->render('@App/adddocument.html.twig', array(
				'ddocument' => $ddocument,
				'form' => $form->createView(),
				'originaction'=>'add_beforecommit'
				
			));
		}else{
			return $this->render('@App/collnoaccess.html.twig');
		}
    }
	
	public function addpointsAction(Request $request){
		$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('4,15,26',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
		if ($rightoncollection == true){
			$dloccenter = new DLoccenter();
			$dcontribution = new Dcontribution();
			
			//$editvals = "";
			$em = $this->getDoctrine()->getManager();
			$form = $this->createForm(PointType::class, $dloccenter, array('entity_manager' => $em,));
			$form2 = $this->createForm(DcontributionType::class, $dcontribution, array('entity_manager' => $em,));
			
			if ($request->isMethod('POST')) {
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
					try {
						$em->persist($dloccenter);
						$em->flush();
						$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
						return $this->redirectToRoute('app_edit_point', array('pk' => $dloccenter->getPk()));
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
					$dloccenter->setPk(0);
					echo "<script type='text/javascript'>alert('error in form');</script>";
				}
			}

			return $this->render('@App/addpoints.html.twig', array(
				'dloccenter' => $dloccenter,
				'point_form' => $form->createView(),
				'form2' => $form2->createView(),
				'originaction'=>'add_beforecommit'
			));
		}else{
			return $this->render('@App/collnoaccess.html.twig');
		}
    }
	
	public function addstratumAction(Request $request){
		$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('4,15,26',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
		if ($rightoncollection == true){
			$dloclitho = new DLoclitho();

			$em = $this->getDoctrine()->getManager();
			$form = $this->createForm(StratumType::class, $dloclitho, array('entity_manager' => $em,));
			
			if ($request->isMethod('POST')) {
				print_r($request->get('---'.'appbundle_dloclitho_alternance'.'---'));
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
	
	public function adddrillingAction(Request $request){
		$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('4,15,26',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
		if ($rightoncollection == true){
			return $this->render('@App/adddrilling.html.twig');
		}else{
			return $this->render('@App/collnoaccess.html.twig');
		}
    }
		
	public function getusercoll_right($coll,$rights){          
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
	
	public function editcontributionAction(Dcontribution $dcontribution, Request $request){				
		if (!$dcontribution) {
			throw $this->createNotFoundException('No contribution found' );
		}else{
			$RAW_QUERY = "";
			$linkcontribute[]=null;
			$arraycontributors[] =null;
			
			$em = $this->getDoctrine()->getManager();
			$form = $this->createForm(DcontributionEditType::class, $dcontribution, array('entity_manager' => $em,));
			
			$idcontrib = $dcontribution->getIdcontribution();
			$RAW_QUERYcontributors = "SELECT * FROM dlinkcontribute where idcontribution = '".$idcontrib."';";
			$statement = $em->getConnection()->prepare($RAW_QUERYcontributors);
			$statement->execute();
			$arraylinkcontribs = $statement->fetchAll();
			
			if (count($arraylinkcontribs) > 0 ){
				
				foreach($arraylinkcontribs as $arraylinkcontribs_obj){
					$Idcontributor = $arraylinkcontribs_obj["idcontributor"];
					
					if ($Idcontributor == null){
						$actionlinkcontrib = "I";
					}else{
						$actionlinkcontrib = "U";
					}
				}
				$RAW_QUERYIdcontrib = "SELECT * FROM dcontributor c left join dlinkcontribute l on l.idcontributor = c.idcontributor where l.idcontribution = '".$idcontrib."';";
						
			//	"SELECT * FROM dcontributor where idcontributor = ".$Idcontributor.";";
				$statement = $em->getConnection()->prepare($RAW_QUERYIdcontrib);
				$statement->execute();
				$arraycontributors = $statement->fetchAll();
				//return new Response('<html><body>'.print_r($arraycontributors).'</body></html>' );
				foreach($arraycontributors as $arraycontributors_obj){
					$idcontributors[]=$arraycontributors_obj['idcontributor'];
				}
			}else{
				$actionlinkcontrib = "I";
			}
					
			if ($request->isMethod('POST')) {
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
					try {
						$em->persist($dcontribution);
						$em->flush();

						$m=0;
						$idcontrib = $dcontribution->getIdcontribution();
						
						if ($idcontrib != ""){	
							$actionscontribstr = $request->get('newcontributors');
							$this->savecontributors($idcontrib,$actionscontribstr,$request);
						}  
						
						$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
					
						return $this->redirectToRoute('app_edit_contribution', array('pk' => $dcontribution->getPk()));
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
		
			if(!isset($arraycontributors[0])){
				return $this->render('@App/editcontribution.html.twig', array(
				'dcontribution' => $dcontribution,
				'form2' => $form->createView(),
				'originaction'=>'edit'
				));
			}else{
				return $this->render('@App/editcontribution.html.twig', array(
					'dcontribution' => $dcontribution,
					'form2' => $form->createView(),
					'arraylinkcontribs' => $arraylinkcontribs,
					'arraycontributors' => $arraycontributors,
					'originaction'=>'edit'
				));
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
	
	public function editpointAction(Dloccenter $dloccenter, Request $request){  
		if (!$dloccenter) {
			throw $this->createNotFoundException('No location found' );
		}else{
			$actionscontributions[] = null;
			$arraydcontributors[]=null;
			$em = $this->getDoctrine()->getManager();
			$idcol = $dloccenter->getIdcollection();
			$idloc = $dloccenter->getIdpt();
			
			//drilling
			$sql = '
					SELECT d.idcollection, d.idpt, drilling, diameter, unitdiameter, waterflow, restingwater, depthwatertable, infowater, infodrilling, d.pk
					FROM dlocdrilling d
					INNER JOIN dloccenter c ON c.idcollection = d.idcollection and c.idpt = d.idpt
					WHERE c.idcollection = :col and c.idpt = :pt
					';
			$statement = $em->getConnection()->prepare($sql);
			$statement->execute(array('col' => $idcol,'pt' => $idloc ));
			$drillings = $statement->fetchAll();
			//print_r($drillings);
			if ($drillings != null){
				$actiondrilling = "U";
			}else{
				$actiondrilling = "I";
			}
			
			//drilling type
			$sqldt = '
					SELECT pk,drillingtype
					FROM dlocdrillingtype 
					WHERE idcollection = :col and idpt = :pt
					';
			$statement = $em->getConnection()->prepare($sqldt);
			$statement->execute(array('col' => $idcol,'pt' => $idloc ));
			$drillingttypes = $statement->fetchAll();
			//print_r($drillingttypes);
			if ($drillingttypes != null){
				$actiondrillingtype = "U";
			}else{
				$actiondrillingtype = "I";
			}
			
			//Contributions
			/*$loccontribution = $this->getDoctrine()
			->getRepository(DLinkContLoc::class)
			 ->findBy(array('idcollection' => $idcol, 
							'id' => $idloc
						   ));*/
			$RAW_QUERYDLinkContLoc = "SELECT idcontribution
						FROM dlinkcontloc 
						WHERE idcollection = '".$idcol."' AND id = ".$idloc.";";
			$statement = $em->getConnection()->prepare($RAW_QUERYDLinkContLoc);
			$statement->execute();
			$loccontribution = $statement->fetchAll();

			foreach($loccontribution as $loccontribution_obj){
				$idcontrib = $loccontribution_obj['idcontribution'];
				
				$idscontr_originals[]=$loccontribution_obj['idcontribution'];
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
			
			$arraydloclitho[]=null;
			//precision
			/*if (!is_null($dloccenter->getIdprecision())){
				$idprecision = $dloccenter->getIdprecision()->getIdprecision();

				$lprecision = $this->getDoctrine()
					->getRepository(LPrecision::class)
					->findBy(array('idprecision' => $idprecision));
			}
			 $lprecisionList = $this->getDoctrine()
				->getRepository(LPrecision::class)
				->findAll();*/
			
			$dcontribution = new Dcontribution();
			$form = $this->createForm(PointEditType::class, $dloccenter, array('entity_manager' => $em,));
			$form2 = $this->createForm(DcontributionType::class, $dcontribution, array('entity_manager' => $em,));
			//echo "<script>console.log('avant post1' );</script>";
			
			if ($request->isMethod('POST')) {
				$form->handleRequest($request);

				if ($form->isSubmitted() && $form->isValid()) {
					try {
						$em->flush();
						//drilling
						$txtdiameter = "";
						if ($request->get('inp_drill-drilling') <> ""){	
							$origin_error = "In drilling, ";						
							$drilling = $request->get('inp_drill-drilling');
							$drilldiameter = $request->get('inp_drill-diameter');
							if ($drilldiameter > 0) {
								$txtdiameter = ",diameter = ".$drilldiameter;
							}else{
								$txtdiameter = ",diameter = 0";
							}
							$unitdiameter = $request->get('inp_drill-unitdiameter');
							$waterflow = $request->get('inp_drill-waterflow');
							if ($waterflow > 0) {
								$txtwaterflow = ",waterflow = ".$waterflow;
							}else{
								$txtwaterflow = ",waterflow = 0";
							}
							$restingwater = $request->get('inp_drill-restingwater');
							if ($restingwater == 0) {
								$txtrestingwater = ",restingwater = 'f'";
							}
							if ($restingwater == 1) {
								$txtrestingwater = ",restingwater = 't'";
							}
							$depthwatertable = $request->get('inp_drill-depthwatertable');
							if ($depthwatertable > 0) {
								$txtdepthwatertable = ",depthwatertable = ".$depthwatertable;
							}else{
								$txtdepthwatertable = ",depthwatertable = 0";
							}
							$infowater = $request->get('inp_drill-infowater');
							$infodrilling = $request->get('inp_drill-infodrilling');
														
							if ($actiondrilling == "U"){
								$RAW_QUERY = "UPDATE dlocdrilling SET drilling = '".$drilling."'".$txtdiameter.",unitdiameter = '".$unitdiameter."'".$txtrestingwater.$txtwaterflow.$txtdepthwatertable.",infowater = '".$infowater."',infodrilling = '".$infodrilling."' WHERE idcollection = '".$idcol."' AND idpt = ".$idloc.';';
							}
							if ($actiondrilling == "I"){
								$RAW_QUERY = "INSERT INTO dlocdrilling (idcollection,idpt,drilling, diameter, unitdiameter, waterflow, restingwater, depthwatertable, infowater, infodrilling) VALUES ('".$idcol."',".$idloc.",'".$drilling."',".$drilldiameter.",'".$unitdiameter."',".$waterflow.",".$restingwater.",".$depthwatertable.",'".$infowater."','".$infodrilling."');";
							}
							if ($actiondrilling == "I" | $actiondrilling == "U" ){ 
								//return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );
								$statement = $em->getConnection()->prepare($RAW_QUERY);
								$statement->execute();
							}
						}
						
						//drilling type
						if ($request->get('newdrilltypes') <> ""){	
							$origin_error = "In drilling type, ";		
							$drillingtypechanges =  explode(",", $request->get('newdrilltypes')); 
							$elem =array();
							
							$i=0;
							foreach($drillingtypechanges as $e) {
								if ($e != ""){
									$elem = explode("-", $e); // $drilltype_pk[0] +"-"+$(this).val()+"-U"
									$pks[$i] = $elem[0];
									$types[$i] = $elem[1];
									$actions[$i] = $elem[2];
									$i++;
								}
							} 
							for ($y = 0; $y < sizeof($pks); $y++) {
								if ($actions[$y] == "U"){
									$RAW_QUERY_DT = "UPDATE dlocdrillingtype SET drillingtype = '".$types[$y]."' WHERE pk = ".$pks[$y]." AND idcollection = '".$idcol."' AND idpt = ".$idloc.';';
								}
								if ($actions[$y] == "I"){
									$RAW_QUERY_DT = "INSERT INTO dlocdrillingtype (idcollection,idpt,drillingtype) VALUES ('".$idcol."',".$idloc.",'".$types[$y]."');";
								}
								if ($actions[$y] == "D"){
									$RAW_QUERY_DT = "DELETE FROM dlocdrillingtype WHERE idcollection = '".$idcol."' and idpt = ".$idloc." and pk = ".$pks[$y]." ;";
								}
								if ($actions[$y] == "I" | $actions[$y] == "U" | $actions[$y] == "D"){
									$statement = $em->getConnection()->prepare($RAW_QUERY_DT);
									$statement->execute();
								}
							}
							//return new Response('<html><body>'.print_r($RAW_QUERY_DT).'</body></html>' );
						}
			
						//contributions
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
	
							for ($y = 0; $y < sizeof($ids); $y++) {
								if ($actionscontributions[$y] == "I"){
									$RAW_QUERY = "INSERT INTO dlinkcontloc (idcollection,id,idcontribution) VALUES ('".$idcol."',".$idloc.",".$ids[$y].');';
								}
								if ($actionscontributions[$y] == "D"){
									$RAW_QUERY = "DELETE FROM dlinkcontloc WHERE idcollection = '".$idcol."' and id = ".$idloc." and idcontribution = ".$ids[$y]." ;";
								}
								if ($actionscontributions[$y] == "I" |$actionscontributions[$y] == "D"){
									//return new Response('<html><body>'.print_r($RAW_QUERY).'</body></html>' );
									$statement = $em->getConnection()->prepare($RAW_QUERY);
									$statement->execute();
								}
							}
						}
						
						$this->addFlash('success','DATA RECORDED IN DATABASE!');   //$dloccenter->getCoordLong()
						
						return $this->redirectToRoute('app_edit_point', array('pk' => $dloccenter->getPk()));
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
			
			$rightoncollection = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('4,15,26',['Curator','Validator','Encoder','Collection_manager']); //'Viewer'
			if ($rightoncollection == true){
				//$editvals = "lat:".$dloccenter->getCoordLat().",,long:".$dloccenter->getCoordLat()	;
				//echo "<script>alert('dans controller fin:".$editvals."');</script>";

				$longarr =array();
				$longarr[0]=$dloccenter->getCoordLong();
				$latarr =array();
				$latarr[0]=$dloccenter->getCoordLat();

				if (isset($arraydcontributors[1])){
					return $this->render('@App/editpoint.html.twig', array(
						'latcoord' => $latarr,
						'longcoord' => $longarr,
						'dloccenter' => $dloccenter,
						'point_form' => $form->createView(),
						'form2' => $form2->createView(),
						'drillings' => $drillings,
						'drillingttypes' => $drillingttypes,
						'arraydloclitho' => $arraydloclitho,
						'arrayLinkcontributions' => $arraydcontributors,
						'origin'=>'edit',
						'originaction'=>'edit'
					));
				}else{
					return $this->render('@App/editpoint.html.twig', array(
						'latcoord' => $latarr,
						'longcoord' => $longarr,
						'dloccenter' => $dloccenter,
						'point_form' => $form->createView(),
						'form2' => $form2->createView(),
						'drillings' => $drillings,
						'drillingttypes' => $drillingttypes,
						'arraydloclitho' => $arraydloclitho,
						'origin'=>'edit',
						'originaction'=>'edit'
					));
				}
			}else{
				return $this->render('@App/collnoaccess.html.twig');
			}
		}
    }
	
	public function editdocAction(Ddocument $ddocument, Request $request){
		$this->set_sql_session();
			/*	$arrayeditvals =  explode(",,", $editvals); 
		$elem =array();
		foreach($arrayeditvals as $e) {
            $elem1 = explode(":", $e);
			$elem[$elem1[0]]=$elem1[1];
			echo "<script>console.log('".$elem1[0]."   ".$elem1[1]."' );</script>";
        } */
		$em = $this->getDoctrine()->getManager();	
		$this->set_sql_session();
		if (!$ddocument) {
			throw $this->createNotFoundException('No document found' );
		}else{		
			//medium
			/*if (!is_null($ddocument->getMedium())){
				$medium = $ddocument->getMedium()->getMedium(); // $ddocument->getMedium() is considered as a table because it's a FK

				$lmedium = $this->getDoctrine()
					->getRepository(LMedium::class)
					->findBy(array('medium' => $medium));
			}
			$lmediumList = $this->getDoctrine()
				->getRepository(LMedium::class)
				->findAll();*/
				
			
			$keywords=$ddocument->initDkeywords($em);
			
			$form = $this->createForm(DdocumentEditType::class, $ddocument, array('entity_manager' => $em,));

			if ($request->isMethod('POST')) {
				$temp= $request->get('appbundle_ddocument_medium');
				echo "<script>console.log('$temp' );</script>";
				$form->handleRequest($request);
				echo "<script>console.log('avantvalid' );</script>";

				if ($form->isSubmitted() && $form->isValid()) {
					try {
						echo "<script>console.log('dans valid' );</script>";
						$keywords=$request->get("widget_keywords",null);
						if($keywords!==null)
						{
							
							$ddocument->initNewDkeywords($em, $keywords);
							//throw new UndefinedOptionsException();
						}
						
						$em->flush();
						$this->addFlash('success','DATA RECORDED IN DATABASE!');  
						
						return $this->redirectToRoute('app_edit_doc', array('pk' => $ddocument->getPk()));
						
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
					/*} catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
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
					}*/
				}elseif ($form->isSubmitted() && !$form->isValid() ){
					echo "<script type='text/javascript'>alert('error in form');</script>";
				}
			}
			
			$rightoncollection1 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('1',['Curator','Validator','Collection_manager']); //'Viewer'
			$rightoncollection2 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('2',['Curator','Validator','Collection_manager']);
			$rightoncollection3 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('3',['Curator','Validator','Collection_manager']);
			$rightoncollection4 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('7',['Curator','Validator','Collection_manager']);
			$rightoncollection5 = $this->container->get('AppBundle\Controller\GeoController')->getusercoll_right('9',['Curator','Validator','Collection_manager']);
			if ($rightoncollection1 == true && $rightoncollection2 == true && $rightoncollection3 == true && $rightoncollection4 == true && $rightoncollection5 == true){
				return $this->render('@App/editdoc.html.twig', array(
					'ddocument' => $ddocument,
					'form' => $form->createView(),
					'origin'=>'edit',
					'originaction'=>'edit',
					'keywords'=>$keywords
				));
			}else{
				return $this->render('@App/collnoaccess.html.twig');
			}
		}
    }
	
	/*public function deletesammineralAction(String $primkey){
		$coll = "";
		$idsample = "";
		$idmineral = "";
		if ($primkey != ""){
			$elem = explode("-", $primkey);
			$coll = $elem[0];
			$idsample = $elem[1];
			$idmineral = $elem[2];
		}
						
		$em = $this->getDoctrine()->getManager();
		
		$sammineral = $this->getDoctrine()
			->getRepository(Dsamminerals::class)
			 ->findBy(array('idcollection' => $coll, 
							'idsample' => $idsample,
							'idmineral' => $idmineral 
		));
						   
	//	if (!$sammineral) {
	//		throw $this->createNotFoundException('No mineral found' );
	//	}else{
		Try{
			$RAW_QUERY = "DELETE FROM dsamminerals WHERE idcollection = '".$coll."' and idsample = ".$idsample." and idmineral = ".$idmineral." ;";
			//return new Response('<html><body>query=:'.$RAW_QUERY.'</body></html>' );
			
			$statement = $em->getConnection()->prepare($RAW_QUERY);
			//$statement->execute();
				
				return $this->render('@App/editmineral.html.twig', array(
				'lminerals' => $lminerals,
				'form' => $form->createView(),
				'origin'=>'edit'
			));
		//}
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
    }*/
	
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
	
	public function searchallAction(Request $request){
		return $this->render('@App/searchall.html.twig');  
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
	
	public function searchpointsAction(Request $request){
		return $this->render('@App/searchpoints.html.twig');
    }
	
	public function searchdocumentAction(Request $request){
		return $this->render('@App/searchdocument.html.twig');
    }
	
	public function searchstratumAction(Request $request){
		return $this->render('@App/searchstratum.html.twig');
    }
	
	public function searchdrillingAction(Request $request){
		return $this->render('@App/searchdrilling.html.twig');
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
	
	public function contribtype_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();

		$RAW_QUERY = "SELECT DISTINCT datetype FROM dcontribution ORDER BY datetype;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $types = $statement->fetchAll();
        
        return new JsonResponse($types);
    }
	
    public function contribrole_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();

		$RAW_QUERY = "SELECT DISTINCT contributorrole FROM dlinkcontribute ORDER BY contributorrole;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $types = $statement->fetchAll();
        
        return new JsonResponse($types);
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
	
	
	public function allkeywords_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$word = $request->query->get("code","");
        if(strlen($word)>0)
        {
			
            $RAW_QUERY = "SELECT * FROM mv_keyword_hierarchy_to_object_list_parent where word ~* :word ORDER BY word LIMIT :limit;"; 
            
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->bindParam(":word", $word, \PDO::PARAM_INT);
            $statement->bindParam(":limit", $this->limit_autocomplete, \PDO::PARAM_INT);
            $statement->execute();
            $names = $statement->fetchAll();
        }       
        
        return new JsonResponse($names);
    }
	
	public function keywords_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$word = $request->query->get("code","");
        if(strlen($word)>0)
        {
			
            $RAW_QUERY = "SELECT * FROM mv_keyword_hierarchy_to_object_list_parent where word ~* :word ORDER BY word LIMIT :limit;"; 
            
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->bindParam(":word", $word, \PDO::PARAM_INT);
            $statement->bindParam(":limit", $this->limit_autocomplete, \PDO::PARAM_INT);
            $statement->execute();
            $names = $statement->fetchAll();
        }       
        
        return new JsonResponse($names);
    }
    
    public function contribnames_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$name = $request->query->get("code","");
        if(strlen($name)>0)
        {
            $RAW_QUERY = "SELECT * FROM dcontributor where lower(people) LIKE :people||'%' ORDER BY people LIMIT :limit;"; 
            
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->bindParam(":people", $name, \PDO::PARAM_STR);
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
	
	public function usersnames_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$name = strtolower($_GET['code']);
		$RAW_QUERY = "SELECT * FROM duser where lower(last_name) LIKE '".$name."%' ORDER BY last_name;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $names = $statement->fetchAll();
        
        return new JsonResponse($names);
    }
	
	public function Parent_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$num = strtolower($_GET['code']);
		$RAW_QUERY = "SELECT DISTINCT lower(mparent) as mparent FROM lminerals where lower(mparent) LIKE '".$num."%';"; 
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $codes = $statement->fetchAll();
        return new JsonResponse($codes);
    }
	
	public function Minname_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$num = strtolower($_GET['code']);
		$RAW_QUERY = "SELECT DISTINCT lower(mname) as mname FROM lminerals where lower(mname) LIKE '".$num."%';"; 
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $codes = $statement->fetchAll();
        return new JsonResponse($codes);
    }
	
	public function Minfname_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$num = strtolower($_GET['code']);
		$RAW_QUERY = "SELECT DISTINCT lower(fmname) as fmname FROM lminerals where lower(fmname) LIKE '".$num."%';"; 
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $codes = $statement->fetchAll();
        return new JsonResponse($codes);
    }
	
	public function Minformula_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$num = strtolower($_GET['code']);
		$RAW_QUERY = "SELECT DISTINCT mformula as mformula FROM lminerals where lower(mformula) LIKE '".$num."%';"; 
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $codes = $statement->fetchAll();
        return new JsonResponse($codes);
    }
	
	public function Museumloc_autocompleteAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		$num = strtolower($_GET['code']);
		$RAW_QUERY = "SELECT DISTINCT lower(museumlocation) as museumlocation FROM dsample where lower(museumlocation) LIKE '".$num."%';"; 
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $codes = $statement->fetchAll();
        return new JsonResponse($codes);
    }
	
	public function Box_autocompleteAction(Request $request){
		$num = strtolower($_GET['code']);
		$em = $this->getDoctrine()->getManager();
		$RAW_QUERY = "SELECT DISTINCT lower(trim(boxnumber)) as boxnumber FROM dsample where lower(boxnumber) LIKE '".$num."%';";
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $codes = $statement->fetchAll();
		return new JsonResponse($codes);
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
	
	public function results_searchsampleAction($queryvals,Request $request){
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
	
	public function results_searchallAction($queryvals,Request $request){
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
	
	public function adminAction(Request $request){
		return $this->render('@App/admin.html.twig');
    }
    
    //
    
    public function searchallbsAction(Request $request){
    
        $form=$this->createForm(SearchAllForm::class, null);
		return $this->render('@App/searchallbs.html.twig',array('form' => $form->createView()));  
	}
    
    public function all_object_categoriesAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		
	   $RAW_QUERY = "select distinct main_type from mv_rmca_main_objects_description order by main_type;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $codes = $statement->fetchAll();
        
        return new JsonResponse($codes);
    }
	
	public function all_collectionsAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		
	   $RAW_QUERY = "select distinct idcollection from mv_rmca_merge_all_objects_vertical_expand order by idcollection;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $codes = $statement->fetchAll();
        
        return new JsonResponse($codes);
    }
	
	public function refresh_viewsAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		
	   $RAW_QUERY = "select rmca_refresh_materialized_view_auto as init_portal FROM rmca_refresh_materialized_view_auto();"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $codes = $statement->fetchAll();
        
        return new JsonResponse($codes);
    }
	
	protected function gen_pdo_name($idx)
	{
		return ":pgeol".(string)$idx;
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
				foreach($contribs as $contrib)
				{
					
					$params_sql_or[]="EXISTS (SELECT contr.pk FROM v_all_contributions_to_object contr WHERE contr.idcontributor=".$this->gen_pdo_name($idx_param)." AND a.pk=contr.pk)                                                
                    "
                    ;
					$params_pdo[$this->gen_pdo_name($idx_param)]["value"]=$contrib;
					$params_pdo[$this->gen_pdo_name($idx_param)]["type"]=\PDO::PARAM_INT;
					$idx_param++;
				}
				$params_sql_or_group["contributor"]=$params_sql_or;
				
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
                    $params_sql_or[]="ST_INTERSECTS(ST_SETSRID(ST_Point(coord_long, coord_lat),4326), ST_GEOMFROMTEXT(".$this->gen_pdo_name($idx_param).",4326))";
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
			
								
		    $RAW_QUERY='WITH main_q AS (SELECT DISTINCT a.pk, main_type, col_1_name, col_1_value, col_2_name, col_2_value, col_3_name, col_3_value, col_4_name, col_4_value, idobject, idcollection, contributors, institutions, coord_long, coord_lat 
			FROM public.mv_rmca_main_objects_description a
			LEFT JOIN public.mv_rmca_merge_all_objects_vertical_expand b
			ON a.pk=b.main_pk
			LEFT JOIN mv_all_contributions_to_object_agg_merge c
			ON a.pk=c.pk
			'.$query_where.'
			
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
					return $this->render('@App/search_results_bs.html.twig', array("results"=>$results, "pagination"=>$pagination, "page_size"=>$page_size, "nb_results"=>$count_all, "geojson"=>$geojson, "headers1"=>implode("\r\n", $headers1),"headers2"=>implode("\r\n", $headers2),"headers3"=>implode("\r\n", $headers3), "headers4"=>implode("\r\n", $headers4) ));
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
	
	public function widget_keywordAction(Request $request)
	{
		$index=$request->get("index","1");
		$default_val=$request->query->get("default_val","");
		return $this->render('@App/foreignkeys/dkeyword.html.twig', array("index"=>$index, "default_val"=>$default_val));
	}
	
}
