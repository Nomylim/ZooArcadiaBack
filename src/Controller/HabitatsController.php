<?php

namespace App\Controller;

use App\Entity\Habitats;
use OpenApi\Attributes as OA;
use App\Repository\HabitatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/habitats', name: 'app_api_habitats_')]
class HabitatsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private HabitatsRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
    ) {
    }
    #[Route(name: 'new', methods: 'POST')]
    #[OA\Post(
        path: "/api/habitats",
        summary: "Créer un habitat",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'habitat à créer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Nom de l'habitat"),
                    new OA\Property(property: "description", type: "string", example: "Description de l'habitat")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Habitat créé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: "1"),
                        new OA\Property(property: "name", type: "string", example: "Nom de l'habitat"),
                        new OA\Property(property: "description", type: "string", example: "Description de l'habitat")
                    ]
                )
            )
        ]
    )]
    public function new(Request $request): Response
    {
        $habitat = $this->serializer->deserialize($request->getContent(), Habitats::class, 'json');

        $this->manager->persist($habitat);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($habitat, 'json');
        $location = $this->urlGenerator->generate(
            'app_api_habitats_show',
            ['id' => $habitat->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/habitats/{id}",
        summary: "Afficher un habitat par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'habitat à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Habitat trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: "1"),
                        new OA\Property(property: "name", type: "string", example: "Nom de l'habitat"),
                        new OA\Property(property: "description", type: "string", example: "Description de l'habitat")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Habitat non trouvé"
            )
        ]
    )]
    public function show(int $id): Response
    {
        $habitat = $this->repository->findOneBy(['id' => $id]);

        if ($habitat) {
            $responseData = $this->serializer->serialize($habitat, 'json');
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Put(
        path: "/api/habitats/{id}",
        summary: "Editer un habitat",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'habitat à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'habitat à modifier",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Nom de l'habitat"),
                    new OA\Property(property: "description", type: "string", example: "Description de l'habitat")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Habitat modifié avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: "1"),
                        new OA\Property(property: "name", type: "string", example: "Nom de l'habitat"),
                        new OA\Property(property: "description", type: "string", example: "Description de l'habitat")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Habitat non trouvé"
            )
        ]
    )]
    public function edit(int $id, Request $request): Response
    {
        $habitat = $this->repository->findOneBy(['id' => $id]);

        if ($habitat) {
            $habitat = $this->serializer->deserialize(
                $request->getContent(),
                Habitats::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $habitat]
            );
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/habitats/{id}",
        summary: "Supprimer un habitats par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'habitat à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Habitat suprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Habitat non trouvé"
            )
        ]
    )]
    public function delete(int $id): Response
    {
        $habitat = $this->repository->findOneBy(['id' => $id]);
        if ($habitat) {
            $this->manager->remove($habitat);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
    #[Route('_all', name: 'list_all', methods: 'GET')]
    #[OA\Get(
        path: "/api/habitats_all",
        summary: "Liste tous les habitats",
        responses: [
            new OA\Response(
                response: 200,
                description: "La liste de tous les habitats",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: "1"),
                            new OA\Property(property: "name", type: "string", example: "Nom de l'habitat"),
                            new OA\Property(property: "description", type: "string", example: "Description de l'habitat")
                        ]
                    )
                )
            )
        ]
    )]
    public function listAll(HabitatsRepository $repository, SerializerInterface $serializer): Response
    {
        try {
            $habitats = $repository->findAll();
            $serializedHabitats = $serializer->serialize($habitats, 'json', ['groups' => 'habitats_read']);
            return new JsonResponse($serializedHabitats, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des habitats: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['message' => 'Erreur interne du serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/habitats_animaux/{id}', name: 'app_api_habitats_animaux_', methods: ['GET'])]
    #[OA\Get(
        path: "/habitats_animaux/{id}",
        summary: "Liste des animaux dans un habitat",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'habitat",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des animaux trouvés dans l'habitat",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: "1"),
                            new OA\Property(property: "prenom", type: "string", example: "Nom de l'animal"),
                            new OA\Property(property: "race", type: "string", example: "Race de l'animal")
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 404,
                description: "Habitat non trouvé"
            )
        ]
    )]
    public function getAnimaux(int $id, HabitatsRepository $repository, SerializerInterface $serializer): Response
    {
        $habitat = $repository->find($id);

        if (!$habitat) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $animaux = $habitat->getAnimaux();
        $serializedAnimaux = $serializer->serialize($animaux, 'json', ['groups' => 'animal_read']);

        return new JsonResponse($serializedAnimaux, Response::HTTP_OK, [], true);
    }
}
