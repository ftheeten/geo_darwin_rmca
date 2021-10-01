<?php

namespace AppBundle\Form;

use AppBundle\Entity\Ddocument;
use AppBundle\Entity\Lmedium;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DecimalType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType; 
use Symfony\Component\Form\Extension\Core\Type\CollectionType; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class DdocumentType extends AbstractType {
		
    /**
     * {@inheritdoc}
     */
	
    public function buildForm(FormBuilderInterface $builder, array $options)  {
		$em = $options['entity_manager'];

		/* list for collections---------*/
        $RAW_QUERYcoll = "SELECT codecollection, collection FROM codecollection where zoneutilisation LIKE 'document%';";
        $statementcoll = $em->getConnection()->prepare($RAW_QUERYcoll);
        $statementcoll->execute();
        $codescollection = $statementcoll->fetchAll();
		
		$elemcoll =array();
		foreach($codescollection as $e) {
			//foreach($e as $ee) {
				//$elemcoll[$ee]=$ee;
				$elemcoll[$e['collection']]=$e['codecollection'];
			//} 
		} 
		
		/* builder---------*/
        $builder
			->add('idcollection', ChoiceType::class, array('choices'  => $elemcoll))
			->add('iddoc', TextType::class, array('required' => true))
			->add('numarchive', TextType::class, array('required' => false))
			->add('caption', TextType::class, array('required' => false))
			->add('centralnum', TextType::class, array('required' => false))
			->add('medium', EntityType::class, array(
               'class' => Lmedium::class,
               'choice_label' => 'Medium',
               'label' => 'Medium'
            ))
			->add('location', TextType::class, array('required' => false))
			->add('numericallocation', TextType::class, array('required' => false))
			->add('filename', TextType::class, array('required' => false))
			->add('docinfo', TextareaType::class, array('required' => false))
			->add('edition', TextType::class, array('required' => false)) 
			->add('pubplace', TextType::class, array('required' => false))
			->add('doccartotype', TextType::class, array('required' => false))
			
			
			//->add('Dkeywords', DkeywordsType::class)
			#->add('keywords', TextType::class, array('mapped' => false,'required' => false))
            /*->add('dkeywords', CollectionType::class, array(
            'entry_type' => DkeywordType::class,
            'mapped' => false,
            'required' => false,
                'entry_options' => [
                    'label'         => false,
                ],
                'label'         => false,
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,  // Very important thing!
            ))*/
            
        
		;
		
		$builder->addEventListener(
		  FormEvents::PRE_SET_DATA,    
					  function(FormEvent $event) { 
						$ddocument = $event->getData();
						if (null === $ddocument) {
						  return; 
						}
					  }
		);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)  {
		 $resolver->setRequired('entity_manager');

        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Ddocument',
			 'error_mapping' => [
            
        ],
        ));
    }
	


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()  {
        return 'appbundle_ddocument';
    }
}
