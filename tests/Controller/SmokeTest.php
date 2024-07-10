<?php

namespace App\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

class SmokeTest extends WebTestCase
{
    public function testApiDocUrlIsSuccessful(): void
    {
        $client = self::createClient();
        $client->request('GET', 'api/doc');

        self::assertResponseIsSuccessful();
    }

    /*public function testApiRegistrationUrlIsSecure(): void
    {
        $client = self::createClient();
        $client->request('GET', 'api/registration');

        self::assertResponseStatusCodeSame(401);
    }*/

    public function testRegisterRouteCanCreateAValidUser(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/registration', [], [], ['CONTENT-TYPE' => 'application/json',],
        json_encode([
            'email' => 'toto@mail.com',
            'password' => 'toto',
            'roles' => ['ROLE_USER'],
        ], JSON_THROW_ON_ERROR));
        $statusCode = $client->getResponse()->getStatusCode();
        dd($statusCode);
    }
    public function testLoginRouteCanConnectAValidUser(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/login', [], [], ['Content-Type' => 'application/json',],
        json_encode([
            'email' => 'toto@mail.com',
            'password' => 'toto',
        ], JSON_THROW_ON_ERROR));
        $statusCode = $client->getResponse()->getStatusCode();
        dd($statusCode);
    }

}
