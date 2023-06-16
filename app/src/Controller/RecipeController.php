<?php
/**
 * Recipe controller.
 */

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Recipe;
use App\Entity\User;
use App\Form\Type\CommentType;
use App\Form\Type\RecipeType;
use App\Service\CommentService;
use App\Service\RecipeServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RecipeController.
 */
#[Route('/')]
class RecipeController extends AbstractController
{
    /**
     * Recipe service.
     */
    private RecipeServiceInterface $recipeService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor
     * @param RecipeServiceInterface $recipeService
     * @param TranslatorInterface    $translator
     */
    public function __construct(RecipeServiceInterface $recipeService, TranslatorInterface $translator)
    {
        $this->recipeService = $recipeService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'recipe_index',
        methods: 'GET'
    )]
    public function index(Request $request): Response
    {
        $pagination = $this->recipeService->getPaginatedList(
            $request->query->getInt('page', 1),
        );

        return $this->render(
            'recipe/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * Show action
     *
     * @param Recipe         $recipe
     * @param CommentService $commentService
     *
     * @return Response
     */
    #[Route(
        '/{id}',
        name: 'recipe_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Recipe $recipe, CommentService $commentService): Response
    {
        return $this->render(
            'recipe/show.html.twig',
            [
                'recipe' => $recipe,
                'comments' => $commentService->findBy(['recipe' => $recipe]),
            ]
        );
    }

    /**
     * Create action
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/create', name: 'recipe_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $recipe = new Recipe();
        $recipe->setAuthor($user);
        $form = $this->createForm(
            RecipeType::class,
            $recipe,
            ['action' => $this->generateUrl('recipe_create')]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->recipeService->save($recipe);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render('recipe/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Recipe  $recipe  Recipe entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'recipe_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Recipe $recipe): Response
    {
        $form = $this->createForm(
            RecipeType::class,
            $recipe,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('recipe_edit', ['id' => $recipe->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->recipeService->save($recipe);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render(
            'recipe/edit.html.twig',
            [
                'form' => $form->createView(),
                'recipe' => $recipe,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Recipe  $recipe  Recipe entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'recipe_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, Recipe $recipe): Response
    {
        $form = $this->createForm(
            FormType::class,
            $recipe,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('recipe_delete', ['id' => $recipe->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->recipeService->delete($recipe);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render(
            'recipe/delete.html.twig',
            [
                'form' => $form->createView(),
                'task' => $recipe,
            ]
        );
    }


    /**
     * Comment action
     * @param Request        $request
     * @param Recipe         $recipe
     * @param CommentService $commentService
     *
     * @return Response
     *
     * @IsGranted("ROLE_USER")
     */
    #[Route('/{id}/comment', name: 'recipe_comment', requirements: ['id' => '[1-9]\d*'], methods: 'GET|POST')]
    public function comment(Request $request, Recipe $recipe, CommentService $commentService): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setRecipe($recipe);
            $comment->setAuthor($this->getUser());
            $comment->setCreatedAt(new \DateTimeImmutable());
            $commentService->save($comment);

            $this->addFlash('success', 'message.added_successfully');

            return $this->redirectToRoute('recipe_show', ['id' => $recipe->getId()]);
        }

        return $this->render(
            'recipe/comment.html.twig',
            [
                'form' => $form->createView(),
                'recipe' => $recipe,
                'comment' => $comment,
            ]
        );
    }
}
