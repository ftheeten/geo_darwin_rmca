<?php
/* src/AppBundle/Controller/UserController.php */

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\Duser;
use AppBundle\Form\DuserEditType;
use AppBundle\Form\DuserType;

/**
 * User controller.
 *
 */
class UserController extends Controller
{
    public function indexAction() {
		$em = $this->getDoctrine()->getManager();		
		$RAW_QUERYusers = "SELECT * FROM duser ORDER BY Id;";
		$statement = $em->getConnection()->prepare($RAW_QUERYusers);
		$statement->execute();
		$users = $statement->fetchAll();
		
       // $userManager = $this->get('fos_user.user_manager');
        //$users = $userManager->findUsers();
        return $this->render('@App/userindex.html.twig', array(
            'users' => $users,
        ));
    }

	public function viewAction(Duser $duser){
		$em = $this->getDoctrine()->getManager();
		
		$RAW_QUERYroles = "SELECT codecollection, collection, uc.role as role
			FROM fos_user_collections uc
			left join codecollection c on c.pk = uc.collection_id
			where uc.user_id = ".$duser->getId().";"; 
				
		$statement = $em->getConnection()->prepare($RAW_QUERYroles);
		$statement->execute();
		$roles = $statement->fetchAll();
		
		return $this->render('@App/userview.html.twig', array('duser' => $duser,'roles' => $roles ));
    }
	
    public function editAction(Request $request, Duser $duser) {
		if ($this->container->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {

			$em = $this->getDoctrine()->getManager();
			$editForm = $this->createForm(DuserEditType::class, $duser);
			
			$roleuserarray = $duser->getRoles();  
			
			$RAW_QUERYroles = "SELECT codecollection, collection, uc.role as role
				FROM fos_user_collections uc
				left join codecollection c on c.pk = uc.collection_id
				where uc.user_id = ".$duser->getId().";"; 
					
			$statement = $em->getConnection()->prepare($RAW_QUERYroles);
			$statement->execute();
			$roles = $statement->fetchAll();
					
			$editForm->handleRequest($request);
			if ($editForm->isSubmitted() && $editForm->isValid()) {
				try {
					$tw = (string) $request->get('newpw');
					$duser->setPlainPassword($tw);      
					$role = (string) $request->get('role');
					$duser->setRoles([$role]);     
							
					$em->persist($duser);
					$em->flush();

					$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
				
				return $this->redirectToRoute('app_edit_profile', array('id' => $duser->getId()));
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

			return $this->render('@App/useredit.html.twig', array(
				'duser' => $duser,
				'article' => $duser,
				'roles' => $roles,  
				'roleuser' => $roleuserarray[0],
				'edit_form' => $editForm->createView(),
			));
		}else{
				echo "<script type='text/javascript'>alert('You are not allowed to do that action !');</script>";
				return $this->render('@App/home.html.twig');
		}
	}
	
	public function addAction(Request $request){
		$duser = new Duser();
			
		$em = $this->getDoctrine()->getManager();
		$form = $this->createForm(DuserType::class, $duser, array('entity_manager' => $em,));
		
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				try {
					$tw = (string) $request->get('newpw');
					$duser->setPlainPassword($tw);      
					$role = (string) $request->get('role');
					$duser->setRoles([$role]); 
					      
					$em->persist($duser);
					$em->flush(); 
					
					$this->addFlash('success', 'DATA RECORDED IN DATABASE!');
				
					return $this->redirectToRoute('app_edit_profile', array('id' => $duser->getId()));
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

        return $this->render('@App/useredit.html.twig', array(
            'edit_form' => $form->createView(),
			'duser' => $duser,
			'originaction'=>'add_beforecommit',
        ));
    }
	
	public function lastuseridAction(Request $request){
		$id = 0;
		$em = $this->getDoctrine()->getManager();
		$RAW_QUERY = "SELECT id FROM duser ORDER BY id DESC LIMIT 1;"; 
		
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $id = $statement->fetchAll();
		
		return new JsonResponse($id);
	}
}