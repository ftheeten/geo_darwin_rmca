<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocplanvolType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fid')->add('nombloc')->add('bid')->add('bande')->add('comment');
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
		$resolver->setRequired('entity_manager');
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Docplanvol'
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
        return 'appbundle_docplanvol';
    }


}
