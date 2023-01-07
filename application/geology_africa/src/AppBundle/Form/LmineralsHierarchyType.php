<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;

class LmineralsHierarchyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')->add('creationDate', DateType::class, [
    // renders it as a single text box
    'widget' => 'single_text',
	'empty_data'=>'2021-10-10',
	'html5' => false,
    'attr' => [
        'class' => 'combinedPickerInput',
        'placeholder' => date('Y-m-d'),
    ],'format' => 'yyyy-M-dd',])->add('author')->add('comment');
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\LmineralsHierarchy'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_lmineralshierarchy';
    }


}
