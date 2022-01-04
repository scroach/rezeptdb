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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController {

	/**
	 * @Route("/", name="recipeIndex")
	 */
	public function indexAction() {
		$recipes = $this->getRecipeRepo()->fetchForIndex($this->getUser());
		$randomRecipes = $this->getRecipeRepo()->findRandomRecipes($this->getUser());
		return $this->render('index.html.twig', array(
			'recipes' => $recipes,
			'randomRecipes' => $randomRecipes,
		));
	}

	/**
	 * @Route("/recipes/loadMoreRecipes", name="loadMoreRecipes")
	 */
	public function loadMoreRecipesAction(Request $request) {
		$excludeIds = $request->get('excludeIds');
		$recipes = $this->getRecipeRepo()->fetchForIndex($this->getUser(), $excludeIds);

		if (empty($recipes)) {
			return new JsonResponse(['message' => 'Keine Rezepte mehr :(']);
		} else {
			return $this->render('ajax/recipes.html.twig', array(
				'recipes' => $recipes,
			));
		}
	}

	/**
	 * @Route("/recipes/search/{searchString}", name="searchRecipe")
	 */
	public function searchAction(Request $request, $searchString = null) {
		if ($request->isMethod('post')) {
			$searchString = $request->request->get('search');
			return $this->redirectToRoute('searchRecipe', ['searchString' => urlencode($searchString)]);
		}

		$searchString = urldecode($searchString);
		$recipes = $this->getRecipeRepo()->search($this->getUser(), $searchString);

		if (empty($recipes)) {
			$this->addFlash('warning', sprintf('Ich konnte leider keine Rezepte mit "%s" finden :\'(', $searchString));
		}

		return $this->render('searchResults.html.twig', array(
			'recipes' => $recipes,
			'searchString' => $searchString,
		));
	}

	/**
	 * @Route("/recipes/add", name="addRecipe")
	 */
	public function addAction(Request $request) {
		$url = $this->extractUrlFromQuery($request);

		$recipe = new Recipe();
		$recipe->setOriginUrl($url);
		$recipe->setUser($this->getUser());
		return $this->formAction($request, $recipe);
	}

	/**
	 * @Route("/recipes/edit/{id}", name="editRecipe")
	 */
	public function editAction(Request $request, int $id) {
		return $this->formAction($request, $this->getRecipeById($id));
	}

	/**
	 * @Route("/recipes/show/{id}", name="showRecipe")
	 */
	public function showAction(int $id) {
		$recipe = $this->getRecipeById($id);
		$this->denyAccessUnlessGranted(RecipeVoter::READ, $recipe);
		return $this->render('details.html.twig', ['recipe' => $recipe]);
	}

	/**
	 * @Route("/recipes/editImages/{id}", name="editRecipeImages")
	 */
	public function editImagesAction(Request $request, int $id) {
		$recipe = $this->getRecipeById($id);
		if ($request->isMethod('post')) {
			$imageOrder = explode(',', $_POST['imageOrder']);
			foreach ($recipe->getImages() as $image) {
				$image->setSort(array_search($image->getId(), $imageOrder));
			}
			$this->getDoctrine()->getManager()->flush();
			$this->addFlash('success', 'Die Reihenfolge der Fotos wurde erfolgreich gespeichert!');
			return $this->redirectToRoute('showRecipe', ['id' => $recipe->getId()]);
		}


		return $this->render('editImages.html.twig', ['recipe' => $recipe]);
	}

	/**
	 * @Route("/recipes/delete/{id}", name="deleteRecipe")
	 */
	public function delete($id) {
		$recipe = $this->getRecipeRepo()->find($id);
		$this->denyAccessUnlessGranted(RecipeVoter::EDIT, $recipe);
		$this->getDoctrine()->getManager()->remove($recipe);
		$this->getDoctrine()->getManager()->flush();
		$this->addFlash('success', 'Rezept erfolgreich gelöscht!');
		return $this->redirectToRoute('recipeIndex');
	}

	/**
	 * @Route("/recipes/tags", name="listRecipeTags")
	 */
	public function listTags() {
		$tags = $this->getTagRepo()->findByUser($this->getUser());
		return $this->render('tags.html.twig', array(
			'tags' => $tags,
		));
	}

	/**
	 * @Route("/recipes/tags/{tagLabel}", name="recipeByTag")
	 */
	public function showByTag($tagLabel) {
		$tag = $this->getTagRepo()->findOneByLabel($this->getUser(), $tagLabel);
		if($tag === null) {
            $this->addFlash('error', 'Dieser Tag existiert leider nicht!');
		    return $this->redirectToRoute('listRecipeTags');
        }
		$builder = $this->getRecipeRepo()->createQueryBuilder('r');
		$recipes = $builder->join('r.tags', 't')->where('t = :tag')->setParameter('tag', $tag)->getQuery()->getResult();
		return $this->render('recipesByTags.html.twig', array(
			'recipes' => $recipes,
			'tag' => $tag,
		));
	}

	/**
	 * @Route("/recipes/analyzeUrl", name="parseRecipeUrl")
	 */
	public function analyzeUrlAction(Request $request, RecipeParserService $service) {
		return new JsonResponse($service->analyze($request->query->get('url')));
	}

	/**
	 * @param Request $request
	 * @param Recipe $recipe
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	private function formAction(Request $request, Recipe $recipe) {
		$this->denyAccessUnlessGranted(RecipeVoter::EDIT, $recipe);

		$formBuilder = $this->createFormBuilder($recipe)
			->add('label', TextType::class, ['label' => 'Titel', 'attr' => ['placeholder' => 'Supersaftige Rippchen']])
			->add('description', TextareaType::class, [
				'required' => false
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
				]
			])
			->add('submit', SubmitType::class, ['label' => 'Rezept speichern']);

		$formBuilder->add('ingredientGroups', CollectionType::class, array(
			'label' => 'Zutaten',
			'allow_add' => true,
			'allow_delete' => true,
			'delete_empty' => true,
			'attr' => ['class' => 'ingredientGroupList'],
			'by_reference' => false,
			'prototype_name' => '__groupcounter__',
			'entry_type' => IngredientGroupType::class,
			'entry_options' => array(
				'label' => false,
				'required' => false,
				'attr' => ['placeholder' => '100 g Mehl, 2 EL Zucker, ...']
			),
		));

		$form = $formBuilder->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {


			$this->processSubmittedTags($recipe);
			$this->processFileUploads($recipe);
			$this->processRemoteImages($recipe);
			$this->removeNoLongerUsedImages($recipe);
			$recipe->removeEmptyIngredients();

			$this->getDoctrine()->getManager()->persist($recipe);
			$this->getDoctrine()->getManager()->flush();

			$this->addFlash('success', 'Rezept erfolgreich gespeichert!');
			return $this->redirectToRoute('showRecipe', ['id' => $recipe->getId()]);
		}

		$existingTags = array_map(function (Tag $tag) {
			return ['value' => $tag->getLabel(), 'text' => $tag->getLabel()];
		}, $this->getTagRepo()->findByUser($this->getUser()));


		return $this->render('form.html.twig', array(
			'form' => $form->createView(),
			'recipe' => $recipe,
			'existingTags' => $existingTags,
		));
	}

	private function removeNoLongerUsedImages(Recipe $recipe): void {
		foreach ($recipe->getImages() as $image) {
			if (!isset($_POST['existingImages']) || !in_array($image->getId(), $_POST['existingImages'])) {
				$this->getDoctrine()->getManager()->remove($image);
			}
		}
	}

	/**
	 * @param Recipe $recipe
	 */
	private function processFileUploads(Recipe $recipe): void {
		/** @var UploadedFile $file */
		foreach ($recipe->getFiles() as $file) {
			$fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
			// moves the file to the directory where brochures are stored
			$file->move(
				$this->getParameter('upload_directory'),
				$fileName
			);

			$image = new Image();
			$image->setRecipe($recipe);
			$image->setLocalFileName($fileName);
			$this->getDoctrine()->getManager()->persist($image);
			$recipe->addImage($image);
		}
	}

	private function generateUniqueFileName() {
		// md5() reduces the similarity of the file names generated by
		// uniqid(), which is based on timestamps
		return md5(uniqid());
	}

	/**
	 * @param Recipe $recipe
	 */
	private function processSubmittedTags(Recipe $recipe): void {
		$tags = $recipe->getTagsString();
		$tagRepo = $this->getTagRepo();
		$recipe->getTags()->clear();
		foreach (explode(',', $tags) as $tagName) {
			if (empty($tagName)) {
				continue;
			}

			$tag = $tagRepo->findOneByLabel($this->getUser(), $tagName);
			if (!$tag) {
				$tag = new Tag();
				$tag->setUser($this->getUser());
				$tag->setLabel($tagName);
				$this->getDoctrine()->getManager()->persist($tag);
			}

			$recipe->getTags()->add($tag);
		}
	}

	/**
	 * @param Recipe $recipe
	 */
	private function processRemoteImages(Recipe $recipe): void {
		$images = [];
		foreach ($_POST['images'] ?? [] as $imageUrl) {
			$image = new Image();
			$image->setRecipe($recipe);
			$image->setUrl($imageUrl);
			$this->downloadRemoteImage($image);
			$this->getDoctrine()->getManager()->persist($image);
			$images[] = $image;
		}
	}

	public function downloadRemoteImage(Image $image) {
		$uploadDirectory = $this->getParameter('upload_directory');
		if (!file_exists($uploadDirectory)) {
			mkdir($uploadDirectory, 0777, true);
		}
		$localPath = $uploadDirectory.'/'.$this->generateUniqueFileName();
		copy($image->getUrl(), $localPath);

		$mimeGuesser = MimeTypeGuesser::getInstance();
		$extGuesser = ExtensionGuesser::getInstance();
		$ext = $extGuesser->guess($mimeGuesser->guess($localPath));
		rename($localPath, $localPath.'.'.$ext);
		$image->setLocalFileName(basename($localPath.'.'.$ext));
	}

	/**
	 * @Route("/recipes/searchByIngredients", name="searchByIngredients")
	 */
	public function searchByIngredients(Request $request) {
		$data = ['ingredients' => ['', '', '']];
		$formBuilder = $this->createFormBuilder($data)->add('ingredients', CollectionType::class, array(
			'label' => 'Zutaten',
			'allow_add' => true,
			'allow_delete' => true,
			'entry_type' => TextType::class,
			'attr' => ['class' => 'ui three column grid'],
			'entry_options' => array(
				'label' => false,
				'required' => false,
				'attr' => ['placeholder' => 'Lachs, Bacon, Käse, ...', 'class' => 'column']
			),
		));

		$form = $formBuilder->getForm();
		$form->handleRequest($request);
		$recipes = [];

		if ($form->isSubmitted() && $form->isValid()) {
			$ingredients = array_filter($form->getData()['ingredients'], function ($ingredient) {
				return !empty($ingredient);
			});

			$recipesDB = $this->getRecipeRepo()->fetchForIndex($this->getUser(), [], null);
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

		return $this->render('searchForm.html.twig', array(
			'form' => $form->createView(),
			'recipes' => $recipes,
		));
	}

	private function sortRecipesByRating(Recipe $rec1, Recipe $rec2) {
		return ($rec1->getSearchRating() <=> $rec2->getSearchRating()) * -1;
	}

	/**
	 * Returns a percentage of ingredients matched
	 * @param Recipe $recipe
	 * @param array $wantedIngredients
	 * @return float|int
	 */
	private function rateRecipeByIngriedientsFilter(Recipe $recipe, array $wantedIngredients) {
		$matchedIngredients = 0;

		foreach ($wantedIngredients as $wantedIngredient) {
			foreach ($recipe->getIngredientGroups() as $group) {
				foreach ($group->getIngredients() as $ingredient) {
					if (strpos(strtolower($ingredient->getLabel()), strtolower($wantedIngredient)) !== false) {
						$matchedIngredients++;
						break;
					}
				}
			}
		}

		return $matchedIngredients / count($wantedIngredients);
	}

	/**
	 * @Route("/recipes/downloadMissingRemoteImages")
	 */
	public function downloadMissingRemoteImages() {
		$recipes = $this->getRecipeRepo()->findAll();
		foreach ($recipes as $recipe) {
			foreach ($recipe->getImages() as $image) {
				if (!$image->getLocalFileName()) {
					$this->downloadRemoteImage($image);
					$this->addFlash('success', sprintf('Downloaded image id %d from url: %s', $image->getId(), $image->getUrl()));
					$this->getDoctrine()->getManager()->flush();
				}
			}
		}
		return $this->redirectToRoute('showRecipe', ['id' => $recipe->getId()]);
	}

	private function getRecipeById(int $id): Recipe {
		/** @var Recipe|null $recipe */
		$recipe = $this->getRecipeRepo()->find($id);
		if (!$recipe) {
			throw $this->createNotFoundException(sprintf('Rezept mit ID %d existiert nicht ', $id));
		}
		return $recipe;
	}

	private function getRecipeRepo(): RecipeRepository {
		return $this->getDoctrine()->getRepository(Recipe::class);
	}

	private function getTagRepo(): TagRepository {
		return $this->getDoctrine()->getRepository(Tag::class);
	}

	protected function getUser(): User {
		return parent::getUser();
	}

	/**
	 * Extracts an url provided in the text query parameter by Android PWA sharing.
	 * Regex thanks to https://stackoverflow.com/a/36564776/2424814
	 */
	private function extractUrlFromQuery(Request $request): string {
		$text = $request->query->get('text');
		preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $text, $match);
		return $match[0][0] ?? '';
	}
}
