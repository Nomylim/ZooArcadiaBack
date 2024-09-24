<?php
namespace App\Controller;

use Predis\Client; // Utilisation du client Redis (prenez Predis ou PhpRedis selon votre config)
use App\Repository\AnimauxRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AnimalController extends AbstractController
{
    private $redis;
    private $animauxRepository;

    public function __construct(Client $redis, AnimauxRepository $animauxRepository) // Injection du service Redis
    {
        $this->redis = $redis;
        $this->animauxRepository = $animauxRepository;
    }

    /**
     * @Route("animal/select/{animalId}", name="animal_select", methods={"POST"})
     */

    public function selectAnimal(int $animalId, Request $request): JsonResponse
    {
        $animal = $this->animauxRepository->find($animalId);

        if (!$animal) {
            return new JsonResponse(['erreur' => 'Animal not found'], 404);
        }

        // On utilise l'ID de l'animal comme clé Redis
        $key = 'animal:selection:' . $animalId;

        // Incrémenter le compteur de l'animal sélectionné dans Redis
        $this->redis->incr($key);

        // Obtenir la valeur actuelle du compteur
        $count = $this->redis->get($key);

        // Retourner les informations de l'animal et la nouvelle valeur du compteur en JSON
        return new JsonResponse([
            'animalId' => $animalId,
            'count' => $count,
            'prenom' => $animal->getPrenom(),
            'etat' => $animal->getEtat() 
        ]);
    }

    /**
     * @Route("animal/selection-count/{animalId}", name="animal_selection_count", methods={"GET"})
     */

    public function getSelectionCount(int $animalId): JsonResponse
    {
        // On utilise l'ID de l'animal comme clé Redis
        $key = 'animal:selection:' . $animalId;

        // Obtenir la valeur actuelle du compteur
        $count = $this->redis->get($key) ?? 0; // Si le compteur n'existe pas, on retourne 0

        return new JsonResponse(['animalId' => $animalId, 'count' => $count]);
    }

    /**
     * @Route("animal/vues", name="animal_vues", methods={"GET"})
     */
    public function getAllVues(): JsonResponse
    {
        // Récupérer tous les animaux de la base de données
        $animaux = $this->animauxRepository->findAll();

        // Initialiser un tableau pour stocker les résultats
        $results = [];

        // Boucler sur chaque animal et récupérer le compteur depuis Redis
        foreach ($animaux as $animal) {
            $animalId = $animal->getId();
            $key = 'animal:selection:' . $animalId;

            // Obtenir la valeur actuelle du compteur ou 0 si pas encore présent dans Redis
            $count = $this->redis->get($key) ?? 0;

            // Ajouter les infos de l'animal au tableau des résultats
            $results[] = [
                'animalId' => $animalId,
                'count' => $count,
                'prenom' => $animal->getPrenom(),
                'etat' => $animal->getEtat() 
            ];
        }

        // Retourner les résultats sous forme de JSON
        return new JsonResponse($results);
    }

}