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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class DdocumentEditType extends AbstractType {
		
    /**
     * {@inheritdoc}
     */
	
    public function buildForm(FormBuilderInterface $builder, array $options)  {
		$em = $options['entity_manager'];
		
		/* builder---------*/
        $builder
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
            'data_class' => 'AppBundle\Entity\Ddocument'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()  {
        return 'appbundle_ddocument';
    }
}
