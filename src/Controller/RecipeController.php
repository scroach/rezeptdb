<?php


namespace App\Controller;


use App\Entity\Recipe;
use App\Service\ChefkochDOMParser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
	public function addAction() {
		$number = mt_rand(0, 100);


		return $this->render('form.html.twig', array(
			'number' => $number,
		));
	}

	/**
	 * @Route("/recipes/{id}", name="showRecipe")
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

		require __DIR__.'/../Service/AbstractDOMParser.php';
		// use internal errors so libxml won't throw php warnings/errors on non-wellformed docs
		libxml_use_internal_errors(true);

		$parser = new ChefkochDOMParser();
		$result = $parser->analyzeUrl($request->query->get('url'));

		libxml_clear_errors();
		return new JsonResponse($result);
	}

}