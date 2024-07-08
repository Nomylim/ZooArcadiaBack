<?php

namespace App\Controller;

use App\Entity\Services;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/services', name: 'app_api_services_')]
class ServicesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager, 
        private ServicesRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {
        
    }
    #[Route(name: 'new', methods: 'POST')]
    public function new(Request $request): Response
    {
        //Crée un nouvel utilisateur à l'aide d'un formulaire
        $services = $this->serializer->deserialize($request->getContent(), Services::class, 'json');

        $this->manager->persist($services);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($services,'json');
        $location = $this->urlGenerator->generate(
            'app_api_services_show',
            ['id' => $services->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=>$location], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): Response
    {
        $services = $this->repository->findOneBy(['id' => $id]);

        if ($services) {
            $responseData = $this->serializer->serialize($services, 'json');
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null,Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): Response
    {
        $services = $this->repository->findOneBy(['id' => $id]);

        if ($services) {
            $services = $this->serializer->deserialize(
                $request->getContent(),
                Services::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE =>$services]
            );
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $services = $this->repository->findOneBy(['id' => $id]);
        if ($services) {
            $this->manager->remove($services);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
