<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class DcontributorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('idcontributor', TextType::class, array('required' => true))
		->add('people')
		->add('peoplefonction', ChoiceType::class, array('required' => false))
		->add('peopletitre', ChoiceType::class, array('required' => false))
		->add('peoplestatut', ChoiceType::class, array('required' => false))
		->add('institut', ChoiceType::class, array('required' => false));
		
		 //disable validation of choice (new constraints)
		 $builder->get('peoplefonction')->resetViewTransformers();
		 $builder->get('peopletitre')->resetViewTransformers();
		 $builder->get('peoplestatut')->resetViewTransformers();
		 $builder->get('institut')->resetViewTransformers();
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
		//added entity manager
		$resolver->setRequired('entity_manager');
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Dcontributor'
        ));
		$resolver->setDefaults([
        'validation_groups' => false,
		]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_dcontributor';
    }


}
