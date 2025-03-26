<?php

namespace App\Controller\Admin;

use App\Entity\Posts;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PostsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Posts::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $cleanPosts = Action::new('clean_posts', 'Nettoyer les articles', 'fa fa-broom')
            ->linkToRoute('admin_clean_posts')
            ->createAsGlobalAction() // Ajout en tant qu'action globale
            ->addCssClass('btn btn-primary');
            
        return $actions
            ->add(Crud::PAGE_INDEX, $cleanPosts);
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title'),
            SlugField::new('slug')->setTargetFieldName('title')->hideOnIndex(),
            TextEditorField::new('content'),
            TextField::new('featuredImage'),
            AssociationField::new('users'),
            AssociationField::new('categories'),
            DateField::new('createdAt'),
            DateField::new('updatedAt')->hideOnIndex(),
            BooleanField::new('isFavorite','Article Favoris'),
        ];
    }
    
}
