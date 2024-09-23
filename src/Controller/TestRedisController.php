<?php

namespace App\Controller;

use Predis\Client; // On utilise Predis\Client
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TestRedisController extends AbstractController
{
    private $redis;

    public function __construct(Client $redis) // Injection de Predis\Client
    {
        $this->redis = $redis;
    }

    /**
     * @Route("/test-redis", name="test_redis")
     */
    public function index()
    {
        // Exemple de commande Redis
        $this->redis->set('test', 'Hello Redis!');
        $value = $this->redis->get('test');

        return $this->json(['value' => $value]);
    }
}

