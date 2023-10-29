<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'client', methods: ['GET'])]
    public function getClientList(ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $clientList = $clientRepository->findAll();
        $jsonClientList = $serializer->serialize($clientList, 'json', ['groups' => 'getClients']);
        return new JsonResponse($jsonClientList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{id}', name: 'detailclient', methods: ['GET'])]
    public function getDetailClient(Client $client, SerializerInterface $serializer): JsonResponse
    {
        $jsonClient = $serializer->serialize($client, 'json', ['groups' => 'getClients']);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
