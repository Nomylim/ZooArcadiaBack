<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Repository\AvisRepository;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/avis', name:'app_api_avis_')]

class AvisController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private AvisRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    )
    {
    }

    #[Route(name:'new', methods:'POST')]
    #[OA\Post(
        path: "/api/avis",
        summary: "Soumission d'un avis",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'avis à soumettre",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "pseudo", type: "string", example:"Mon pseudo"),
                    new OA\Property(property: "avis", type: "string", example: "Ceci est un avis")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Avis soumis avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "status", type: "string", example:"Succes"),
                        new OA\Property(property: "message", type: "string", example: "Avis en attente de validation")
                    ]
                )
            ),
            new OA\Response(response: 400, description:"Erreur de validation")
        ]
    )]
    public function new(Request $request): Response
    {
        $avis = $this->serializer->deserialize($request->getContent(), Avis::class, 'json', ['groups' => ['avis:write']]);

        $this->manager->persist($avis);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($avis,'json', ['groups' => ['avis:read']]);
        /*$location = $this->urlGenerator->generate(
            'app_api_avis_show',
            ['id' => $avis->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );*/

        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }
    /*
    #[Route('/{id}', name:'show', methods:['GET'])]
    #[OA\Get(
        path: "/api/avis/{id}",
        summary: "Afficher un avis par son ID",
        parameters:[
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'avis à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Avis trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "pseudo", type: "string", example:"Mon pseudo"),
                        new OA\Property(property: "avis", type: "string", example: "Ceci est un avis")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description:"Avis non trouvé"
            )
        ]
    )]
    public function show(string $id): Response
    {
        if (!ctype_digit($id)) {
            return new JsonResponse(['error' => 'L\'ID doit être un entier.'], Response::HTTP_BAD_REQUEST);
        }
        $id = (int)$id;

        $avis = $this->repository->findOneBy(['id' => $id]);

        if ($avis) {
            $responseData = $this->serializer->serialize($avis, 'json', ['groups' => ['avis:read']]);
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null,Response::HTTP_NOT_FOUND);
    }*/

    #[Route('/getAllValide', name:'get_avis_valide', methods:['GET'])]
    #[OA\Get(
        path: "/api/avis/getAllValide",
        summary: "Obtenir les avis approuvés",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des avis approuvés",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "pseudo", type: "string", example: "JohnDoe"),
                            new OA\Property(property: "avis", type: "string", example: "Ceci est un avis.")
                        ]
                    )
                )
            )
        ]
    )]
    public function getallValide(): JsonResponse
    {
        $avis = $this->repository->findBy(['valide' => true]);
        $response =[];
        foreach($avis as $a){
            $response[] = [
                'pseudo' => $a->getPseudo(),
                'avis' => $a->getAvis()
            ];
        }
        return new JsonResponse(['status' => 'success', 'avis' => $response], Response::HTTP_OK);
    }

    #[Route('/getAttenteValide', name:'get_avis_attente_valide', methods:['GET'])]
    #[OA\Get(
        path: "/api/avis/getAttenteValide",
        summary: "Obtenir les avis en attente de validation",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des avis en attente de validation",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "pseudo", type: "string", example: "JohnDoe"),
                            new OA\Property(property: "avis", type: "string", example: "Ceci est un avis.")
                        ]
                    )
                )
            )
        ]
    )]
    public function getAttenteValide(): JsonResponse
    {
        $avis = $this->repository->findBy(['valide' => false]);
        $response =[];
        foreach($avis as $a){
            $response[] = [
                'pseudo' => $a->getPseudo(),
                'avis' => $a->getAvis()
            ];
        }
        return new JsonResponse(['status' => 'success', 'avis' => $response], Response::HTTP_OK);
    }

    #[Route('/{id}', name:'valide', methods:'POST')]
    #[OA\Post(
        path: "/api/avis/{id}",
        summary: "Valider un avis",
        parameters:[
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'avis à valider",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Avis validé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "pseudo", type: "string", example:"Mon pseudo"),
                        new OA\Property(property: "avis", type: "string", example: "Ceci est un avis")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description:"Avis non trouvé"
            )
        ]
    )]
    public function valideAvis(int $id): JsonResponse
    {
        $avis = $this->repository->findOneBy(['id' => $id]);

        if($avis) {
            $avis->setValide(true);
            $this->manager->flush();

            return new JsonResponse(['status' => 'success', 'message' => "L'avis a été approuvé."], Response::HTTP_OK);
        }
        return new JsonResponse(['status' => 'error', 'message' => "Avis non trouvé."], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name:'edit', methods:['PUT'])]
    #[OA\Put(
        path: "/api/avis/{id}",
        summary: "Editer un avis",
        parameters:[
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'avis à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'avis à modifier",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "pseudo", type: "string", example:"Mon pseudo"),
                    new OA\Property(property: "avis", type: "string", example: "Ceci est un avis")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Avis modifié avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "pseudo", type: "string", example:"Mon pseudo"),
                    new OA\Property(property: "avis", type: "string", example: "Ceci est un avis")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description:"Avis non trouvé"
            )
        ]
    )]
    public function edit(int $id, Request $request): Response
    {
        $avis = $this->repository->findOneBy(['id' => $id]);

        if ($avis) {
            $avis = $this->serializer->deserialize(
                $request->getContent(),
                Avis::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $avis]
            );
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name:'delete', methods:['DELETE'])]
    #[OA\Delete(
        path: "/api/avis/{id}",
        summary: "Supprimer un avis par son ID",
        parameters:[
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'avis à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Avis suprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description:"Avis non trouvé"
            )
        ]
    )]
    public function delete(int $id): Response
    {
        $avis = $this->repository->findOneBy(['id' => $id]);
        if ($avis) {
            $this->manager->remove($avis);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
