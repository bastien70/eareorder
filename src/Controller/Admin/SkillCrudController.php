<?php

namespace App\Controller\Admin;

use App\Entity\Skill;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SkillCrudController extends AbstractCrudController
{
    public function __construct(
        private SkillRepository $skillRepository
    ){}

    public static function getEntityFqcn(): string
    {
        return Skill::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $qb->orderBy('entity.position', 'ASC');

        return $qb;
    }

    public function reindexSkillsAction()
    {
        return $this->render('bundles/EasyAdminBundle/skills/reindex.html.twig', [
            'skills' => $this->skillRepository->findAll()
        ]);
    }

    #[
        Route(
            path: '/admin/skills/reindex',
            name: 'admin_skills_reindex',
            methods: ['POST']
        ),
    ]
    public function doReindexSkillsAction(
        Request $request,
        EntityManagerInterface $manager,
    )
    {
        $orderedIds = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if($orderedIds === false) {
            return $this->json(['detail' => 'Invalid body'], 400);
        }

        // from (position) => (id) to (id) => (position)
        $orderedIds = array_flip($orderedIds);

        $skills = $this->skillRepository->findAll();

        foreach($skills as $skill) {
            $skill->setPosition($orderedIds[$skill->getId()]);
        }

        $manager->flush();

        return new JsonResponse();
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addWebpackEncoreEntry('reindex');
    }

    public function configureActions(Actions $actions): Actions
    {
        $reindexAction = Action::new('reindexSkills', 'Réindexer les compétences')
            ->linkToCrudAction('reindexSkillsAction')
            ->setCssClass('btn btn-secondary')
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $reindexAction)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter')
                    ->setIcon('fas fa-plus');
            })
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Nom');
    }
}
