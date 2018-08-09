<?php
/**
 * Created by PhpStorm.
 * User: darks_000
 * Date: 09.08.2018
 * Time: 21:05
 */

namespace App\RestController;
use App\Entity\Recipe;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;


class RestRecipeController extends FOSRestController
{
    /**
     * Create Article.
     * @FOSRest\Get("/recipesTest")
     */
    public function getRecipes() {
        $recipes = $this->getDoctrine()->getRepository(Recipe::class)->fetchForIndex();
        return View::create($recipes, Response::HTTP_OK , []);
    }
}