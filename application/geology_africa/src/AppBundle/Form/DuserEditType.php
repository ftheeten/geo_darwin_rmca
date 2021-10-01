<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

class DuserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('username', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('first_name', null, array('label' => 'form.firstname', 'translation_domain' => 'FOSUserBundle'))
            ->add('last_name', null, array('label' => 'form.lastname', 'translation_domain' => 'FOSUserBundle'))
			->add('enabled', null, array('label' => 'form.enabled', 'translation_domain' => 'FOSUserBundle'))
			->add('email', null, array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
			->add('password', PasswordType::class, array('label' => 'form.password', 'translation_domain' => 'FOSUserBundle'))
		;
    }
}