<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('query', SearchType::class, [
            'label' => false,
            'attr' => 
            [
                'placeholder' => 'Rechercher...',
                'class' => 'cs-search__input'
            ]
        ]);
    }
}
