<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

use function PHPSTORM_META\type;

#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(private SerializerInterface $serializer, private EntityManagerInterface $manager)
    {
    }
    
    #[Route('/registration', name: 'registration', methods: 'POST')]
    #[OA\Post(
        path: "/api/registration",
        summary: "Inscription d'un nouvel utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'utilisateur à inscrire",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "email", type: "string", example: "adresse@mail.com"),
                    new OA\Property(property: "password", type: "string", example: "Mot de passe"),
                    new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string", example: "ROLE_USER"))
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Utilisateur inscrit avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "user", type: "string", example: "adresse@mail.com"),
                        new OA\Property(property: "apiToken", type: "string", example: "355s48f43sf628sef355s48f43sf628sef"),
                        new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string", example: "ROLE_USER"))
                    ]
                )
            )
        ]
    )]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class,'json');
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(
            ['user'=>$user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()],
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'login', methods: 'POST')]
    #[OA\Post(
        path:"/api/login",
        summary: "Connecter un utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'utilisateur pour se connecter",
            content : new OA\JsonContent(
                type : "object",
                properties: [
                    new OA\Property(property: "username", type: "string", example:"adresse@mail.com"),
                    new OA\Property(property:"password", type: "string", example: "Mot de passe")
                ]
            )
        ),
        responses: [
            new OA\Response(
            response: 200,
            description: "Connexion réussie",
            content: new OA\JsonContent(
                type : "object",
                properties: [
                    new OA\Property(property: "user", type: "string", example:"Nom d'utilisateur"),
                    new OA\Property(property:"apiToken", type: "string", example: "355s48f43sf628sef355s48f43sf628sef"),
                    new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string", example: "ROLE_USER")),
                ]
            )
        )]
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if(null === $user){
            return new JsonResponse(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(
            ['user'=>$user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ]);
    }
}
