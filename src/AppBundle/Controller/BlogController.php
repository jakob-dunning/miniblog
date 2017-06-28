<?php
// src/AppBundle/Controller/BlogController.php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Blog;
use AppBundle\Form\BlogEdit;
use AppBundle\Form\BlogCreate;
use AppBundle\AppBundle;

class BlogController extends Controller
{
	/**
	 * @Route("/", name="blog_show")
	 */
	public function listAction(EntityManagerInterface $em) {
		
		// get all blog posts
		$posts = $em->getRepository('AppBundle:Blog')->findAll();
		
		return $this->render('blog.html.twig', array('posts' => $posts));
	}
	
	/**
	 * @Route("/create", name="entry_create")
	 */
	public function createAction(Request $request, EntityManagerInterface $em) {
		
		$blog = new Blog();

		$form = $this->createForm(BlogCreate::class, $blog);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid()) {
			$blog = $form->getData();
			
			// get the image file and save the correct path
			$image = $blog->getImage();
			$imageName = md5(uniqid()).'.'.$image->guessExtension();
			$image->move( $this->getParameter('files_directory') . $this->getParameter('image_directory'),
					$imageName );
			$blog->setImage($this->getParameter('image_directory') . '/' . $imageName);
			
			$em->persist($blog);
			$em->flush();
			
			// get the ID of our newly created blog entry and go to show mode
			return $this->redirectToRoute('entry_show', array('id' => $blog->getId()));
		}

		return $this->render('form.html.twig', array('form' => $form->createView()));
		
	}
	
	/**
	 * @Route("/edit/{id}", name="entry_edit", requirements={"id":"\d+"})
	 */
	public function editAction(EntityManagerInterface $em, $id) {
		
		// grab blog entry from DB
		$blog = $em->getRepository('AppBundle:Blog')
			->find($id);
		
		// 404 if id doesn't match anything in the database
		if(!$blog) {
			throw $this->createNotFoundException('No blog entry found for id ' . $id . '.');
		}
		
		// create form and populate it
		$form = $this->createForm(BlogEdit::class, $blog);
		
		return $this->render('form.html.twig', array('form' => $form->createView()));
	}
	
	/**
	 * @Route("/show/{id}", name="entry_show", requirements={"id":"\d+"})
	 */
	public function showAction(EntityManagerInterface $em, $id) {
		
		$blogEntry = $em->getRepository('AppBundle\Entity\Blog')
			->find($id);
		
		if (!$blogEntry) {
			throw $this->createNotFoundException(
					'No entry found for id '.$id
					);
		}
		return $this->render('show.html.twig', array('entry' => $blogEntry));
		
	}
}