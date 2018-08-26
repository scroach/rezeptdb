<?php

namespace App\RestController;
use App\Entity\Recipe;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;


class RestRecipeController extends FOSRestController
{
    /**
     * @FOSRest\Get("/recipes")
     */
    public function getRecipes() {
        $recipes = $this->getDoctrine()->getRepository(Recipe::class)->fetchForIndex();
        return View::create($recipes, Response::HTTP_OK , []);
    }
}