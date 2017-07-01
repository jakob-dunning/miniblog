<?php
// TODO
// add paginator to facilitate next, previous entry
// add constraints to form fields?
// add Date / Map field to posts?
// add safeguard to deletion process
// don't validate deletions

// src/AppBundle/Controller/BlogController.php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Blog;
use AppBundle\Form\BlogEdit;
use AppBundle\Form\BlogCreate;
use AppBundle\AppBundle;
use Symfony\Component\Form\Extension\Core\Type\FileType;

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
	 * @Route("/create", name="post_create")
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
			return $this->redirectToRoute('post_show', array('id' => $blog->getId()));
		}

		return $this->render('create.html.twig', array('form' => $form->createView()));
		
	}
	
	/**
	 * @Route("/edit/{id}", name="post_edit", requirements={"id":"\d+"})
	 */
	public function editAction(Request $request, EntityManagerInterface $em, $id) {
		
		// do authorization check
		
		// grab blog entry from DB
		$blog = $em->getRepository('AppBundle:Blog')->find($id);
		
		// 404 if id doesn't match anything in the database
		if(!$blog) {
			throw $this->createNotFoundException('No blog entry found for id ' . $id . '.');
		}
		
		// recreate the file handler to satisfy the requierements
		$blog->setImage( new File($blog->getImage(), false) );
		
		// keep the old image
		$image_tmp = $blog->getImage();
		
		// create form and populate it
		$form = $this->createForm(BlogEdit::class, $blog);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid()) {
			
			//check which button was clicked
			if($form->get('delete')->isClicked()) {
				
				// delete post
				$em->remove($blog);
				$em->flush();
				
				return $this->redirectToRoute('blog_show');
			}
			
			$blog = $form->getData();
			
			// only perform if we have a new image
			if($blog->getImage()) {
				// get the image file and save the correct path
				$image = $blog->getImage();
				$imageName = md5(uniqid()).'.'.$image->guessExtension();
				$image->move( $this->getParameter('files_directory') . $this->getParameter('image_directory'),
						$imageName );
				$blog->setImage($this->getParameter('image_directory') . '/' . $imageName);
			} else {
				$blog->setImage($image_tmp);
			}
			
			$em->persist($blog);
			$em->flush();
			
			// get the ID of our newly created blog entry and go to show mode
			return $this->redirectToRoute('post_edit', array('id' => $blog->getId()));
		}
		
		return $this->render('edit.html.twig', array('form' => $form->createView(), 'post' => $blog));
	}
	
	/**
	 * @Route("/show/{id}", name="post_show", requirements={"id":"\d+"})
	 */
	public function showAction(EntityManagerInterface $em, $id) {
		
		$post = $em->getRepository('AppBundle\Entity\Blog')
			->find($id);
		
		
		if (!$post) {
			throw $this->createNotFoundException(
					'No entry found for id '.$id
					);
		}
		return $this->render('show.html.twig', array('post' => $post));
		
	}
}