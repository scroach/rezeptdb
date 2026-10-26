<?php

namespace App\RestController;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

class RestTestController extends AbstractFOSRestController
{
    #[FOSRest\Get('/test')]
    public function testAvailability(): View
    {
        return View::create('service available', Response::HTTP_OK, []);
    }

    #[FOSRest\Get('/login')]
    public function testLogin(): View
    {
        return View::create('login successful', Response::HTTP_OK, []);
    }
}
