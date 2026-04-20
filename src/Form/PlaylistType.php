<?php
namespace App\Form;

use App\Entity\Playlist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PlaylistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ "name" : seul champ obligatoire selon le cahier des charges.
            // NotBlank déclenche une erreur côté serveur si le champ est vide,
            // même si l'utilisateur désactive la validation HTML5 du navigateur.
            // Length(max:100) correspond à la colonne BDD : varchar(100) dans Playlist.
            ->add('name', TextType::class, [
                'label'       => 'Nom de la playlist',
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom est obligatoire.'
                    ]),
                    new Assert\Length([
                        'max'        => 100,
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            // Champ "description" : facultatif.
            // required: false désactive l'attribut HTML "required" ET la validation Symfony.
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Lie ce formulaire à l'entité Playlist.
        // Symfony appelle automatiquement getName()/setName() et
        // getDescription()/setDescription() lors du préremplissage et de la soumission.
        $resolver->setDefaults([
            'data_class' => Playlist::class,
        ]);
    }
}
