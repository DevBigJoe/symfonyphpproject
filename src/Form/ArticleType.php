<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Article;
use App\Entity\Enum\ArticleType as ArticleTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

final class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titel',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Inhalt',
                'required' => true,
                'empty_data' => '',
                'attr' => ['rows' => 10],
            ])
            ->add('articleType', EnumType::class, [
                'label' => 'Artikel-Typ',
                'class' => ArticleTypeEnum::class,
                'required' => true,
                'placeholder' => 'Bitte wählen…',
                'choice_label' => fn (ArticleTypeEnum $choice) => 'article_type.' . $choice->name,
            ])
            ->add('published', CheckboxType::class, [
                'label' => 'Veröffentlicht',
                'required' => false,
            ])
            ->add('uploadFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '10M',
                        mimeTypes: ['image/*', 'application/pdf'],
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
