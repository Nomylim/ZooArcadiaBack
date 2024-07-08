<?php

namespace App\Controller;

use App\Controller\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(private SerializerInterface $serializer, private EntityManagerInterface $manager)
    {

    }

    #[Route('/registration', name: 'registration', methods: 'POST')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class,'json');
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(
            ['user'=>$user->getUserIdentifier(), 'apiToken' => $user->getApiToken, 'roles' => $user->getRoles],
            Response::HTTP_CREATED
        );
    }
}
