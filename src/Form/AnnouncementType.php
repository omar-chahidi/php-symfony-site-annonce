<?php

namespace App\Form;

use App\Entity\Announcement;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ["label" => "Titre", "required" => true])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'title'
            ])
            ->add('announcementContent', TextareaType::class, [
                'label'=>'Texte annonce',
                    'attr'=> ['rows'=>'15']
            ])
            ->add('price', NumberType::class, [
                "label" => "Prix",
                "html5" => true,
                "attr" =>["min" => "1", "max" => "999999.99", "step" =>".5"]
            ])
            ->add('createdAt', DateType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text'
            ])

            ->add('submit', SubmitType::class, ["label" => "Valider", "attr" => ["class" => "btn btn-success"]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Announcement::class,
        ]);
    }
}
