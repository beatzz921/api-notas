<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CambiarContrasenaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'password',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Las dos contraseñas deben coincidir',
                    'required' => true,
                    'first_options' => [
                        'label' => 'Nueva Contraseña'
                    ],
                    'second_options' => [
                        'label' => 'Confirmar Nueva Contraseña'
                    ]

                ]
            )
            ->add('save', SubmitType::class, [
                'label' => 'Guardar',
                'attr' => [
                    'style' => 'width:100%'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
