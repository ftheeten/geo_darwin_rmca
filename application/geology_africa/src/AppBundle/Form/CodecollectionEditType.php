<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CodecollectionEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('codecollection', null, array('label' => 'form.code', 'translation_domain' => 'FOSUserBundle'))
            ->add('collection', null, array('label' => 'form.collection', 'translation_domain' => 'FOSUserBundle'))
            ->add('typeobjets', null, array('label' => 'form.typeobjects', 'translation_domain' => 'FOSUserBundle'))
			->add('zoneutilisation', null, array('label' => 'form.zoneutilisation', 'translation_domain' => 'FOSUserBundle'))
		;
    }



}