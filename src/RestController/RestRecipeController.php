<?php

namespace App\RestController;

use App\DTO\RecipeDTO;
use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\RecipeRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

class RestRecipeController extends AbstractFOSRestController
{
    public function __construct(private RecipeRepository $recipeRepository)
    {
    }

    #[FOSRest\Get('/recipes')]
    public function getRecipes(): View
    {
        /** @var User $user */
        $user = $this->getUser();
        $recipes = $this->recipeRepository->fetchForIndex($user);
        $recipeDTOs = [];
        foreach ($recipes as $recipe) {
            $recipeDTOs[] = RecipeDTO::createFromRecipe($recipe);
        }

        return View::create($recipeDTOs, Response::HTTP_OK, []);
    }
}
