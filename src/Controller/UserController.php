<?php


namespace App\Controller;


class UserController
{
	public function getUsersAction()
	{
//		$data = ...; // get data, in this case list of users.
//        $view = $this->view($data, 200)
//			->setTemplate("MyBundle:Users:getUsers.html.twig")
//			->setTemplateVar('users')
//		;

//        return $this->handleView($view);
    }

	public function redirectAction()
	{
		$view = $this->redirectView($this->generateUrl('some_route'), 301);
		// or
		$view = $this->routeRedirectView('some_route', array(), 301);

		return $this->handleView($view);
	}
}