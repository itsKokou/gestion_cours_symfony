<?php

namespace App\Form;

use App\Entity\Classe;
use App\Entity\Inscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotNull;

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder 
            ->add('etudiant',EtudiantType::class, [
                'required'=>false, 
                'label' => false,
            ])
            ->add('classe',EntityType::class, [
                'required' => false,
                'class' => Classe::class,
                'choice_label' => 'libelle',
                "attr"=>[
                    "class"=>"select2 inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                "attr" => [
                    "class" => "px-4 py-2 text-sm font-medium leading-5 text-red-600 transition-colors duration-150 bg-transparent border border-red-600 rounded-lg hover:text-white hover:bg-red-600   focus:outline-none focus:shadow-outline-red",
                    "style" => "margin-left:90%",
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inscription::class,
        ]);
    }
}
