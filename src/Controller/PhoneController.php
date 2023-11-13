<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use App\Service\CacheService;
use App\Service\VersioningService;
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

class PhoneController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des téléphones.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des téléphones",
     *
     *     @OA\JsonContent(
     *        type="array",
     *
     *        @OA\Items(ref=@Model(type=Phone::class))
     *     )
     * )
     *
     * @OA\Parameter(
     *     name="offset",
     *     in="query",
     *     description="La page que l'on veut afficher (défaut: 1)"
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre maximum de téléphones affichés par page (défaut: 3)"
     * )
     *
     * @OA\Tag(name="Phones")
     */
    #[Route('/api/phones', name: 'phone', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour effectuer cette action')]
    public function getPhoneList(PhoneRepository $phoneRepository, SerializerInterface $serializer, Request $request, VersioningService $versioningService, CacheService $cacheService): JsonResponse
    {
        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setVersion($version);
        $offset = $request->get('offset', 1);
        $limit = $request->get('limit', 3);
        $phoneList = $phoneRepository->findAllWithPagination($offset, $limit);
        $cacheKey = 'phoneList-'.$offset.'-'.$limit;
        $jsonPhoneList = $serializer->serialize($phoneList, 'json', $context);
        $cacheJsonPhoneList = $cacheService->getOrCache($cacheKey, $jsonPhoneList, ['phoneList']);

        return new JsonResponse($cacheJsonPhoneList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer un téléphone grace à son id.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne le téléphone correspondant à l'id",
     *
     *     @OA\JsonContent(
     *        type="array",
     *
     *        @OA\Items(ref=@Model(type=Phone::class))
     *     )
     * )
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="L'id du téléphone que l'on veut récupérer"
     * )
     *
     * @OA\Tag(name="Phones")
     */
    #[Route('/api/phones/{id}', name: 'detailPhone', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour effectuer cette action')]
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer, VersioningService $versioningService, CacheService $cacheService): JsonResponse
    {
        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setVersion($version);
        $phoneId = $phone->getId();
        $cacheKey = 'getDetailPhone'.$phoneId;
        $jsonPhone = $serializer->serialize($phone, 'json', $context);
        $cacheJsonPhone = $cacheService->getOrCache($cacheKey, $jsonPhone, ['getDetailUser']);

        return new JsonResponse($cacheJsonPhone, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
