<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\FormationType as FormationTypeEntity;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Aufstellungsname',
                'attr' => ['placeholder' => 'z.B. Standard 4-4-2']
            ])
            ->add('formationType', EntityType::class, [
                'label' => 'Sportart',
                'class' => FormationTypeEntity::class,
                'choice_label' => 'name',
                'placeholder' => 'Sportart wählen',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
