<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Player;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface as Hasher;

#[Route('/admin/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository, Hasher $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(!in_array("ROLE_ADMIN", $user->getRoles()) ){
                $newPlayer = new Player;
                $newPlayer->setNickname($user->getPseudo());
                $newPlayer->setEmail($form->get("email")->getData());
                // $newPlayer->setEmail($request->request->get("email")); // on peut récupérer la valeur du champs avec l'objet Request
                $user->setPlayer($newPlayer);
            }else{
                $user->setRoles(["ROLE_ADMIN"]);

            }

            $mdp = $form->get('password')->getData();
            $password = $hasher->hashPassword($user, $mdp);
            $user->setPassword($password);
            $userRepository->add($user);
            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', requirements: ['id' => '[0-9]+'], name: 'app_admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, Hasher $hasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($mdp = $form->get('password')->getData()){
                $password = $hasher->hashPassword($user, $mdp);
                $user->setPassword($password);
            }
            $userRepository->add($user);
            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user);
        }

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }





// rajouter une route : name='app_admin_user_update'
// Récupérer tous les users. Vérifier pour chaque user s'il y a un player relié ou pas.
// S'il n'y a pas de player relié, créez un nouveau player (son nickname sera égal au pseudo de l'user, l'email sera pseudo@yopmail.com), reliez ce player au user, et enregistrez les modifications en bdd
//Faites une redirection vers la page liste des users avec un message succès : Mise à jour réussie


    #[Route('/update', name: 'app_admin_user_update', methods: ['GET', 'POST'])]

    public function update(UserRepository $ur, EntityManagerInterface $em)
    {
        $listeuser = $ur->findAll();
        $compteur=0;
        foreach ($listeuser as $utilisateur){
            if(!in_array("ROLE_ADMIN", $utilisateur->getRoles()) && !$utilisateur->getPlayer() ){ //Si l'util n'est pas admin et Si l'util n'a pas de player          
                $nouveauJoueur = new Player;
                $nouveauJoueur ->setNickname($utilisateur->getPseudo())
                               ->setEmail($utilisateur->getPseudo() . '@yopmail.com');
                $utilisateur->setPlayer($nouveauJoueur);
                $compteur++;
            }           
        }
        $em->flush();
        $this->addFlash('sucess', "Mise à jours des utilisateurs réussie! $compteur joueurs créés");
        return $this->redirectToRoute("app_admin_player_index");
    }
}
