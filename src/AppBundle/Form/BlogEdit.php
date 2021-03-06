<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BlogEdit extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
		->add('image', FileType::class, array('required' => false))
		->add('title', TextType::class)
		->add('text', TextareaType::class)
		->add('save', SubmitType::class, array('label' => 'Save'))
		->add('delete', SubmitType::class, array('label' => 'Delete'));
	}
}