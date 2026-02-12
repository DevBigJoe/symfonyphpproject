<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Degree;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DegreeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label'      => 'Name',
                'required'   => true,
                'empty_data' => '',
            ])
            ->add('description', TextareaType::class, [
                'label'      => 'Beschreibung',
                'required'   => true,
                'empty_data' => '',
                'attr'       => [
                    'rows' => 4,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Degree::class,
        ]);
    }
}