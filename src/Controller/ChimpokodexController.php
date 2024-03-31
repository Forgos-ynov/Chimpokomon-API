<?php

namespace App\Controller;

use App\Entity\Chimpokodex;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Repository\ChimpokodexRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ChimpokodexController extends AbstractController
{
    private ChimpokodexRepository $oRepository;
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer,ChimpokodexRepository $oRepository)
    {
        $this->oRepository = $oRepository;
        $this->serializer = $serializer;
    }

    /**
     * Renvoie l'ensemble des chimpokomon du chimpokodex
     *
     * @param ChimpokodexRepository $oRepository
     * @param SerializerInterface $serializer
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route("/api/chimpokodex", name: "chimpokodex_get_all", methods: "GET")]
    #[OA\Response(
        response: 200,
        description: "Retourne la liste des chimpokomon",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: new Model(type: Chimpokodex::class))
        ))]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function all(ChimpokodexRepository $oRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        $idCache = "getAllChimpokodex";
        $jsonChimpokodex = $cache->get($idCache, function (ItemInterface $item) use ($oRepository, $serializer) {
            $item->tag("chimpokodexCache");
            $aChimpokodex = $oRepository->allActivated();
            return $serializer->serialize($aChimpokodex, "json", ["groups" => "getAllChimpokodex"]);
        });

        return new JsonResponse($jsonChimpokodex, 200, [], true);
    }

    #[Route("/api/chimpokodex/random", name: "chimpokodex_getRandomChimpokodex_get", methods: ["GET"])]
    public function getRandomChimpokodex(Request $oRequest): JsonResponse
    {
        $defaultValue = 10;
        if ($oRequest->getContent() === "")
        {
            $randomNumber = $defaultValue;
        } else
        {
            $randomNumber = $oRequest->toArray()["randomNumber"] ?? $defaultValue;
        }

        $aRandomChimpokodex = $this->oRepository->getRandomChimpokodex($randomNumber);

        $jsonChimpokodex = $this->serializer->serialize($aRandomChimpokodex, 'json', ['groups' => 'getAllChimpokodex']);
        return new JsonResponse($jsonChimpokodex, Response::HTTP_OK, [], true);
    }

    /**
     * Renvoie un chimpokomon du chimpokodex suivant son id
     *
     * @param int $id
     * @param SerializerInterface $serializer
     * @param ChimpokodexRepository $oChimpokodexRepository
     * @return JsonResponse
     */
    #[Route("/api/chimpokodex/{id}", name: "chimpokodex_get_byId", methods: "GET")]
    public function byId(int $id, SerializerInterface $serializer, ChimpokodexRepository $oChimpokodexRepository): JsonResponse
    {
        $aChimpokodex = $oChimpokodexRepository->byIdActivated($id);

        if (sizeof($aChimpokodex) === 1) {
            $jsonChimpokodex = $serializer->serialize($aChimpokodex[0], "json", ["groups" => "getAllChimpokodex"]);
            return new JsonResponse($jsonChimpokodex, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(["message" => "Ressource not found :'("], Response::HTTP_NOT_FOUND);
    }

    /**
     * Permet de créer un chimpokomon du chimpokodex
     *
     * @param ChimpokodexRepository $repository
     * @param Request $oRequest
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @param EntityManagerInterface $manager
     * @param ValidatorInterface $validator
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route("/api/chimpokodex", name: "chimpokodex_post_create", methods: "POST")]
    public function create(ChimpokodexRepository $repository, Request $oRequest, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, EntityManagerInterface $manager, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {
        $oChimpokodex = $serializer->deserialize($oRequest->getContent(), Chimpokodex::class, "json");
        $oDate = new DateTime();

        // Gestion des évolutions
        $evolutionId = $oRequest->toArray()["evolutionId"];
        $oEvolution = $repository->find($evolutionId);
        if (!is_null($oEvolution)) {
            $oChimpokodex->addEvolution($oEvolution);
        }

        $oChimpokodex->setStatus("on")
            ->setCreatedAt($oDate)
            ->setUpdatedAt($oDate);

        $aErrors = $validator->validate($oChimpokodex);
        if ($aErrors->count() > 0) {
            return new JsonResponse($serializer->serialize($aErrors, "json"), Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        }

        $manager->persist($oChimpokodex);
        $manager->flush();

        // Invalidation du cache
        $cache->invalidateTags(["chimpokodexCache"]);

        $location = $urlGenerator->generate("chimpokodex_get_byId", ["id" => $oChimpokodex->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $jsonChimpokodex = $serializer->serialize($oChimpokodex, "json", ["groups" => "getAllChimpokodex"]);
        return new JsonResponse($jsonChimpokodex, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Modifier un élément du chimpokodex
     *
     * @param Chimpokodex $oChimpokodex
     * @param Request $oRequest
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $manager
     * @param ChimpokodexRepository $repository
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route("/api/chimpokodex/{id}", name: "chimpokodex_put_update", methods: "PUT")]
    public function update(Chimpokodex $oChimpokodex, Request $oRequest, SerializerInterface $serializer, EntityManagerInterface $manager, ChimpokodexRepository $repository, TagAwareCacheInterface $cache): JsonResponse
    {
        $oUpdatedChimpokodex = $serializer->deserialize($oRequest->getContent(), Chimpokodex::class, "json", [AbstractNormalizer::OBJECT_TO_POPULATE => $oChimpokodex]);
        $oUpdatedChimpokodex->setUpdatedAt(new DateTime());

        // Gestion des modifications d'évolution
        $evolutionId = $oRequest->toArray()["evolutionId"];
        if (is_int($evolutionId)) {
            $evolutionId = array($evolutionId);
        }
        if (is_array($evolutionId)) {
            foreach ($evolutionId as $evolution) {
                $oEvolution = $repository->find($evolution);
                if (!is_null($oEvolution)) {
                    $oChimpokodex->addEvolution($oEvolution);
                }
            }
        }

        $manager->persist($oUpdatedChimpokodex);
        $manager->flush();

        // Invalidation du cache
        $cache->invalidateTags(["chimpokodexCache"]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Permet de faire un hard ou un soft reset
     * Hard reset => suppression totale de la donnée
     * Soft reset => passage de son status en "off"
     *
     * @param Chimpokodex $oChimpokodex
     * @param Request $oRequest
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route("/api/chimpokodex/{id}", name: "chimpokodex_delete_delete", methods: "DELETE")]
    public function delete(Chimpokodex $oChimpokodex, Request $oRequest, EntityManagerInterface $manager, TagAwareCacheInterface $cache): JsonResponse
    {
        $force = false;
        if (!is_null($oRequest->getContentType())) {
            $aContent = $oRequest->toArray();
            $force = isset($aContent["force"]) && $aContent["force"] === true;
        }

        if ($force) {
            // Un hard delete est demandé
            $manager->remove($oChimpokodex);
        } else {
            // Un soft delete est fait
            $oChimpokodex->setStatus("off");
            $oChimpokodex->setUpdatedAt(new DateTime());
            $manager->persist($oChimpokodex);
        }
        $manager->flush();

        // Invalidation du cache
        $cache->invalidateTags(["chimpokodexCache"]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
