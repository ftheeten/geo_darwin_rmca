<?php

namespace AppBundle\Form;

use AppBundle\Entity\Dcontribution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class DcontributionType extends AbstractType {
	 
		
    /**
     * {@inheritdoc}
     */
	
    public function buildForm(FormBuilderInterface $builder, array $options)  {

		/*$years=[];
		for($i = 2019; $i >1700; $i--) {
			$years[$i]=$i;
		}*/
		
		
		/* builder---------*/
        $builder
			->add('idcontribution', TextType::class, array('required' => true,"label"=> false))//,'attr' => ['style' => 'visibility:hidden'] ))
			->add('datetype', ChoiceType::class, array('required' => true ))
			->add('name', TextType::class, array('required' => false ))
			
			->add('date', DateType::class, array('required' => false, 'widget' => 'single_text',

                // prevents rendering it as type="date", to avoid HTML5 date pickers
                'html5' => false,
				"label"=> false,
                // adds a class that can be selected in JavaScript
                'attr' => [ 'style'=> 'visibility:hidden']))
			->add('year', TextType::class, array('required' => true))
			->add('date_format', TextType::class, array('required' => false, "label"=> false, "invalid_message"=> "Date is not valid",'attr' => [ 'style'=> 'visibility:hidden']))
			->add('date_year', TextType::class, array('required' => false, 'mapped' => false))
			->add('date_month', ChoiceType::class, array('choices'=>array_combine(range(1,12), range(1,12)),'required' => false, 'mapped' => false))
			->add('date_day', ChoiceType::class, array('choices'=>array_combine(range(1,31), range(1,31)),'required' => false, 'mapped' => false))
		;
		 $builder->get('datetype')->resetViewTransformers();
		 $builder->get('date_year')->resetViewTransformers();
		 $builder->get('date_month')->resetViewTransformers();
		 $builder->get('date_year')->resetViewTransformers();
		
		$builder->addEventListener(
		  FormEvents::PRE_SET_DATA,    
					  function(FormEvent $event) { 
						$dcontribution = $event->getData();
						if (null === $dcontribution) {
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
            'data_class' => 'AppBundle\Entity\Dcontribution'
        ));
		$resolver->setDefaults([
        'validation_groups' => false,
		]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()  {
        return 'appbundle_dcontribution';
    }
}
