<?php

namespace AppBundle\Form;

use AppBundle\Entity\Codecollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CollectionType extends AbstractType {
    /**
     * {@inheritdoc}
     */
	
    public function buildForm(FormBuilderInterface $builder, array $options)  {
		/* builder---------*/
        $builder
			->add('codecollection', TextType::class, array('required' => true, 'label' => 'form.code', 'translation_domain' => 'FOSUserBundle'))
            ->add('collection', TextType::class, array('required' => true, 'label' => 'form.collection', 'translation_domain' => 'FOSUserBundle'))
            ->add('typeobjets', TextType::class, array('label' => 'form.typeobjects', 'translation_domain' => 'FOSUserBundle'))
			->add('zoneutilisation',  TextType::class, array('label' => 'form.zoneutilisation', 'translation_domain' => 'FOSUserBundle'))
		;
		
		$builder->addEventListener(
		  FormEvents::PRE_SET_DATA,    
					  function(FormEvent $event) { 
						$codecollection = $event->getData();
						if (null === $codecollection) {
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
            'data_class' => 'AppBundle\Entity\Codecollection'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()  {
        return 'appbundle_dcontribution';
    }
}
