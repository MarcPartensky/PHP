<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ArticleRepository;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Entity\Comment;
use App\Form\CommentType;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog')]
    public function index(ArticleRepository $repo): Response
    {
			$articles = $repo->findAll();
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
						'articles' => $articles
        ]);
    }

		#[Route("/", name: "home")]
		public function home() {
			return $this->render('blog/home.html.twig', [
				'title' => "Salut",
				'age' => 20
			]);
		}

		#[Route("/blog/new", name: "blog_create")]
		#[Route("/blog/{id}/edit", name: "blog_edit")]
		public function create(Article $article = null, Request $request, EntityManagerInterface $manager) {

			if (!$article) {
				$article = new Article();
			}

			$article->setTitle("Titre d'exemple")
							->setContent("Le contenu de l'article");

			$form = $this->createForm(ArticleType::class, $article);

			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				if (!$article->getId()) {
					$article->setCreatedAt(new \DateTime());
				}
				$manager->persist($article);
				$manager->flush();

				return $this->redirectToRoute('blog_show', [
					'id' => $article->getId()
				]);
			}

			return $this->render('blog/create.html.twig', [
				'formArticle' => $form->createView(),
				'editMode' => $article->getId() !== null
			]);
		}

		/* #[Route("/blog/new", name: "blog_create")] */
		/* public function create(Request $request, EntityManagerInterface $manager) { */
		/* 	/1* dump($request); *1/ */
		/* 	if ($request->request->count() > 0) { */
		/* 		$article = new Article(); */
		/* 		$article->setTitle($request->request->get('title')) */
		/* 			->setContent($request->request->get('content')) */
		/* 			->setImage($request->request->get('image')) */
		/* 			->setCreatedAt(new \DateTime()); */
		/* 	} */
		/* 	$manager->persist($article); */
		/* 	$manager->flush(); */
		/* 	return $this->redirectToRoute('blog_show', [ */
		/* 		'id' => $article->getId(), */
		/* 	]) */
		/* } */

		#[Route("/blog/{id}", name: "blog_show")]
		public function show(
			Article $article,
			Request $request,
			EntityManagerInterface $manager
		) {
			$comment = new Comment();
			$form = $this->createForm(CommentType::class, $comment);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				$comment->setCreatedAt(new \Datetime())
					->setArticle($article);
				$manager->persist($comment);
				$manager->flush();

				return $this->redirectToRoute('blog_show', [
					'id' => $article->getId()
				]);
			}

			return $this->render('blog/show.html.twig', [
				'article' => $article,
				'commentForm' => $form->createView()
			]);
		}
}
