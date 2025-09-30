<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType as EmailField;
use Symfony\Component\Form\Extension\Core\Type\TextareaType as Textarea;
use Symfony\Component\Form\Extension\Core\Type\TextType as TextField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextField::class, [
                'label' => 'Votre nom',
                'constraints' => [
                    new Assert\NotBlank(message: 'Merci d’indiquer votre nom.'),
                    new Assert\Length(max: 100),
                ],
            ])
            ->add('email', EmailField::class, [
                'label' => 'Votre email',
                'constraints' => [
                    new Assert\NotBlank(message: 'Merci d’indiquer votre email.'),
                    new Assert\Email(message: 'Email invalide.'),
                ],
            ])
            ->add('message', Textarea::class, [
                'label' => 'Votre message',
                'constraints' => [
                    new Assert\NotBlank(message: 'Merci d’écrire un message.'),
                    new Assert\Length(min: 10, minMessage: '10 caractères minimum'),
                ],
                'attr' => ['rows' => 6],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // pas d’entité : on utilise un simple tableau de données
        ]);
    }
}
