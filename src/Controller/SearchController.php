<?php

namespace App\Controller;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(Request $rq, GameRepository $gr): Response
    {
        $word = $rq->query->get("search");
        $jeux = $gr->findBySearch($word);

        // EXO afficher les résultats dans le fichier search/index.html.twig, afficher aussi dans une balise h1 : Résultat de la recherche pour... et remplacer les ... par les mottapé dans la brarre de recherche
       
        // SELECT FROM game WHERE title LIKE '%test%'
        return $this->render('search/index.html.twig', [
            'jeux' => $jeux,
          
            'mot'=> $word
        ]);
    }
}
