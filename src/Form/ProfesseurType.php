<?php

namespace App\Form;

use App\Entity\Classe;
use App\Entity\Module;
use App\Entity\Professeur;
use App\Repository\ProfesseurRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\NotNull;

class ProfesseurType extends AbstractType{
    private ProfesseurRepository $professeurRepository;

    public function __construct(ProfesseurRepository $professeurRepository){
       $this->professeurRepository = $professeurRepository;
    }    

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class,[
                "attr"=>[
                    "class"=>"inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400'],
            ])
            ->add('password',PasswordType::class,[
                "attr"=>[
                    "class"=>"inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400'],
            ])
            ->add('nomComplet',TextType::class,[
                "attr"=>[
                    "class"=>"inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400'],
            ])
            ->add('specialite',TextType::class,[
                "attr"=>[
                    "class"=>"inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400'],
            ])
            ->add('grade',ChoiceType::class,[
                "choices"=>$this->professeurRepository->getGrades(),
                "attr"=>[
                    "class"=>"inline-flex w-full px-4 h-10 bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400']
            ])
            ->add('modules',EntityType::class, [
                // looks for choices from this entity
                'class' => Module::class,
                'mapped' => false,
                // uses the User.username property as the visible option string
                'choice_label' => 'libelle',
                // used to render a select box, check boxes or radios
                'multiple' => true,
                'expanded' => false,
                "attr"=>[
                    "class"=>"select2 inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    "style"=>"justify-content: space-between;"
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400'],
            ])
            ->add('classes',EntityType::class, [
                'class' => Classe::class,
                'mapped' => false,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => false,
                "attr"=>[
                    "class"=>"select2 inline-flex w-full px-4 h-10 max-h-fit bg-gray-50 dark:bg-gray-700 border border-gray-300 text-gray-900 rounded focus:ring-blue-500 focus:border-blue-500 block dark:border-gray-600 dark:placeholder-gray-400 dark:text-white",
                    "style"=>"justify-content: space-between;",
                ],
                'label_attr'=> ['class'=> 'text-gray-900 dark:text-gray-400'],
            ])
            ->add('save',SubmitType::Class, [
                'label'=> 'Enregistrer',
                "attr"=>[
                    "class"=>"px-4 py-2 text-sm font-medium leading-5 text-red-600 transition-colors duration-150 bg-transparent border border-red-600 rounded-lg hover:text-white hover:bg-red-600   focus:outline-none focus:shadow-outline-red",
                    "style"=>"margin-left:90%",
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Professeur::class,
        ]);
    }
}
