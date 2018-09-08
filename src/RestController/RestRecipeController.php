<?php

namespace App\RestController;
use App\Entity\Recipe;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use App\DTO\RecipeDTO;


class RestRecipeController extends FOSRestController
{
    /**
     * @FOSRest\Get("/recipes")
     */
    public function getRecipes() {
        $recipes = $this->getDoctrine()->getRepository(Recipe::class)->fetchForIndex();
        $recipeDTOs = array();
        foreach ($recipes as $recipe) {
            array_push($recipeDTOs, new RecipeDTO($recipe));
        }
        //return View::create(sizeof($recipeDTOs), Response::HTTP_OK , []);
        return View::create($recipeDTOs, Response::HTTP_OK , []);
    }
}