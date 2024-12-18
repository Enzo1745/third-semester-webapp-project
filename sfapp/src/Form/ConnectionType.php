<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

class ConnectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // connection form
        $builder
        ->add('username')
        ->add('password', PasswordType::class)
        ->add('button', SubmitType::class);//Button to submit the conexion form
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
