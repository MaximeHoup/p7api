<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CacheService;
use App\Service\VersioningService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ClientController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private TagAwareCacheInterface $cache,
        private VersioningService $versioningService
    ) {
    }

    /**
     * Cette méthode permet de récupérer l'ensemble des utilisateurs liés à un client.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des utilisateurs liés à un client",
     *
     *     @OA\JsonContent(
     *        type="array",
     *
     *        @OA\Items(ref=@Model(type=Client::class))
     *     )
     * )
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="L'id du client"
     * )
     * @OA\Parameter(
     *     name="offset",
     *     in="query",
     *     description="La page que l'on veut afficher (défaut: 1)"
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre maximum d'utilisateurs affichés par page (défaut: 3)"
     * )
     *
     * @OA\Tag(name="Users")
     */
    #[Route('/api/clients/{id}/users', name: 'userList', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour effectuer cette action')]
    public function getUserList(Client $client, UserRepository $userRepository, SerializerInterface $serializer, Request $request, VersioningService $versioningService, CacheService $cacheService): JsonResponse
    {
        $offset = $request->get('offset', 1);
        $limit = $request->get('limit', 3);
        $clientid = $client->getId();
        $userList = $userRepository->findUsersWithPagination($clientid, $offset, $limit);

        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setVersion($version);

        $cacheKey = 'getUserList-'.$offset.'-'.$limit;
        $context->setGroups(['getUsers']);

        $jsonUserList = $serializer->serialize($userList, 'json', $context);
        $cacheJsonUserList = $cacheService->getOrCache($cacheKey, $jsonUserList, ['getUsers']);

        return new JsonResponse($cacheJsonUserList, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Cette méthode permet de récupérer un utilisateur grace à son id.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne l'utilisateur correspondant à l'id",
     *
     *     @OA\JsonContent(
     *        type="array",
     *
     *        @OA\Items(ref=@Model(type=User::class))
     *     )
     * )
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="L'id de l'utilisateur"
     * )
     *
     * @OA\Tag(name="Users")
     */
    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour effectuer cette action')]
    public function getDetailUser(User $user, VersioningService $versioningService, SerializerInterface $serializer, CacheService $cacheService): JsonResponse
    {
        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setVersion($version);
        $userId = $user->getId();
        $cacheKey = 'getDetailUser-'.$userId;
        $context->setGroups(['getDetailUser']);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        $cacheJsonUser = $cacheService->getOrCache($cacheKey, $jsonUser, ['getDetailUser']);

        return new JsonResponse($cacheJsonUser, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Cette méthode permet de créer un utilisateur.
     *
     * @OA\RequestBody(
     *     required=true,
     *
     *     @OA\MediaType(
     *         mediaType="application/json",
     *
     *         @OA\Schema(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="firstName",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="lastName",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string"
     *             ),
     *         )
     *     )
     * )
     *
     * @OA\Response(
     *     response=201,
     *     description="Crée l'utilisateur",
     * )
     *
     * @OA\Tag(name="Users")
     */
    #[Route('/api/clients/{id}/users', name: 'createUser', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour effectuer cette action')]
    public function createUser(Client $client, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cachePool, VersioningService $versioningService): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        // On vérifie les erreurs
        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $client->addUser($user);
        $cachePool->invalidateTags(['getUsers']);
        $em->persist($user);
        $em->flush();
        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setVersion($version);
        $context->setGroups(['getDetailUser']);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * Cette méthode permet de supprimer un utilisateur.
     *
     * @OA\Response(
     *     response=204,
     *     description="Supprime l'utilisateur correspondant à l'id"
     * )
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="L'id de l'utilisateur"
     * )
     *
     * @OA\Tag(name="Users")
     */
    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour effectuer cette action')]
    public function deleteUser(User $user, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $cachePool->invalidateTags(['getUsers', 'getDetailUser']);
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
