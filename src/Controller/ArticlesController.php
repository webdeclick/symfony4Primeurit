<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Commentaires;
use App\Form\CommentaireFormType;
use App\Form\AjoutArticleFormType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

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
     * @IsGranted("ROLE_USER")
     * @Route("/article/nouveau",name="ajout_article")
     */
    public function ajoutArticle(HttpFoundationRequest $request){
        $article = new Articles();
        $form = $this->createForm(AjoutArticleFormType::class, $article);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $article->setUsers($this->getUser());
            $doctrine = $this->getDoctrine()->getManager();
            $doctrine->persist($article);
            $doctrine->flush();

            $this->addFlash('message', 'Votre articles a bien été publié.');
            
            return $this->redirectToRoute('articles');
        }
        return $this->render('articles/ajout.html.twig',[
            'articleForm' => $form->createView()
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

     /**
     * @Route("/article/modifier/{id}",name="modif_article")
     */
    public function modifArticle(){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    }
}
