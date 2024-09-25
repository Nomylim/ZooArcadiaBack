<?php

namespace App\Controller;

use App\Entity\Nourriture;
use App\Repository\AnimauxRepository;
use OpenApi\Annotations\Property;
use OpenApi\Attributes as OA;
use App\Repository\NourritureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;
use DateTime;

#[Route('/api/nourriture', name: 'app_api_nourriture_')]
class NourritureController extends AbstractController
{
    public function __construct(
        private NourritureRepository $nourritureRepository,
        private AnimauxRepository $animauxRepository,
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger
    ) {
    }

    #[Route(name: 'new', methods: 'POST')]
    #[OA\Post(
        path: "/api/nourriture",
        summary: "Créer consommation de nourriture",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de la consommation de nourriture à créer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "Du foin"),
                    new OA\Property(property: "grammage", type: "integer", example: 500),
                    new OA\Property(property: "date", type: "string", format: "date", example: "2023-07-10"),
                    new OA\Property(property: "heure", type: "string", format: "time", example: "14:30"),
                    new OA\Property(property: "animal_id", type: "object", properties:[
                        new OA\Property(property: "id", type:"integer", example: 23)
                    ]),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Consommation de nourriture créée avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Du foin"),
                        new OA\Property(property: "grammage", type: "integer", example: 500),
                        new OA\Property(property: "date", type: "string", format: "date", example: "2023-07-10"),
                        new OA\Property(property: "heure", type: "string", format: "time", example: "14:30"),
                        new OA\Property(property: "animal_id", type: "integer", example: 23),
                        new OA\Property(property: "animal_prenom", type: "string", example: "Fido"),
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

            if (!$data || !isset($data['nom'], $data['grammage'], $data['date'], $data['heure'], $data['animal'])) {
                $statusCode = Response::HTTP_BAD_REQUEST;
                $responseData = json_encode(['message' => 'Données invalides']);
            } else {
                $animal = $this->animauxRepository->find($data['animal']);
                if (!$animal) {
                    $statusCode = Response::HTTP_NOT_FOUND;
                    $responseData = json_encode(['message' => 'Animal non trouvé']);
                } else {
                    $date = DateTime::createFromFormat('Y-m-d', $data['date']);
                    $heure = DateTime::createFromFormat('H:i', $data['heure']);

                    if ($date === false || $heure === false) {
                        $statusCode = Response::HTTP_BAD_REQUEST;
                        $responseData = json_encode(['message' => 'Format de date ou d\'heure invalide']);
                    } else {
                        $nourriture = new Nourriture();
                        $nourriture->setNom($data['nom']);
                        $nourriture->setGrammage($data['grammage']);
                        $nourriture->setDate($date);
                        $nourriture->setHeure($heure);
                        $nourriture->setAnimal($animal);

                        $this->manager->persist($nourriture);
                        $this->manager->flush();

                        $responseData = $this->serializer->serialize($nourriture, 'json', ['groups' => 'nourriture_read']);
                        $animalPrenom = $animal->getPrenom();// Récupérer le prénom de l'animal
                        $responseDataArray = json_decode($responseData, true); // Convertir en tableau
                        $responseDataArray['animal_prenom'] = $animalPrenom; // Ajouter le prénom
                        $responseData = json_encode($responseDataArray); // Convertir de nouveau en JSON

                        $headers["Location"] = $this->urlGenerator->generate(
                            'app_api_nourriture_show',
                            ['id' => $nourriture->getId()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de la consommation de nourriture: ' . $e->getMessage(), ['exception' => $e]);
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $responseData = json_encode(['message' => 'Erreur interne du serveur', 'details' => $e->getMessage()]);
        }
        return new JsonResponse($responseData, $statusCode, $headers, true);
    }
    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/nourriture/{id}",
        summary: "Afficher une consommation de nourriture par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la consommation de nourriture à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Consommation de nourriture trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new Oa\Property(property: "Id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Du foin"),
                        new OA\Property(property: "grammage", type: "integer", example: 500),
                        new OA\Property(property: "date", type: "string", format: "date", example: "2023-07-10"),
                        new OA\Property(property: "heure", type: "string", format: "time", example: "14:30"),
                        new OA\Property(property: "animal_id", type: "object", example: 23),
                        new OA\Property(property: "animal_prenom", type: "string", example: "Fido"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Consommation de nourriture non trouvé"
            )
        ]
    )]
    public function show(int $id): Response
    {
        $nourriture = $this->nourritureRepository->findOneBy(['id' => $id]);

        if ($nourriture) {
            $animal = $nourriture->getAnimal();

            $responseData = [
                "Id" => $nourriture->getId(),
                "nom" => $nourriture ->getNom(),
                'grammage' => $nourriture->getGrammage(),
                'date' => $nourriture->getDate()->format('Y-m-d'),
                'heure' => $nourriture->getHeure()->format('H:i'),
                'animal_id' => $animal ? $animal->getId() : null,
                'animal_prenom' => $animal ? $animal->getPrenom() : null,
            ];
            return new JsonResponse($responseData, Response::HTTP_OK);

        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: "/api/nourriture/{id}",
        summary: "Editer une consommation de nourriture",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la consommation de nourriture à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de la consommation de nourriture à modifier",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "Du foin"),
                    new OA\Property(property: "grammage", type: "integer", example: 500),
                    new OA\Property(property: "date", type: "string", format: "date", example: "2023-07-10"),
                    new OA\Property(property: "heure", type: "string", format: "time", example: "14:00"),
                    new OA\Property(property: "animal", type: "object", example : 23),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Consommation de nourriture modifié avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Consommation de nourriture non trouvé"
            )
        ]
    )]
    public function edit(int $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $nourriture = $this->nourritureRepository->findOneBy(['id' => $id]);
        $animal = $this->animauxRepository->find($data['animal']);
        $date = DateTime::createFromFormat('Y-m-d', $data['date']);
        $heure = DateTime::createFromFormat('H:i', $data['heure']);

        $errorMessages = [];
        if (!$nourriture) {
            $errorMessages[] = 'Consommation de nourriture non trouvée';
        }
        if (!$animal) {
            $errorMessages[] = 'Animal non trouvé';
        }
        if ($date === false || $heure === false) {
            $errorMessages[] = 'Format de date ou d\'heure invalide';
        }

        if (!empty($errorMessages)) {
            $statusCode = Response::HTTP_NOT_FOUND;
            if (in_array('Format de date ou d\'heure invalide', $errorMessages)) {
                $statusCode = Response::HTTP_BAD_REQUEST;
            }
            return new JsonResponse(['message' => implode('. ', $errorMessages)], $statusCode);
        }

        $nourriture->setNom($data['nom']);
        $nourriture->setGrammage($data['grammage']);
        $nourriture->setDate($date);
        $nourriture->setHeure($heure);
        $nourriture->setAnimal($animal);

        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/nourriture/{id}",
        summary: "Supprimer une consommation de nourriture par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la consommation de nourriture à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Consommation de nourriture suprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Consommation de nourriture non trouvé"
            )
        ]
    )]
    public function delete(int $id): Response
    {
        $nourriture = $this->nourritureRepository->findOneBy(['id' => $id]);
        if ($nourriture) {
            $this->manager->remove($nourriture);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('_all', name: 'list_all', methods: 'GET')]
    #[OA\Get(
        path: "/api/nourriture_all",
        summary: "Liste tous les consommations de nourriture",
        responses: [
            new OA\Response(
                response: 200,
                description: "La liste de tous les consommations de nourriture",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "nom", type: "string", example: "Du foin"),
                            new OA\Property(property: "grammage", type: "integer", example: 500),
                            new OA\Property(property: "date", type: "string", format: "date", example: "2023-07-10"),
                            new OA\Property(property: "heure", type: "string", format: "time", example: "14:00"),
                            new OA\Property(property: "animal", type: "object", example: 23),
                            new OA\Property(property: "animal_prenom", type: "string", example: "Fido"),
                        ]
                    )
                )
            )
        ]
    )]
    public function listAll(NourritureRepository $repository, SerializerInterface $serializer): Response
    {
        try{
            $nourritures = $this->nourritureRepository->findAll();
        $responseData = []; // Assurez-vous d'initialiser un tableau vide

        foreach ($nourritures as $nourriture) {
            $animal = $nourriture->getAnimal();

            // Créez un tableau pour chaque consommation de nourriture
            $nourritureData = [
                "Id" => $nourriture->getId(),
                'animal_prenom' => $animal ? $animal->getPrenom() : null,
                "nom" => $nourriture->getNom(),
                'grammage' => $nourriture->getGrammage(),
                'date' => $nourriture->getDate()->format('Y-m-d'),
                'heure' => $nourriture->getHeure()->format('H:i'),
            ];

            // Ajoutez les données de cette consommation à la liste
            $responseData[] = $nourritureData;
        }

        // Retournez l'ensemble des données dans la réponse JSON
        return new JsonResponse($responseData, Response::HTTP_OK);
        }
        catch(\Exception $e){
            $this->logger->error('Erreur lors de la récupération des nourriture: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['message' => 'Erreur interne du serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
