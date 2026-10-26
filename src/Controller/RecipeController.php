<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Recipe;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\Type\IngredientGroupType;
use App\Repository\RecipeRepository;
use App\Repository\TagRepository;
use App\Security\Voter\RecipeVoter;
use App\Service\RecipeParserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecipeRepository $recipeRepository,
        private TagRepository $tagRepository,
        private MimeTypesInterface $mimeTypes,
    ) {
    }

    #[Route('/', name: 'recipeIndex')]
    public function indexAction(): Response
    {
        $recipes = $this->recipeRepository->fetchForIndex($this->getUser());
        $randomRecipes = $this->recipeRepository->findRandomRecipes($this->getUser());

        return $this->render('index.html.twig', [
            'recipes' => $recipes,
            'randomRecipes' => $randomRecipes,
        ]);
    }

    #[Route('/recipes/loadMoreRecipes', name: 'loadMoreRecipes')]
    public function loadMoreRecipesAction(Request $request): Response
    {
        $excludeIds = $request->query->all()['excludeIds'] ?? [];
        $recipes = $this->recipeRepository->fetchForIndex($this->getUser(), $excludeIds);

        if (empty($recipes)) {
            return new JsonResponse(['message' => 'Keine Rezepte mehr :(']);
        }

        return $this->render('ajax/recipes.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recipes/search/{searchString}', name: 'searchRecipe')]
    public function searchAction(Request $request, ?string $searchString = null): Response
    {
        if ($request->isMethod('post')) {
            $searchString = $request->request->get('search');
            return $this->redirectToRoute('searchRecipe', ['searchString' => urlencode((string) $searchString)]);
        }

        $searchString = urldecode((string) $searchString);
        $recipes = $this->recipeRepository->search($this->getUser(), $searchString);

        if (empty($recipes)) {
            $this->addFlash('warning', sprintf('Ich konnte leider keine Rezepte mit "%s" finden :\'(', $searchString));
        }

        return $this->render('searchResults.html.twig', [
            'recipes' => $recipes,
            'searchString' => $searchString,
        ]);
    }

    #[Route('/recipes/add', name: 'addRecipe')]
    public function addAction(Request $request): Response
    {
        $url = $this->extractUrlFromQuery($request);

        $recipe = new Recipe();
        $recipe->setOriginUrl($url);
        $recipe->setUser($this->getUser());

        return $this->formAction($request, $recipe);
    }

    #[Route('/recipes/edit/{id}', name: 'editRecipe')]
    public function editAction(Request $request, int $id): Response
    {
        return $this->formAction($request, $this->getRecipeById($id));
    }

    #[Route('/recipes/show/{id}', name: 'showRecipe')]
    public function showAction(int $id): Response
    {
        $recipe = $this->getRecipeById($id);
        $this->denyAccessUnlessGranted(RecipeVoter::READ, $recipe);

        return $this->render('details.html.twig', ['recipe' => $recipe]);
    }

    #[Route('/recipes/editImages/{id}', name: 'editRecipeImages')]
    public function editImagesAction(Request $request, int $id): Response
    {
        $recipe = $this->getRecipeById($id);
        if ($request->isMethod('post')) {
            $imageOrder = explode(',', (string) $request->request->get('imageOrder', ''));
            foreach ($recipe->getImages() as $image) {
                $image->setSort(array_search($image->getId(), $imageOrder));
            }
            $this->entityManager->flush();
            $this->addFlash('success', 'Die Reihenfolge der Fotos wurde erfolgreich gespeichert!');

            return $this->redirectToRoute('showRecipe', ['id' => $recipe->getId()]);
        }

        return $this->render('editImages.html.twig', ['recipe' => $recipe]);
    }

    #[Route('/recipes/delete/{id}', name: 'deleteRecipe')]
    public function delete(int $id): Response
    {
        $recipe = $this->recipeRepository->find($id);
        if (!$recipe) {
            throw $this->createNotFoundException(sprintf('Rezept mit ID %d existiert nicht ', $id));
        }
        $this->denyAccessUnlessGranted(RecipeVoter::EDIT, $recipe);
        $this->entityManager->remove($recipe);
        $this->entityManager->flush();
        $this->addFlash('success', 'Rezept erfolgreich gelöscht!');

        return $this->redirectToRoute('recipeIndex');
    }

    #[Route('/recipes/tags', name: 'listRecipeTags')]
    public function listTags(): Response
    {
        $tags = $this->tagRepository->findByUser($this->getUser());

        return $this->render('tags.html.twig', [
            'tags' => $tags,
        ]);
    }

    #[Route('/recipes/tags/{tagLabel}', name: 'recipeByTag')]
    public function showByTag(string $tagLabel): Response
    {
        $tag = $this->tagRepository->findOneByLabel($this->getUser(), $tagLabel);
        if ($tag === null) {
            $this->addFlash('error', 'Dieser Tag existiert leider nicht!');

            return $this->redirectToRoute('listRecipeTags');
        }
        $builder = $this->recipeRepository->createQueryBuilder('r');
        $recipes = $builder->join('r.tags', 't')->where('t = :tag')->setParameter('tag', $tag)->getQuery()->getResult();

        return $this->render('recipesByTags.html.twig', [
            'recipes' => $recipes,
            'tag' => $tag,
        ]);
    }

    #[Route('/recipes/analyzeUrl', name: 'parseRecipeUrl')]
    public function analyzeUrlAction(Request $request, RecipeParserService $service): JsonResponse
    {
        return new JsonResponse($service->analyze((string) $request->query->get('url')));
    }

    private function formAction(Request $request, Recipe $recipe): Response
    {
        $this->denyAccessUnlessGranted(RecipeVoter::EDIT, $recipe);

        $formBuilder = $this->createFormBuilder($recipe)
            ->add('label', TextType::class, ['label' => 'Titel', 'attr' => ['placeholder' => 'Supersaftige Rippchen']])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('originUrl', UrlType::class)
            ->add('tagsString', TextType::class)
            ->add('effort', TextType::class, ['label' => 'Aufwand', 'attr' => ['placeholder' => '20 Minuten']])
            ->add('files', FileType::class, [
                'label' => 'Fotos',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'multiple' => 'multiple',
                    'accept' => 'image/*',
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Rezept speichern']);

        $formBuilder->add('ingredientGroups', CollectionType::class, [
            'label' => 'Zutaten',
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'attr' => ['class' => 'ingredientGroupList'],
            'by_reference' => false,
            'prototype_name' => '__groupcounter__',
            'entry_type' => IngredientGroupType::class,
            'entry_options' => [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => '100 g Mehl, 2 EL Zucker, ...'],
            ],
        ]);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processSubmittedTags($recipe);
            $this->processFileUploads($recipe);
            $this->processRemoteImages($recipe);
            $this->removeNoLongerUsedImages($recipe);
            $recipe->removeEmptyIngredients();

            $this->entityManager->persist($recipe);
            $this->entityManager->flush();

            $this->addFlash('success', 'Rezept erfolgreich gespeichert!');

            return $this->redirectToRoute('showRecipe', ['id' => $recipe->getId()]);
        }

        $existingTags = array_map(function (Tag $tag) {
            return ['value' => $tag->getLabel(), 'text' => $tag->getLabel()];
        }, $this->tagRepository->findByUser($this->getUser()));

        return $this->render('form.html.twig', [
            'form' => $form->createView(),
            'recipe' => $recipe,
            'existingTags' => $existingTags,
        ]);
    }

    private function removeNoLongerUsedImages(Recipe $recipe): void
    {
        foreach ($recipe->getImages() as $image) {
            if (!isset($_POST['existingImages']) || !in_array($image->getId(), $_POST['existingImages'])) {
                $this->entityManager->remove($image);
            }
        }
    }

    private function processFileUploads(Recipe $recipe): void
    {
        /** @var UploadedFile $file */
        foreach ($recipe->getFiles() ?? [] as $file) {
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('upload_directory'),
                $fileName
            );

            $image = new Image();
            $image->setRecipe($recipe);
            $image->setLocalFileName($fileName);
            $this->entityManager->persist($image);
            $recipe->addImage($image);
        }
    }

    private function generateUniqueFileName(): string
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    private function processSubmittedTags(Recipe $recipe): void
    {
        $tags = $recipe->getTagsString();
        $recipe->getTags()->clear();
        foreach (explode(',', (string) $tags) as $tagName) {
            if (empty($tagName)) {
                continue;
            }

            $tag = $this->tagRepository->findOneByLabel($this->getUser(), $tagName);
            if (!$tag) {
                $tag = new Tag();
                $tag->setUser($this->getUser());
                $tag->setLabel($tagName);
                $this->entityManager->persist($tag);
            }

            $recipe->getTags()->add($tag);
        }
    }

    private function processRemoteImages(Recipe $recipe): void
    {
        foreach ($_POST['images'] ?? [] as $imageUrl) {
            $image = new Image();
            $image->setRecipe($recipe);
            $image->setUrl($imageUrl);
            $this->downloadRemoteImage($image);
            $this->entityManager->persist($image);
        }
    }

    public function downloadRemoteImage(Image $image): void
    {
        $uploadDirectory = $this->getParameter('upload_directory');
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }
        $localPath = $uploadDirectory.'/'.$this->generateUniqueFileName();
        copy($image->getUrl(), $localPath);

        $mimeType = $this->mimeTypes->guessMimeType($localPath);
        $extensions = $mimeType ? $this->mimeTypes->getExtensions($mimeType) : [];
        if (isset($extensions[0])) {
            $ext = $extensions[0];
        } else {
            $ext = pathinfo(parse_url($image->getUrl(), PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
        }
        rename($localPath, $localPath.'.'.$ext);
        $image->setLocalFileName(basename($localPath.'.'.$ext));
    }

    #[Route('/recipes/searchByIngredients', name: 'searchByIngredients')]
    public function searchByIngredients(Request $request): Response
    {
        $data = ['ingredients' => ['', '', '']];
        $formBuilder = $this->createFormBuilder($data)->add('ingredients', CollectionType::class, [
            'label' => 'Zutaten',
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => TextType::class,
            'attr' => ['class' => 'ui three column grid'],
            'entry_options' => [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Lachs, Bacon, Käse, ...', 'class' => 'column'],
            ],
        ]);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        $recipes = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $ingredients = array_filter($form->getData()['ingredients'], function ($ingredient) {
                return !empty($ingredient);
            });

            $recipesDB = $this->recipeRepository->fetchForIndex($this->getUser(), [], null);
            $recipes = [];
            foreach ($recipesDB as $recipe) {
                $recipe->setSearchRating($this->rateRecipeByIngriedientsFilter($recipe, $ingredients));
                if ($recipe->getSearchRating() > 0) {
                    $recipes[] = $recipe;
                }
            }
            usort($recipes, [$this, 'sortRecipesByRating']);
        }

        if ($form->isSubmitted() && empty($recipes)) {
            $this->addFlash('warning', 'Ich konnte leider keine Rezepte mit diesen Zutaten finden :\'(');
        }

        return $this->render('searchForm.html.twig', [
            'form' => $form->createView(),
            'recipes' => $recipes,
        ]);
    }

    private function sortRecipesByRating(Recipe $rec1, Recipe $rec2): int
    {
        return ($rec1->getSearchRating() <=> $rec2->getSearchRating()) * -1;
    }

    /**
     * Returns a percentage of ingredients matched
     */
    private function rateRecipeByIngriedientsFilter(Recipe $recipe, array $wantedIngredients): float|int
    {
        $matchedIngredients = 0;

        foreach ($wantedIngredients as $wantedIngredient) {
            foreach ($recipe->getIngredientGroups() as $group) {
                foreach ($group->getIngredients() as $ingredient) {
                    if (str_contains(strtolower((string) $ingredient->getLabel()), strtolower((string) $wantedIngredient))) {
                        $matchedIngredients++;
                        break;
                    }
                }
            }
        }

        return $matchedIngredients / count($wantedIngredients);
    }

    #[Route('/recipes/downloadMissingRemoteImages')]
    public function downloadMissingRemoteImages(): Response
    {
        $recipes = $this->recipeRepository->findAll();
        $recipe = null;
        foreach ($recipes as $recipe) {
            foreach ($recipe->getImages() as $image) {
                if (!$image->getLocalFileName()) {
                    $this->downloadRemoteImage($image);
                    $this->addFlash('success', sprintf('Downloaded image id %d from url: %s', $image->getId(), $image->getUrl()));
                    $this->entityManager->flush();
                }
            }
        }

        if ($recipe === null) {
            return $this->redirectToRoute('recipeIndex');
        }

        return $this->redirectToRoute('showRecipe', ['id' => $recipe->getId()]);
    }

    private function getRecipeById(int $id): Recipe
    {
        /** @var Recipe|null $recipe */
        $recipe = $this->recipeRepository->find($id);
        if (!$recipe) {
            throw $this->createNotFoundException(sprintf('Rezept mit ID %d existiert nicht ', $id));
        }

        return $recipe;
    }

    protected function getUser(): User
    {
        return parent::getUser();
    }

    /**
     * Extracts an url provided in the text query parameter by Android PWA sharing.
     * Regex thanks to https://stackoverflow.com/a/36564776/2424814
     */
    private function extractUrlFromQuery(Request $request): string
    {
        $text = (string) $request->query->get('text');
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $text, $match);

        return $match[0][0] ?? '';
    }
}
