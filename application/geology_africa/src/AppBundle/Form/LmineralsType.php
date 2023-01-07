<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use  Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\LmineralsHierarchy;

class LmineralsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	    
        $builder->add('idmineral')
		->add('rank', ChoiceType::class, array('choices'  => array('mineral' => 'mineral','class' => 'class','group' => 'group')))
		->add('fmname')
		->add('mname')
		->add('mformula')
		->add('fmparent')
		->add('mparent')
			->add('lmineralshierarchy', EntityType::class, array('class' => LmineralsHierarchy::class,));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
		$resolver->setRequired('entity_manager');
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Lminerals'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_lminerals';
    }


}
