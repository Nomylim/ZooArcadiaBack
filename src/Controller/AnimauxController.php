<?php

namespace App\Controller;

use App\Entity\Animaux;
use OpenApi\Attributes as OA;
use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/animaux', name: 'app_api_animaux_')]
class AnimauxController extends AbstractController
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
                    new OA\Property(property: "habitat_id", type: "integer", example: "L'ID de l'habitat")
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
                        new OA\Property(property: "habitat_id", type: "integer", example: "L'ID de l'habitat"),
                        new OA\Property(property: "prenom", type: "string", example: "Prenom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "Race de l'animal")
                    ]
                )
            )
        ]
    )]
    public function new(Request $request): Response
    {
        $animaux = $this->serializer->deserialize($request->getContent(), Animaux::class, 'json');

        $this->manager->persist($animaux);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($animaux,'json');
        $location = $this->urlGenerator->generate(
            'app_api_animaux_show',
            ['id' => $animaux->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=>$location], true);
    }

    #[Route('/{id}', name:'show', methods:'GET')]
    #[OA\Get(
        path: "/api/animaux/{id}",
        summary: "Afficher un animal par son ID",
        parameters:[
            new OA\Parameter(
                name: "id",
                in: "path",
                required: "true",
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
                        new OA\Property(property: "habitat_id", type: "integer", example: "L'ID de l'habitat"),
                        new OA\Property(property: "prenom", type: "string", example: "Prenom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "Race de l'animal")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description:"Animal non trouvé"
            )
        ]
    )]
    public function show(int $id): Response
    {
        $animaux = $this->repository->findOneBy(['id' => $id]);

        if ($animaux) {
            $responseData = $this->serializer->serialize($animaux, 'json');
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null,Response::HTTP_NOT_FOUND);
    }

    #[Route('{/id}', name:'edit', methods:'PUT')]
    #[OA\Put(
        path: "/api/animaux/{id}",
        summary: "Editer un animal",
        parameters:[
            new OA\Parameter(
                name: "id",
                in: "path",
                required: "true",
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
                    new OA\Property(property: "habitat_id", type: "integer", example: "L'ID de l'habitat")
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
                        new OA\Property(property: "habitat_id", type: "integer", example: "L'ID de l'habitat")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description:"Animal non trouvé"
            )
        ]
    )]
    public function edit(int $id, Request $request): Response
    {
        $animaux = $this->repository->findOneBy(['id' => $id]);

        if ($animaux) {
            $animaux = $this->serializer->deserialize(
                $request->getContent(),
                Animaux::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE =>$animaux]
            );
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('{/id}', name:'delete', methods:'DELETE')]
    #[OA\Delete(
        path: "/api/animaux/{id}",
        summary: "Supprimer un animal par son ID",
        parameters:[
            new OA\Parameter(
                name: "id",
                in: "path",
                required: "true",
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
                description:"Animal non trouvé"
            )
        ]
    )]
    public function delete(int $id): Response
    {
        $animaux = $this->repository->findOneBy(['id' => $id]);
        if ($animaux) {
            $this->manager->remove($animaux);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
