<?php

namespace App\Controller;

use App\Entity\Services;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/services', name: 'app_api_services_')]
class ServicesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private ServicesRepository $repository)
    {
        
    }
    #[Route('/{id}', name: 'new', methods: 'POST')]
    public function new(): Response
    {
        //Crée un nouvel utilisateur à l'aide d'un formulaire
        $services = new Services();
        $services ->setNom('Petit train');
        $services ->setDescription("Faite le tour de notre parc en famille à l'aide de notre petit train. Vous découvrirez tous les habitats et leurs histoires.");
        
        $this->manager->persist($services);
        $this->manager->flush();

        return $this->json(
            ['message' =>"Service créer avec {$services->getId()} id"],
            Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): Response
    {
        $services = $this->repository->findOneBy(['id' => $id]);

        if (!$services) {
            throw $this->createNotFoundException("No Services found for {$id} id");
        }

        return $this->json(
            ['message' => "A service was found : {$services->getNom()} for {$services->getId()} id"]
        );
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id): Response
    {
        $services = $this->repository->findOneBy(['id' => $id]);

        if (!$services) {
            throw $this->createNotFoundException("No service found for {$id} id");
        }
        //Utiliser l'information d'un formulaire
        $services->setNom('Service name updated');
        $this->manager->flush();

        return $this->redirectToRoute('app_api_services_show', ['id' => $services->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $services = $this->repository->findOneBy(['id' => $id]);
        if (!$services) {
            throw $this->createNotFoundException("No service found for {$id} id");
        }

        $this->manager->remove($services);
        $this->manager->flush();

        return $this->json(['message' => "Service resource deleted"], Response::HTTP_NO_CONTENT);
    }
}
