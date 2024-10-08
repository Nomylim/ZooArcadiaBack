<?php

namespace App\Controller;

use App\Entity\Services;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Response, Request};
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/api/services', name: 'app_api_services_')]
class ServicesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ServicesRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {

    }
    #[Route(name: 'new', methods: 'POST')]
    #[OA\Post(
        path: "/api/services",
        summary: "Créer un service",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du service à créer",
            content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "nom", type: "string", example: "Nom du service"),
                        new OA\Property(property: "description", type: "string", example: "Description du service")
                    ]
                )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Service créé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        "id" => new OA\Property(property: "id", type: "integer", example: 1)
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Données invalides")
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        $services = $this->serializer->deserialize($request->getContent(), Services::class, 'json',['groups' => ['services_read']]);

        $this->manager->persist($services);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($services, 'json',['groups' => ['services_read']]);
        $location = $this->urlGenerator->generate(
            'app_api_services_show',
            ['id' => $services->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/services/{id}",
        summary: "Afficher un service par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du service à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Service trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Nom du service"),
                        new OA\Property(property: "description", type: "string", example: "Description du service")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Service non trouvé"
            )
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $services = $this->repository->findOneBy(['id' => $id]);

        if ($services) {
            $responseData = $this->serializer->serialize($services, 'json',['groups' => ['services_read']]);
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: "/api/services/{id}",
        summary: "Editer un service",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du service à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du service à modifier",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "Nom du service"),
                    new OA\Property(property: "description", type: "string", example: "Description du service")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Service modifié avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: "1"),
                        new OA\Property(property: "nom", type: "string", example: "Nom du service"),
                        new OA\Property(property: "description", type: "string", example: "Description du service")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Service non trouvé"
            )
        ]
    )]
    public function edit(int $id, Request $request): Response
    {
        $services = $this->repository->findOneBy(['id' => $id]);

        if ($services) {
            $services = $this->serializer->deserialize(
                $request->getContent(),
                Services::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $services]
            );
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/services/{id}",
        summary: "Supprimer un service par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du service à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Service suprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Service non trouvé"
            )
        ]
    )]
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

    #[Route('_all', name: 'list_all', methods: 'GET')]
    #[OA\Get(
        path: "/api/services_all",
        summary: "Liste tous les services",
        responses: [
            new OA\Response(
                response: 200,
                description: "La liste de tous les services",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "nom", type: "string", example: "Nom du service"),
                            new OA\Property(property: "description", type: "string", example: "Description du service")
                        ]
                    )
                )
            )
        ]
    )]
    public function listAll(ServicesRepository $repository, SerializerInterface $serializer): Response
    {
        $services = $repository->findAll();
        $serializedSesrvices = $serializer->serialize($services, 'json',['groups' => ['services_read']]);
        return new JsonResponse($serializedSesrvices, Response::HTTP_OK, [], true);
    }
}

