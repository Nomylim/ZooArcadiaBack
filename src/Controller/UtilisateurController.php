<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/utilisateur', name: 'app_api_utilisateur_')]
class UtilisateurController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private UtilisateurRepository $repository)
    {
        
    }
    #[Route('/{id}', name: 'new', methods: 'POST')]
    public function new(): Response
    {
        //Crée un nouvel utilisateur à l'aide d'un formulaire
        $utilisateur = new Utilisateur();
        $utilisateur->setMail('mail@mail.com');
        $utilisateur->setPassword('123');
        $utilisateur->setRole('admin');
        
        $this->manager->persist($utilisateur);
        $this->manager->flush();

        return $this->json(
            ['message' =>"Utilisateur créer avec {$utilisateur->getId()} id"],
            Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): Response
    {
        $utilisateur = $this->repository->findOneBy(['id' => $id]);

        if (!$utilisateur) {
            throw $this->createNotFoundException("No Users found for {$id} id");
        }

        return $this->json(
            ['message' => "A user was found : {$utilisateur->getMail()} for {$utilisateur->getId()} id"]
        );
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id): Response
    {
        $utilisateur = $this->repository->findOneBy(['id' => $id]);

        if (!$utilisateur) {
            throw $this->createNotFoundException("No user found for {$id} id");
        }
        //Utiliser l'information d'un formulaire
        $utilisateur->setMail('User mail updated');
        $this->manager->flush();

        return $this->redirectToRoute('app_api_utilisateur_show', ['id' => $utilisateur->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $utilisateur = $this->repository->findOneBy(['id' => $id]);
        if (!$utilisateur) {
            throw $this->createNotFoundException("No user found for {$id} id");
        }

        $this->manager->remove($utilisateur);
        $this->manager->flush();

        return $this->json(['message' => "User resource deleted"], Response::HTTP_NO_CONTENT);
    }
}
