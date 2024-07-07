<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/utilisateur', name: 'app_api_utilisateur_')]
class UtilisateurController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private UtilisateurRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {
        
    }
    #[Route( name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        //Crée un nouvel utilisateur à l'aide d'un formulaire
        $utilisateur = $this->serializer->deserialize($request->getContent(), Utilisateur::class, 'json');
        
        $this->manager->persist($utilisateur);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($utilisateur,'json');
        $location = $this->urlGenerator->generate(
            'app_api_utilisateur_show',
            ['id' => $utilisateur->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=>$location], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): Response
    {
        $utilisateur = $this->repository->findOneBy(['id' => $id]);

        if ($utilisateur) {
            $responseData = $this->serializer->serialize($utilisateur, 'json');
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null,Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): JsonResponse
    {
        $utilisateur = $this->repository->findOneBy(['id' => $id]);

        if ($utilisateur) {
            $utilisateur = $this->serializer->deserialize(
                $request->getContent(),
                Utilisateur::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE =>$utilisateur]
            );
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        //Utiliser l'information d'un formulaire
        
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $utilisateur = $this->repository->findOneBy(['id' => $id]);
        if ($utilisateur) {
            $this->manager->remove($utilisateur);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
