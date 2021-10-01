<?php

namespace AppBundle\Form;

use AppBundle\Entity\DLoclitho;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class StratumType extends AbstractType {
		
    /**
     * {@inheritdoc}
     */
	
    public function buildForm(FormBuilderInterface $builder, array $options)  {
		$em = $options['entity_manager'];

		/* list for collections---------*/
        $RAW_QUERYcoll = "SELECT codecollection, collection FROM codecollection where zoneutilisation LIKE 'localisation%';";
        $statementcoll = $em->getConnection()->prepare($RAW_QUERYcoll);
        $statementcoll->execute();
        $codescollection = $statementcoll->fetchAll();
		
		$elemcoll =array();
		foreach($codescollection as $e) {
			$elemcoll[$e['collection']]=$e['codecollection'];
		} 
		
		/* builder---------*/
        $builder
			->add('idcollection', ChoiceType::class, array('choices'  => $elemcoll))
			

			->add('idpt', TextType::class, array('required' => true))
			->add('idstratum', TextType::class, array('required' => true))
			->add('lithostratum', TextType::class, array('required' => false))
			->add('topstratum', NumberType::class, array( 'scale' => 4,'required' => false))
			->add('bottomstratum', NumberType::class, array('scale' => 4,'required' => false))
			->add('descriptionstratum', TextareaType::class, array('required' => false))
			//->add('alternance', ChoiceType::class, array('choices'  => ['True', 'False']))
		;
		
		$builder->addEventListener(
		  FormEvents::PRE_SET_DATA,    
					  function(FormEvent $event) { 
						$dloclitho = $event->getData();
						if (null === $dloclitho) {
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
            'data_class' => 'AppBundle\Entity\DLoclitho'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()  {
        return 'appbundle_dloclitho';
    }
}
