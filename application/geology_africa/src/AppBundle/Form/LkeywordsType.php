<?php

namespace AppBundle\Form;

use AppBundle\Entity\Lkeywords;
use AppBundle\Entity\Lmedium;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class LkeywordsType extends AbstractType {
		
    /**
     * {@inheritdoc}
     */
     public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('wordson', TextType::class);
         $builder->add('wordfather', TextType::class);
		 $builder->add('callback_action', TextType::class, array('mapped' => false));
		 $builder->add('wordson_edit', TextType::class, array('mapped' => false));
    }
     
     /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)  {
		 $resolver->setRequired('entity_manager');

        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Lkeywords'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()  {
        return 'appbundle_lkeywords';
    }
     
}