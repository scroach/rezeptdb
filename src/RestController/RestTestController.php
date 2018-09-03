<?php

namespace App\RestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;


class RestTestController extends FOSRestController
{
    /**
     * @FOSRest\Get("/test")
     */
    public function testAvailability() {
        return View::create("service available", Response::HTTP_OK , []);
    }

    /**
     * @FOSRest\Get("/login")
     */
    public function testLogin() {
        return View::create("login successful", Response::HTTP_OK , []);
    }
}