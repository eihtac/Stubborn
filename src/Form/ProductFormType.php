<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom', 
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un nom de produit.']),
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix', 
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un prix']),
                ],
            ])
            ->add('highlight', CheckboxType::class, [
                'label' => 'En avant ?   ',
                'required' => false,
            ])
            ->add('stockXS', NumberType::class, [
                'label' => 'Stock XS', 
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer le stock XS']),
                ],
            ])
            ->add('stockS', NumberType::class, [
                'label' => 'Stock S', 
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer le stock S']),
                ],
            ])
            ->add('stockM', NumberType::class, [
                'label' => 'Stock M', 
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer le stock M']),
                ],
            ])
            ->add('stockL', NumberType::class, [
                'label' => 'Stock L', 
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer le stock L']),
                ],
            ])
            ->add('stockXL', NumberType::class, [
                'label' => 'Stock XL', 
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer le stock XL']),
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du produit (JPEG ou PNG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M', 
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Formats autorisés : JPEG ou PNG',
                    ])
                ], 
                'attr' => ['accept' => 'image/jpeg,image/png']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}