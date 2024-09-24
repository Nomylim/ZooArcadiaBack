<?php

namespace App\Controller;

use App\Entity\RapportVeterinaires;
use App\Repository\AnimauxRepository;
use OpenApi\Attributes as OA;
use App\Repository\RapportVeterinairesRepository;
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


#[Route('/api/rapportveterinaires', name: 'app_api_rapportveterinaires_')]
class RapportVeterinairesController extends AbstractController
{
    public function __construct(
        private RapportVeterinairesRepository $rapportVeterinairesRepository,
        private AnimauxRepository $animauxRepository,
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger
    ) {
    }

    #[Route(name: 'new', methods: 'POST')]
    #[OA\Post(
        path: "/api/rapportveterinaires",
        summary: "Créer un rapport vétérinaire",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du rapport vétérinaire à créer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nourriture", type: "string", example: "Du foin"),
                    new OA\Property(property: "grammage", type: "integer", example: 500),
                    new OA\Property(property: "date", type: "string", format: "date", example: "2023-07-10"),
                    new OA\Property(property: "etatanimal", type: "string", example: "en bonne santé"),
                    new OA\Property(property: "description", type: "string", example: "descriptif de l'animal plus poussé"),
                    new OA\Property(property: "animal", type: "integer", example: 23),
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
                        new OA\Property(property: "id", type: "integer", example: "1"),
                        new OA\Property(property: "nourriture", type: "string", example: "Du foin"),
                        new OA\Property(property: "grammage", type: "integer", example: 500),
                        new OA\Property(property: "date", type: "string", format: "date", example: "2023-07-10"),
                        new OA\Property(property: "etatanimal", type: "string", example: "en bonne santé"),
                        new OA\Property(property: "description", type: "string", example: "descriptif de l'animal plus poussé"),
                        new OA\Property(property: "animal", type: "integer", example: 23),
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

            if (!$data || !isset($data['nourriture'], $data['grammage'], $data['date'], $data['etatanimal'], $data['description'], $data['animal'])) {
                $statusCode = Response::HTTP_BAD_REQUEST;
                $responseData = json_encode(['message' => 'Données invalides']);
            } else {
                $animal = $this->animauxRepository->find($data['animal']);
                if (!$animal) {
                    $statusCode = Response::HTTP_NOT_FOUND;
                    $responseData = json_encode(['message' => 'Animal non trouvé']);
                } else {
                    $date = DateTime::createFromFormat('Y-m-d', $data['date']);

                    if ($date === false) {
                        $statusCode = Response::HTTP_BAD_REQUEST;
                        $responseData = json_encode(['message' => 'Format de date ou d\'heure invalide']);
                    } else {
                        $rapportveterinaire = new RapportVeterinaires();
                        $rapportveterinaire->setNourriture($data['nourriture']);
                        $rapportveterinaire->setGrammage($data['grammage']);
                        $rapportveterinaire->setDate($date);
                        $rapportveterinaire->setEtatAnimal($data['etatanimal']);
                        $rapportveterinaire->setDescription($data['description']);
                        $rapportveterinaire->setAnimal($animal);

                        //Ajoute le rapport à l'animal
                        $animal->getRapport()->add($rapportveterinaire);

                        $this->manager->persist($rapportveterinaire);
                        $this->manager->flush();

                        $responseData = $this->serializer->serialize($rapportveterinaire, 'json', ['groups' => 'rapportveterinaire_read']);
                        $animalPrenom = $animal->getPrenom();// Récupérer le prénom de l'animal
                        $responseDataArray = json_decode($responseData, true); // Convertir en tableau
                        $responseDataArray['animal_prenom'] = $animalPrenom; // Ajouter le prénom
                        $responseData = json_encode($responseDataArray); // Convertir de nouveau en JSON

                        $headers["Location"] = $this->urlGenerator->generate(
                            'app_api_rapportveterinaires_show',
                            ['id' => $animal->getId()],
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
        path: "/api/rapportveterinaires/{id}",
        summary: "Afficher un rapport vétérinaire par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du rapport vétérinaire à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Rapport vétérinaire trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: "1"),
                        new OA\Property(property: "nourriture", type: "string", example: "Du foin"),
                        new OA\Property(property: "grammage", type: "integer", example: 500),
                        new OA\Property(property: "date", type: "string", format: "date", example: "2023-07-10"),
                        new OA\Property(property: "etatanimal", type: "string", example: "en bonne santé"),
                        new OA\Property(property: "description", type: "string", example: "descriptif de l'animal plus poussé"),
                        new OA\Property(property: "animal_id", type: "integer", example: 23),
                        new OA\Property(property: "animal_prenom", type: "string", example: "Fido"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Rapport vétérinaire non trouvé"
            )
        ]
    )]
    public function show(int $id): Response
    {
        $rapportveterinaire = $this->rapportVeterinairesRepository->findOneBy(['id' => $id]);

        if ($rapportveterinaire) {
            // Obtenir l'animal associé
            $animal = $rapportveterinaire->getAnimal();

            // Créer une réponse personnalisée
            $responseData = [
                'id' => $rapportveterinaire->getId(),
                'nourriture' => $rapportveterinaire->getNourriture(),
                'grammage' => $rapportveterinaire->getGrammage(),
                'date' => $rapportveterinaire->getDate()->format('Y-m-d'),
                'etatanimal' => $rapportveterinaire->getEtatAnimal(),
                'description' => $rapportveterinaire->getDescription(),
                'animal_id' => $animal ? $animal->getId() : null,
                'animal_prenom' => $animal ? $animal->getPrenom() : null,
            ];

            return new JsonResponse($responseData, Response::HTTP_OK);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: "/api/rapportveterinaires/{id}",
        summary: "Editer un rapport vétérinaire",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du rapport vétérinaire à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du rapport vétérinaire à modifier",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nourriture", type: "string", example: "Du foin"),
                    new OA\Property(property: "grammage", type: "integer", example: 500),
                    new OA\Property(property: "date", type: "string", format: "date", example: "2023-07-10"),
                    new OA\Property(property: "etatanimal", type: "string", example: "en bonne santé"),
                    new OA\Property(property: "description", type: "string", example: "descriptif de l'animal plus poussé"),
                    new OA\Property(property: "animal_id", type: "integer", example: 23),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Rapport vétérinaire modifié avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Rapport vétérinaire non trouvé"
            )
        ]
    )]
    public function edit(int $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $rapportveterinaire = $this->rapportVeterinairesRepository->findOneBy(['id' => $id]);
        $animal = $this->animauxRepository->find($data['animal_id']);
        $date = DateTime::createFromFormat('Y-m-d', $data['date']);

        $errorMessages = [];
        if (!$rapportveterinaire) {
            $errorMessages[] = 'Rapport veterinaire non trouvée';
        }
        if (!$animal) {
            $errorMessages[] = 'Animal non trouvé';
        }
        if ($date === false) {
            $errorMessages[] = 'Format de date invalide';
        }

        if (!empty($errorMessages)) {
            $statusCode = Response::HTTP_NOT_FOUND;
            if (in_array('Format de date invalide', $errorMessages)) {
                $statusCode = Response::HTTP_BAD_REQUEST;
            }
            return new JsonResponse(['message' => implode('. ', $errorMessages)], $statusCode);
        }

        $rapportveterinaire->setNourriture($data['nourriture']);
        $rapportveterinaire->setGrammage($data['grammage']);
        $rapportveterinaire->setDate($date);
        $rapportveterinaire->setEtatAnimal($data['etatanimal']);
        $rapportveterinaire->setDescription($data['description']);
        $rapportveterinaire->setAnimal($animal);

        //Ajoute le rapport à l'animal
        $animal->getRapport()->add($rapportveterinaire);

        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/rapportveterinaires/{id}",
        summary: "Supprimer un rapport vétérinaire par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du rapport vétérinaire à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Rapport vétérinaire suprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Rapport vétérinaire non trouvé"
            )
        ]
    )]
    public function delete(int $id): Response
    {
        $rapportveterinaire = $this->rapportVeterinairesRepository->findOneBy(['id' => $id]);
        if ($rapportveterinaire) {
            $this->manager->remove($rapportveterinaire);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('_all', name: 'list_all', methods: 'GET')]
    #[OA\Get(
        path: "/api/rapportveterinaires_all",
        summary: "Liste tous les rapport veterinaires",
        responses: [
            new OA\Response(
                response: 200,
                description: "La liste de tous les rapport veterinaires",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "nourriture", type: "string", example: "Du foin"),
                            new OA\Property(property: "grammage", type: "integer", example: 500),
                            new OA\Property(property: "date", type: "string", format: "date", example: "2023-07-10"),
                            new OA\Property(property: "etatanimal", type: "string", example: "en bonne santé"),
                            new OA\Property(property: "description", type: "string", example: "descriptif de l'animal plus poussé"),
                            new OA\Property(property: "animal_id", type: "integer", example: 23),
                        ]
                    )
                )
            )
        ]
    )]
    public function listAll(RapportVeterinairesRepository $repository, SerializerInterface $serializer): Response
    {
        try {
            $rapports = $this->rapportVeterinairesRepository->findAll();

            $responseData = [];
            foreach ($rapports as $rapport) {
                $animal = $rapport->getAnimal(); // Assurez-vous d'avoir la méthode getAnimal()

                $responseData[] = [
                    'id' => $rapport->getId(),
                    'animal_prenom' => $animal ? $animal->getPrenom() : null, // Inclure le prénom de l'animal
                    'nourriture' => $rapport->getNourriture(),
                    'grammage' => $rapport->getGrammage(),
                    'date' => $rapport->getDate()->format('Y-m-d'),
                    'etatanimal' => $rapport->getEtatAnimal(),
                    'description' => $rapport->getDescription(),
                ];
            }

            return new JsonResponse($responseData, Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des rapports vétérinaires: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['message' => 'Erreur interne du serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
