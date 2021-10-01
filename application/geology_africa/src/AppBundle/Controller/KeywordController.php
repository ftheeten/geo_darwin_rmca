<?php

// src/AppBundle/Controller/KeywordController.php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Lkeywords;
use AppBundle\Form\LkeywordsType;

use Symfony\Component\Form\FormError;

class KeywordController extends Controller
{
	protected $limit_autocomplete=30;
		
 
	 public function main_searchAction(Request $request)
    {
        $lkeyword = new Lkeywords();
		$em = $this->getDoctrine()->getManager();		 
        $form = $this->createForm(LkeywordsType::class, $lkeyword, array('entity_manager' => $em,));        
        $form_delete = $this->createForm(
                LkeywordsType::class, 
                $lkeyword, 
                array('entity_manager' => $em,
                      //'action'=> $this->generateUrl('app_delete_keywords')
                )
                
                );
        $current_form=NULL;        
        $parent="";
        $modal_mode="";
        $callback_action="";
        $term="";
		$is_parent=false;
         if ($request->isMethod('POST')) 
         {
            $parent=$form->get('wordfather')->getData();
                    
            $term=$form->get('wordson')->getData();
            
            $form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
                    
                    
                    $callback_action=$form->get('callback_action')->getData();
                    if($callback_action=="record_creation")
                    {
                        $modal_mode="show_add";
					}
					if($callback_action=="record_edition")
                    {
                        $modal_mode="show_add";
					}
                    elseif($callback_action=="record_deletion")
                    {
                         $modal_mode="show_delete";
                    }
                    try {
                        if($callback_action=="record_creation")
                        {
							$wordson=$form->get('wordson')->getData();
							$wordfather=$form->get('wordfather')->getData();
							$parent=$wordfather;
							$lkeyword->setWordson($wordson);
							$lkeyword->setWordfather($wordfather);
						    if($wordson==$parent)
						     {
								 $is_parent=true;
							 }
                            $current_form=$form;
                            $em->persist($lkeyword);
                            $em->flush();
                            $this->addFlash('success', 'DATA RECORDED IN DATABASE!');
                            $modal_mode= "close";
                            $callback_action ="display_new_data" ;
                            //$parent="";
                       }
					   elseif($callback_action=="record_edition")
					   {
						   $current_form=$form;
						    $term=$form->get('wordson_edit')->getData();
							
							$wordson=$form->get('wordson')->getData();
							$wordfather=$form->get('wordfather')->getData();
							$parent=$wordfather;
						    if($wordson==$parent)
						     {
								 $is_parent=true;
							 }
							 $lkeyword = $this->getDoctrine()
                                    ->getRepository(Lkeywords::class)
                                     ->findOneBy(array('wordson' => $term));
                           if( $lkeyword==null)
                           {
							   //sometimes root is not inserted
								$lkeyword=new Lkeywords();
								$lkeyword->setWordson($term);
								$lkeyword->setWordfather($term);
								$em->persist($lkeyword);
								$em->flush();
                           }
                           $lkeyword->setWordson($wordson);
						   $lkeyword->setWordfather($wordfather);
                           $em->flush();
                           $modal_mode=""; 
						   					   
					   }
                       elseif($callback_action=="record_deletion")
                       {
                           $current_form=$form_delete;
                           $term=$form->get('wordson')->getData();
                           $lkeyword = $this->getDoctrine()
                                    ->getRepository(Lkeywords::class)
                                     ->findOneBy(array('wordson' => $term));
                           if( $lkeyword==null)
                           {
								$lkeyword=new Lkeywords();
								$lkeyword->setWordson($term);
								$lkeyword->setWordfather($term);
								$em->persist($lkeyword);
								$em->flush();
                           }
                           $em->remove($lkeyword);
                           $em->flush();
                           $modal_mode="";                           
                       }
					}                    
					catch(\Doctrine\DBAL\DBALException $e ) {
						$current_form->addError(new FormError($e->getMessage()));
                        
					}
					catch(Exception $e ) {
						$current_form->addError(new FormError($e->getMessage()));
                       
					}
				}elseif ($form->isSubmitted() && !$form->isValid() ){
					$current_form->addError(new FormError("Other form error"));
                   
				}
         }
		return $this->render('@App/keywords/keyword_search.html.twig',
        Array(
         'lkeyword' => $lkeyword,
		 'form' => $form->createView(),
         'form_delete' => $form_delete->createView(),
         'mode'=>'creation',
         'parent'=>$parent,
         'callback_action'=>$callback_action,
         'modal_mode'=>$modal_mode,
         'term'=>$term,  
          'is_parent'=>$is_parent		 
         )
        );
	}
	
    public function raw_searchAction(Request $request)
    {
		return $this->render('@App/keywords/keyword_search_raw.html.twig');
	}
	
	

    
    protected function recursfancytreeHierarchy($path_array, &$target_array, $path_parent, $level=1, $expanded=true, $checkbox=true, $expand_first_level=false)
    {
        foreach($path_array[$path_parent] as $row)
        {
            $tmp_row=Array();
			$tmp_row["key"]=$row["word"];
			$tmp_row["checkbox"]=$checkbox;
			$tmp_row["title"]=$row["word"];
			if($row["wordfather"]==null)
			{
				$row["wordfather"]=$row["word"];
			}
			$tmp_row["wordfather"]=$row["wordfather"];
            $tmp_row["expanded"]=$expanded;
			if($level==1 && $expand_first_level )
			{
				$tmp_row["expanded"]=true;
			}			

			$tmp_row["unselect"]=true;
            $child_path= $path_parent.$row["word"]."/";
            if(array_key_exists($child_path, $path_array))
            {
                $tmp_row["children"]=Array();
                $this->recursfancytreeHierarchy($path_array, $tmp_row["children"], $child_path, $level +1, $expanded, $checkbox, $expand_first_level);
            }
            $target_array[]=$tmp_row;
            
        }
        return $target_array;
    }
	

	
	public function get_fancytreeAction(Request $request)
	{
		$em = $this->container->get('doctrine')->getEntityManager();
		$returned=Array();
		$struct=Array();
		//$result_tmp=Array();
		$hierarch_tmp=Array();
        $keyw=$request->get("keyword","");
        $exp_str=$request->get("expanded","true");
		$fuzzy_str=$request->get("fuzzy","false");
		$children_str=$request->get("children_only","false");
        $checkbox=$request->get("checkbox","true");
        $expand_first_level=$request->get("expand_first_level","false");
        if(strtolower( $checkbox)=="false")
        {
            $checkbox=false;
        }
        else
        {
            $checkbox=true;
        }
        
		
        $expanded=true;
		$root="/";
        if(strtolower($exp_str)=="false")
        {
            $expanded=false;
        }
        if(strtolower( $expand_first_level)=="true")
        {
            $expand_first_level=true;
        }
        else
        {
            $expand_first_level=false;
        }
        if(strlen(trim($keyw))>0)
        {
			
			if(strtolower($fuzzy_str)=="true")
			{
				$comparator="path_word ~* :keyw";
			}
			else
			{
				$comparator="LOWER(word) = LOWER(:keyw) or path_word ~* :keywreg ";				
			}
			if(strtolower($children_str)=="true")
			{
				 
				 $RAW_QUERY="with a as (SELECT *, max(level) OVER() as min_level, 
                        max(level) OVER() max_level,
                        regexp_replace(path_word,'[^/]*/$','') as path_parent,
                        rank()  over(PARTITION BY regexp_replace(path_word,'[^/]*/$','')
                                     order by path_word) -1 as idx

                        from public.rmca_get_keywords_hierarchy()
                        where ".$comparator."
                        ORDER BY level , path_word)
						SELECT pk, word, wordfather, parent_pk, path_pk, path_word, level, 
						min_level, max_level, regexp_replace(regexp_replace(path_word, :keywregahead, :keywreg) ,'[^/]*/$','') path_parent, idx FROM a";
					$statement = $em->getConnection()->prepare($RAW_QUERY);
					$statement->bindParam(":keyw", $keyw, \PDO::PARAM_STR);
					//if(strtolower($fuzzy_str)!=="true")
					//{			
					$keywregahead='.*/'.$keyw.'/';
					$keywreg='/'.$keyw.'/';
					$statement->bindParam(":keywregahead",$keywregahead  , \PDO::PARAM_STR); 	
					$statement->bindParam(":keywreg", $keywreg , \PDO::PARAM_STR); 					
					//}
			}
			else
			{
             $RAW_QUERY="with a as (SELECT *, 1 as min_level, 
                        max(level) OVER() max_level,
                        regexp_replace(path_word,'[^/]*/$','') as path_parent,
                        rank()  over(PARTITION BY regexp_replace(path_word,'[^/]*/$','')
                                     order by path_word) -1 as idx

                        from public.rmca_get_keywords_hierarchy()
                        where ".$comparator."
                        ORDER BY level , path_word)
                        SELECT * FROM (
                        SELECT  * FROM a 
                        UNION
                        SELECT DISTINCT
                        b.*,
                         min(level) OVER() as min_level, 
                        max(level) OVER() max_level,
                        regexp_replace(path_word,'[^/]*/$','') as path_parent,
                        rank()  over(PARTITION BY regexp_replace(path_word,'[^/]*/$','')
                                     order by path_word) -1 as idx
                        from public.rmca_get_keywords_hierarchy()
                        b
                        INNER JOIN 
                        (SELECT regexp_split_to_table(a.path_word, '/') as elem
                        from a
                        ) c on b.word=c.elem
                        ) d ORDER BY path_word";
						$statement = $em->getConnection()->prepare($RAW_QUERY);
						$statement->bindParam(":keyw", $keyw, \PDO::PARAM_STR);
						if(strtolower($fuzzy_str)!=="true")
						{					
							$keywreg='/'.$keyw.'/';
							$statement->bindParam(":keywreg",$keywreg , \PDO::PARAM_STR); 					
						}						
			}
                      
        }
        else
        {   $RAW_QUERY="SELECT *, MIN(level) OVER() as min_level, MAX(level) OVER() max_level,
                regexp_replace(path_word,'[^/]*/$','') as path_parent,
                RANK()  over(PARTITION BY regexp_replace(path_word,'[^/]*/$','') ORDER BY path_word) -1 as idx
                FROM public.rmca_get_keywords_hierarchy() ORDER BY level, path_word;";
            $statement = $em->getConnection()->prepare($RAW_QUERY);               
        }
        
		$statement->execute(); 
		$struct = $statement->fetchAll();
        $struct_by_parent=Array(); 
		$term_to_pos=Array();
		if(count($struct)>0)
		{
            $struct_by_parent=Array();
            foreach($struct as $row)
            {
                if(!array_key_exists($row["path_parent"], $struct_by_parent))
                {
                    $struct_by_parent[$row["path_parent"]]=array();
                    
                }
                 $struct_by_parent[$row["path_parent"]][]=$row;
            }
            
            
            $hierarch_tmp=$this->recursfancytreeHierarchy($struct_by_parent, $hierarch_tmp, $root, 1, $expanded, $checkbox,$expand_first_level);	
			
		}
		 return new JsonResponse($hierarch_tmp);
	}
	
	public function get_keywordpathAction(Request $request)
	{
		$em = $this->container->get('doctrine')->getEntityManager();
		$keyw=$request->get("keyword","");
		$returned=Array();
		$RAW_QUERY="SELECT * FROM (SELECT regexp_split_to_table(path_word,'/') 
			   AS word   FROM public.rmca_get_keywords_hierarchy() WHERE word=:keyw) a WHERE word !='';";
		$statement = $em->getConnection()->prepare($RAW_QUERY);
		$statement->bindParam(":keyw", $keyw, \PDO::PARAM_STR);
		$statement->execute(); 
		$struct = $statement->fetchAll();
		if(count($struct)>0)
		{
			$returned=$struct;
		}
		 return new JsonResponse($returned);
	}
	
	public function allkeywords_autocompleteAction(Request $request)
	{
		$names=Array();
		$em = $this->getDoctrine()->getManager();
		$word = strtolower($request->query->get("code",""));
        if(strlen($word)>0)
        {
			
            $RAW_QUERY = "SELECT * FROM
			(SELECT * FROM (SELECT word , 1 as sort   FROM v_lkeywords_merge where LOWER(word) LIKE :word||'%') a 
			UNION
			SELECT * FROM (SELECT word, 2  FROM v_lkeywords_merge  as sort where word ~* :word AND NOT LOWER(word) LIKE :word||'%')  b) c
			ORDER BY sort, lower(word)
			LIMIT :limit
			;"; 
            
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->bindParam(":word", $word, \PDO::PARAM_INT);
            $statement->bindParam(":limit", $this->limit_autocomplete, \PDO::PARAM_INT);
            $statement->execute();
            $names = $statement->fetchAll();
        }       
        
        return new JsonResponse($names);
    }
	
    
    public function addKeywordAction(Request $request)
    {
        $parent=$request->get("parent","");
        return $this->render('@App/keywords/modal_add_keyword.html.twig', Array('parent'=>$parent, 'new_keyword'=>""));
    }   

	
}	