<?php
namespace AppBundle\Form;

use AppBundle\Entity\Duser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DuserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('username', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('first_name', null, array('label' => 'form.firstname', 'translation_domain' => 'FOSUserBundle'))
            ->add('last_name', null, array('label' => 'form.lastname', 'translation_domain' => 'FOSUserBundle'))
			->add('enabled', null, array('label' => 'form.enabled', 'translation_domain' => 'FOSUserBundle'))
			->add('email', null, array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
			->add('password', null, array('label' => 'form.password', 'translation_domain' => 'FOSUserBundle'))
			->add('id', TextType::class, array('label' => 'form.id', 'translation_domain' => 'FOSUserBundle'))
		;
		
		$builder->addEventListener(
		  FormEvents::PRE_SET_DATA,    
					  function(FormEvent $event) { 
						$duser = $event->getData();
						if (null === $duser) {
						  return; 
						}
					  }
		);
    }
		
	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)  {
		 $resolver->setRequired('entity_manager');

		$resolver->setDefaults(array(
			'data_class' => 'AppBundle\Entity\Duser'
		));
	}

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()  {
        return 'appbundle_duser';
    }

}