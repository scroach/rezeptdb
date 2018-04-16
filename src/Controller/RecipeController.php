<?php


namespace App\Controller;

use App\Entity\Image;
use App\Entity\Recipe;
use App\Entity\Tag;
use App\Form\Type\IngredientType;
use App\Service\ChefkochDOMParser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends Controller {

	/**
	 * @Route("/recipes", name="recipeIndex")
	 */
	public function indexAction() {
		$recipes = $this->getDoctrine()->getRepository(Recipe::class)->findAll();
		return $this->render('index.html.twig', array(
			'recipes' => $recipes,
		));
	}

	/**
	 * @Route("/recipes/add", name="addRecipe")
	 */
	public function addAction(Request $request) {
		// creates a task and gives it some dummy data for this example
		$recipe = new Recipe();
		return $this->formAction($request, $recipe);
	}

	/**
	 * @Route("/recipes/edit/{id}", name="editRecipe")
	 */
	public function editAction(Request $request, int $id) {
		$recipe = $this->getDoctrine()->getRepository(Recipe::class)->find($id);

		if (!$recipe) {
			throw $this->createNotFoundException(
				'No product found for id '.$id
			);
		}

		return $this->formAction($request, $recipe);
	}

	/**
	 * @Route("/recipes/show/{id}", name="showRecipe")
	 */
	public function showAction($id) {
		$recipe = $this->getDoctrine()->getRepository(Recipe::class)->find($id);

		if (!$recipe) {
			throw $this->createNotFoundException(
				'No product found for id '.$id
			);
		}

		return $this->render('details.html.twig', ['recipe' => $recipe]);
	}

	/**
	 * @Route("/recipes/delete/{id}", name="deleteRecipe")
	 */
	public function delete($id) {
		$recipe = $this->getDoctrine()->getRepository(Recipe::class)->find($id);
		$this->getDoctrine()->getManager()->remove($recipe);
		$this->getDoctrine()->getManager()->flush();
		return $this->redirectToRoute('recipeIndex');
	}

	/**
	 * @Route("/recipes/tags", name="listRecipeTags")
	 */
	public function listTags() {
	}

	/**
	 * @Route("/recipes/tags/{tag}", name="recipeByTag")
	 */
	public function showByTag($id) {
	}

	/**
	 * @Route("/recipes/analyzeUrl", name="parseRecipeUrl")
	 */
	public function analyzeUrlAction(Request $request) {
		// use internal errors so libxml won't throw php warnings/errors on non-wellformed docs
		libxml_use_internal_errors(true);

		$parser = new ChefkochDOMParser();
		$result = $parser->analyzeUrl($request->query->get('url'));

		libxml_clear_errors();

		return new JsonResponse($result);
	}

	/**
	 * @param Request $request
	 * @param Recipe $recipe
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	private function formAction(Request $request, Recipe $recipe) {
		$formBuilder = $this->createFormBuilder($recipe)
			->add('label', TextType::class, ['label' => 'Titel', 'attr' => ['placeholder' => 'Supersaftige Rippchen']])
			->add('description', TextareaType::class)
			->add('originUrl', UrlType::class)
			->add('tagsString', TextType::class)
			->add('effort', TextType::class, ['label' => 'Aufwand', 'attr' => ['placeholder' => '20 Minuten']])
			->add('submit', SubmitType::class, ['label' => 'Rezept speichern']);

		$formBuilder->add('ingredients', CollectionType::class, array(
			'label' => 'Zutaten',
			'allow_add' => true,
			'allow_delete' => true,
			'entry_type' => IngredientType::class,
			'entry_options' => array(
				'label' => false,
				'required' => false,
				'attr' => ['placeholder' => '100 g Mehl, 2 EL Zucker, ...']
			),
		));

		$form = $formBuilder->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$tags = $recipe->getTagsString();

			$tagRepo = $this->getDoctrine()->getManager()->getRepository(Tag::class);
			$recipe->getTags()->clear();
			foreach (explode(',', $tags) as $tagName) {
				if (empty($tagName)) {
					continue;
				}

				$tag = $tagRepo->findOneBy(['label' => $tagName]);
				if (!$tag) {
					$tag = new Tag();
					$tag->setLabel($tagName);
					$this->getDoctrine()->getManager()->persist($tag);
				}

				$recipe->getTags()->add($tag);
			}

			$images = [];

			foreach ($_POST['images'] ?? [] as $imageUrl) {
				$image = new Image();
				$image->setRecipe($recipe);
				$image->setUrl($imageUrl);
				$this->getDoctrine()->getManager()->persist($image);
				$images[] = $image;
			}

			//TODO replace this hack?
			foreach ($recipe->getIngredients() as $ingredient) {
				$ingredient->setRecipe($recipe);
			}

			$this->getDoctrine()->getManager()->persist($recipe);
			$this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('showRecipe', ['id' => $recipe->getId()]);
		}

		$existingTags = array_map(function (Tag $tag) {
			return ['value' => $tag->getLabel(), 'text' => $tag->getLabel()];
		}, $this->getDoctrine()->getManager()->getRepository(Tag::class)->findAll());


		return $this->render('form.html.twig', array(
			'form' => $form->createView(),
			'existingTags' => $existingTags,
		));
	}

}