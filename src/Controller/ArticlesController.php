<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Commentaires;
use App\Form\CommentaireFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Routing\Annotation\Route;

class ArticlesController extends AbstractController
{
    /**
     * @Route("/articles", name="articles")
     */
    public function index()
    {
        $articles = $this->getDoctrine()->getRepository(Articles::class)->findAll();
        return $this->render('articles/index.html.twig', [
            'controller_name' => 'ArticlesController',
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/article/{slug}",name="article")
     */
    public function article($slug, HttpFoundationRequest $request){
        $article = $this->getDoctrine()->getRepository(Articles::class)->findOneBy([
            'slug'=>$slug
        ]);

        if(!$article){
            throw $this->createNotFoundException("L'article recherché n'existe pas");
        }

        $commentaire = new Commentaires();

        $form = $this->createForm(CommentaireFormType::class, $commentaire);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $commentaire->setArticles($article);
            $commentaire->setCreatedAt(new \DateTime('now'));
            $doctrine = $this->getDoctrine()->getManager();
            $doctrine->persist($commentaire);
            $doctrine->flush();
        }
        
        return $this->render('articles/article.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView()
        ]);

    }
}
