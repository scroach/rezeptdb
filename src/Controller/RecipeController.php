<?php


namespace App\Controller;

use App\Entity\Image;
use App\Entity\Recipe;
use App\Entity\Tag;
use App\Service\ChefkochDOMParser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
		$task = new Recipe();
		$form = $this->createFormBuilder($task)
			->add('label', TextType::class, ['label' => 'Titel', 'attr' => ['placeholder' => 'Supersaftige Rippchen']])
			->add('description', TextareaType::class)
			->add('originUrl', UrlType::class)
			->add('ingredients', TextType::class)
			->add('tags', TextType::class)
			->add('effort', TextType::class, ['label' => 'Aufwand', 'attr' => ['placeholder' => '20 Minuten']])
			->add('submit', SubmitType::class, ['label' => 'Rezept speichern'])
			->getForm();


		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			// $form->getData() holds the submitted values
			// but, the original `$task` variable has also been updated
			$task = $form->getData();

			$tags = $task->getTags();
			$tagObjects = [];


			foreach (explode(',', $tags) as $tagName) {
				$tag = new Tag();
				$tag->setLabel($tagName);
				$this->getDoctrine()->getManager()->persist($tag);
				$tagObjects[] = $tag;
			}

			$images = [];
			foreach ($_POST['images'] as $imageUrl) {
				$image = new Image();
				$image->setRecipe($task);
				$image->setUrl($imageUrl);
				$this->getDoctrine()->getManager()->persist($image);
				$images[] = $image;
			}

			$task->setTags($tagObjects);

			$this->getDoctrine()->getManager()->persist($task);
			$this->getDoctrine()->getManager()->flush();

			return $this->redirectToRoute('recipeIndex');
		}

		return $this->render('form.html.twig', array(
			'form' => $form->createView(),
		));
	}

	/**
	 * @Route("/recipes/show/{id}", name="showRecipe")
	 */
	public function showAction($id) {
//		$product = $this->getDoctrine()
//			->getRepository(Product::class)
//			->find($id);
//
//		if (!$product) {
//			throw $this->createNotFoundException(
//				'No product found for id '.$id
//			);
//		}
//
//		return new Response('Check out this great product: '.$product->getName());

		// or render a template
		// in the template, print things with {{ product.name }}
		// return $this->render('product/show.html.twig', ['product' => $product]);
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

}