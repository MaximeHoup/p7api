<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class PhoneController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des téléphones.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des téléphones",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Phone::class))
     *     )
     * )
     * @OA\Tag(name="Phones")
     *
     * @param PhoneRepository $phoneRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/phones', name: 'phone', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour effectuer cette action')]
    public function getPhoneList(PhoneRepository $phoneRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idCache = 'getAllPhones';
        $jsonPhoneList = $cache->get($idCache, function (ItemInterface $item) use ($phoneRepository, $serializer) {
            $item->tag('phonesCache');
            $phoneList = $phoneRepository->findAll();

            return $serializer->serialize($phoneList, 'json');
        });

        return new JsonResponse($jsonPhoneList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer un téléphone grace à son id.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne le téléphone correspondant à l'id",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Phone::class))
     *     )
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="L'id du téléphone que l'on veut récupérer"
     * )
     * @OA\Tag(name="Phones")
     *
     * @param Phone $phone
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/phones/{id}', name: 'detailPhone', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour effectuer cette action')]
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idCache = 'getDetailPhone';
        $jsonPhone = $cache->get($idCache, function (ItemInterface $item) use ($serializer) {
            $item->tag('DetailPhonesCache');

            return $serializer->serialize($item, 'json');
        });
        $jsonPhone = $serializer->serialize($phone, 'json');

        return new JsonResponse($jsonPhone, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
