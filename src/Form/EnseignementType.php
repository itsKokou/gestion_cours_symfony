<?php

namespace App\Form;

use App\Entity\Classe;
use App\Entity\Module;
use App\Entity\Enseignement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnseignementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('anneeScolaire')
            // ->add('professeur')
            ->add('modules', EntityType::class, [
                // looks for choices from this entity
                'class' => Module::class,
                'mapped' => false,
                // uses the User.username property as the visible option string
                'choice_label' => 'libelle',
                // used to render a select box, check boxes or radios
                'multiple' => true,
                'expanded' => false,
                "attr" => [
                    "class" => "select2 inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    "style" => "justify-content: space-between;"
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400'],
            ])
            ->add('classes', EntityType::class, [
                'class' => Classe::class,
                'mapped' => false,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => false,
                "attr" => [
                    "class" => "select2 inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    "style" => "justify-content: space-between;",
                ],
                'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Enseignement::class,
        ]);
    }
}
