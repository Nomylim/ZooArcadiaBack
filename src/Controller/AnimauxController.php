<?php

namespace App\Controller;

use App\Entity\Animaux;
use App\Entity\Habitats;
use OpenApi\Attributes as OA;
use App\Repository\{AnimauxRepository, HabitatsRepository};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

#[Route('/api/animaux', name: 'app_api_animaux_')]
class AnimauxController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private AnimauxRepository $animauxrepository,
        private HabitatsRepository $habitatsrepository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
    ) {
    }
    #[Route(name: 'new', methods: 'POST')]
    #[OA\Post(
        path: "/api/animaux",
        summary: "Créer un animal",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'animal à créer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "prenom", type: "string", example: "Prenom de l'animal"),
                    new OA\Property(property: "race", type: "string", example: "Race de l'animal"),
                    new OA\Property(property: "habitat_id", type: "integer", example: "34")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Animal créé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: "1"),
                        new OA\Property(property: "habitat_id", type: "integer", example: "34"),
                        new OA\Property(property: "prenom", type: "string", example: "Prenom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "Race de l'animal")
                    ]
                )
            )
        ]
    )]
    public function new(Request $request): Response
    {
        $statusCode = Response::HTTP_CREATED;
        $responseData = '';
        $headers = [];

        try {
            $data = json_decode($request->getContent(), true);

            if (!$data || !isset($data['prenom'], $data['race'], $data['habitat_id'])) {
                $statusCode = Response::HTTP_BAD_REQUEST;
                $responseData = json_encode(['message' => 'Données invalides']);
            } else {
                $habitat = $this->habitatsrepository->find($data['habitat_id']);
                if (!$habitat) {
                    $statusCode = Response::HTTP_NOT_FOUND;
                    $responseData = json_encode(['message' => 'Habitat non trouvé']);
                } else {
                    $animal = new Animaux();
                    $animal->setPrenom($data['prenom']);
                    $animal->setRace($data['race']);
                    $animal->setHabitat($habitat);

                    $this->manager->persist($animal);
                    $this->manager->flush();

                    $responseData = $this->serializer->serialize($animal, 'json', ['groups' => 'animal_read']);
                    $headers["Location"] = $this->urlGenerator->generate(
                        'app_api_animaux_show',
                        ['id' => $animal->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }
            }
        } catch (\Exception $e) {
            // Log detailed error
            $this->logger->error('Erreur lors de la création de l\'animal: ' . $e->getMessage(), ['exception' => $e]);
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $responseData = json_encode(['message' => 'Erreur interne du serveur', 'details' => $e->getMessage()]);
        }

        return new JsonResponse($responseData, $statusCode, $headers, true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/animaux/{id}",
        summary: "Afficher un animal par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'animal à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Animal trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: "1"),
                        new OA\Property(property: "habitat_id", type: "integer", example: "34"),
                        new OA\Property(property: "prenom", type: "string", example: "Prenom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "Race de l'animal")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Animal non trouvé"
            )
        ]
    )]
    public function show(int $id): Response
    {
        $animaux = $this->animauxrepository->findOneBy(['id' => $id]);

        if ($animaux) {
            $responseData = $this->serializer->serialize($animaux, 'json', ['groups' => 'animal_read']);
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: "/api/animaux/{id}",
        summary: "Editer un animal",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'animal à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'animal à modifier",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "prenom", type: "string", example: "Prenom de l'animal"),
                    new OA\Property(property: "race", type: "string", example: "Race de l'animal"),
                    new OA\Property(property: "habitat_id", type: "integer", example: "34")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Animal modifié avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: "1"),
                        new OA\Property(property: "prenom", type: "string", example: "Prenom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "Race de l'animal"),
                        new OA\Property(property: "habitat_id", type: "integer", example: "34")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Animal non trouvé"
            )
        ]
    )]
    public function edit(int $id, Request $request): Response
    {
        $animaux = $this->animauxrepository->findOneBy(['id' => $id]);

        if ($animaux) {
            $data = json_decode($request->getContent(), true);


            $habitat = $this->habitatsrepository->find($data['habitat_id']);
            if (!$habitat) {
                return new JsonResponse(['message' => 'Habitat non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $animaux->setPrenom($data['prenom']);
            $animaux->setRace($data['race']);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/animaux/{id}",
        summary: "Supprimer un animal par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'animal à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Animal suprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Animal non trouvé"
            )
        ]
    )]
    public function delete(int $id): Response
    {
        $animaux = $this->animauxrepository->findOneBy(['id' => $id]);
        if ($animaux) {
            $this->manager->remove($animaux);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('_all', name: 'list_all', methods: 'GET')]
    #[OA\Get(
        path: "/api/animaux_all",
        summary: "Liste tous les animaux",
        responses: [
            new OA\Response(
                response: 200,
                description: "La liste de tous les animaux",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: "1"),
                            new OA\Property(property: "prenom", type: "string", example: "Prenom de l'animal"),
                            new OA\Property(property: "race", type: "string", example: "Race de l'animal"),
                            new OA\Property(property: "habitat_id", type: "integer", example: "34")
                        ]
                    )
                )
            )
        ]
    )]
    public function listAll(AnimauxRepository $repository, SerializerInterface $serializer): Response
    {
        try{
            $animaux = $repository->findAll();
        $serializedAnimaux = $serializer->serialize($animaux, 'json',  ['groups' => 'animal_read']);
        return new JsonResponse($serializedAnimaux, Response::HTTP_OK, [], true);
        }
        catch(\Exception $e){
            $this->logger->error('Erreur lors de la récupération des animaux: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['message' => 'Erreur interne du serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
