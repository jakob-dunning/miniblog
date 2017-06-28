<?php
// src/AppBundle/Controller/BlogController.php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Blog;
use AppBundle\Form\BlogEdit;

class BlogController extends Controller
{
	/**
	 * @Route("/", name="blog_show")
	 */
	public function listAction(EntityManagerInterface $em) {
		
		return new Response("Show all blog entries");
		
	}
	
	/**
	 * @Route("/create", name="entry_create")
	 */
	public function createAction(Request $request, EntityManagerInterface $em) {
		
		$blog = new Blog();
		
		/*$form = $this->createFormBuilder(new Blog())
			->add('image', FileType::class)
			->add('title', TextType::class)
			->add('text', TextareaType::class)
			->add('save', SubmitType::class, array('label' => 'Save'))
			->getForm();
		*/
		$form = $this->createForm(BlogEdit::class, new Blog());
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid()) {
			$blog = $form->getData();
			
			// get the image file and save the correct path
			$image = $blog->getImage();
			$imageName = md5(uniqid()).'.'.$image->guessExtension();
			$image->move( $this->getParameter('files_directory') . $this->getParameter('image_directory'),
					$imageName );
			$blog->setImage($imageName);
			
			$em->persist($blog);
			$em->flush();
		}

		return $this->render('create.html.twig', array('form' => $form->createView()));
		
	}
	
	/**
	 * @Route("/edit/{id}", name="entry_edit", requirements={"id":"\d+"})
	 */
	public function editAction(EntityManagerInterface $em, $id) {
		
		return new Response("Editing blog entry #" . $id);
		
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