<?php
// src/AppBundle/Controller/BlogController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class BlogController
{
	/**
	 * @Route("/")
	 */
	public function blogList()
	{
		$number = mt_rand(0, 100);
		
		return new Response(
				'<html><body>Lucky number: '.$number.'</body></html>'
				);
	}
}