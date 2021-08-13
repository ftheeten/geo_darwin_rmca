<?php

namespace AppBundle\Form;

use AppBundle\Entity\DLoccenter;
use AppBundle\Entity\Lprecision;
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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PointEditType extends AbstractType {
		
    /**
     * {@inheritdoc}
     */
	
    public function buildForm(FormBuilderInterface $builder, array $options)  {
		$em = $options['entity_manager'];

		/* builder---------*/
        $builder
			->add('fieldnum', TextType::class, array('required' => false))
			->add('coordlat', NumberType::class, array( 'scale' => 4,'required' => true))
			->add('coordlong', NumberType::class, array('scale' => 4,'required' => true))
			->add('altitude', NumberType::class, array('required' => false))
			->add('date', DateType::class, array('widget' => 'single_text', 'required' => false))
			->add('place', TextType::class, array('required' => false))
			->add('geodescription', TextareaType::class, array('required' => false))
			->add('positiondescription', TextareaType::class, array('required' => false))
			->add('variousinfo', TextareaType::class, array('required' => false)) 
			->add('country', TextType::class, array('required' => false))
			->add('docref', TextType::class, array('required' => false))
			->add('epsg', TextType::class, array('required' => false))
			->add('wkt', TextType::class, array('required' => false))
			//->add('originalcoord', TextType::class, array('required' => false))
			->add('idprecision', EntityType::class, array(
               'class' => Lprecision::class,
               'choice_label' => 'precision',
               'label' => 'precision'
            ))
		;
		
		$builder->addEventListener(
		  FormEvents::PRE_SET_DATA,    
					  function(FormEvent $event) { 
						$dloccenter = $event->getData();
						if (null === $dloccenter) {
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
            'data_class' => 'AppBundle\Entity\DLoccenter'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()  {
        return 'appbundle_dloccenter';
    }
}
