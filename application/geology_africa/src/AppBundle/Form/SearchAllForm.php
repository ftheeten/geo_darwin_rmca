<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SearchAllForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('document_type', ChoiceType::class, [
                    'choices'  => [
					     "All"=>"all",
                         "Sample"=>'sample', 
                         "Map"=>"map",
                         "Satellite image"=>'satellite',
                         "Aerial image"=> "aerial",
                        'Film' => "film",
                    ],
                    "multiple"=> true
                ]);
         $builder->add("contributor", ChoiceType::class, array('required' => false));
		 $builder->add("role", ChoiceType::class, array('required' => false));
		 $builder->add('wkt_search', TextType::class, array('required' => false));
    }
    
    public function getName()
    {
        return "search_all_bootstrap_ajax";
    }
}