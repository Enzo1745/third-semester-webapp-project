<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class DateCaptureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajout des deux champs de type DateTime
        $builder
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Date début',
                'widget' => 'single_text', // Pour un champ de type texte avec date et heure
                'required' => true, // Si vous souhaitez rendre ce champ obligatoire
            ])
            ->add('dateFin', DateTimeType::class, [
                'label' => 'Date fin',
                'widget' => 'single_text', // Idem pour ce champ
                'required' => true, // Si vous souhaitez rendre ce champ obligatoire
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configurez les options du formulaire ici, si nécessaire
        ]);
    }
}
