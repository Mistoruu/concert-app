<?php

namespace App\Form;

use App\Entity\Artist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class ArtistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'artiste',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un nom']),
                    new Length(['min' => 2, 'max' => 255]),
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'choices'  => [
                    'Rock' => 'Rock',
                    'Pop' => 'Pop',
                    'Techno' => 'Techno',
                    'Rap' => 'Rap',
                    'Classique' => 'Classique',
                ],
                'label' => 'Genre musical',
            ])
            ->add('biography', TextareaType::class, [
                'label' => 'Biographie / Présentation',
                'attr' => ['rows' => 6],
                'constraints' => [
                    new NotBlank(['message' => 'Une petite description est nécessaire']),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'La biographie doit faire au moins {{ limit }} caractères',
                        'max' => 2000,
                        'maxMessage' => 'La biographie ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artist::class,
        ]);
    }
}
