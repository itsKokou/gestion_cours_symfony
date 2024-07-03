<?php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\Classe;
use App\Entity\Module;
use App\Entity\Semestre;
use App\Entity\Professeur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('semestre',EntityType::class, [
                'class' => Semestre::class,
                'choice_label' => 'libelle',
                'multiple' => false,
                'expanded' => false,
                "attr"=>[
                    "class"=>"select2 select-semestre inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    "style"=>"justify-content: space-between;"
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400']
            ])
            ->add('module',EntityType::class, [
                "required" => false,
                'class' => Module::class,
                'choice_label' => 'libelle',
                'multiple' => false,
                'expanded' => false,
                "attr"=>[
                    "class"=>"select2 select-module inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    "style"=>"justify-content: space-between;",
                    
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400']
            ])
            
            // ->add('nbreHeureTotal',TextType::class,[
            //     "required"=>false,
            //     "attr" => [
            //         "class" => "inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
            //     ],
            //     'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400']
            // ])
            // ->add('classes', EntityType::class, [
            //     "required" => false,
            //     'class' => Classe::class,
            //     'choice_label' => 'libelle',
            //     'multiple' => true,
            //     'expanded' => false,
            //     "attr" => [
            //         "class" => "select2 inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
            //         "style" => "justify-content: space-between;"
            //     ],
            //     'label_attr' => ['class' => 'text-gray-900 dark:text-gray-400'],
            // ])
            // ->add('save', SubmitType::class, [
            //     'label' => 'Enregistrer',
            //     "attr" => [
            //         "class" => "px-4 py-2 text-sm font-medium leading-5 text-red-600 transition-colors duration-150 bg-transparent border border-red-600 rounded-lg hover:text-white hover:bg-red-600   focus:outline-none focus:shadow-outline-red",
            //         "style" => "margin-left:90%",
            //     ]
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
        ]);
    }
}
