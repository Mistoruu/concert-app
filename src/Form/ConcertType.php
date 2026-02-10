<?php

namespace App\Form;

use App\Entity\Artist;
use App\Entity\Concert;
use App\Entity\User;
use App\Repository\ArtistRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConcertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('title', null, [
                'label' => 'Nom de la tournée / Concert',
                'constraints' => [new NotBlank()]
            ])
            ->add('description')
            ->add('date', null, [
                'widget' => 'single_text',
                'label' => 'Date et heure'
            ])
            ->add('lieu')

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Concert::class,
            'user' => null,
        ]);
    }
}
