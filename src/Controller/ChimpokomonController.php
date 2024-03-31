<?php

namespace App\Controller;

use App\Entity\Chimpokomon;
use App\Repository\ChimpokodexRepository;
use App\Repository\ChimpokomonRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ChimpokomonController extends AbstractController
{
    private ChimpokomonRepository $repository;
    private SerializerInterface $serializer;
    private EntityManagerInterface $manager;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $manager,
                                UrlGeneratorInterface $urlGenerator, ChimpokomonRepository $repository)
    {
        $this->serializer = $serializer;
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
        $this->repository = $repository;
    }

    /**
     * Route de récupération d'un chimpokomon suivant son id
     *
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/api/chimpokomon/{id}', name: 'chimpokomon_byId_get', methods: "GET")]
    public function byId(int $id): JsonResponse
    {
        $aChimpokomon = $this->repository->byIdActivated($id);

        if (sizeof($aChimpokomon) === 1) {
            $jsonChimpokomon = $this->serializer->serialize($aChimpokomon[0], "json", ["groups" => "getAllChimpokokomon"]);
            return new JsonResponse($jsonChimpokomon, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(["message" => "Ressource not found :'("], Response::HTTP_NOT_FOUND);
    }

    /**
     * Création d'un chimpokomon suivant un chimpokodexId
     *
     * @param Request $oRequest
     * @param ChimpokodexRepository $oRepository
     * @return JsonResponse
     * @throws \Random\RandomException
     */
    #[Route('/api/chimpokomon', name: 'chimpokomon_create_post', methods: "POST")]
    public function create(Request $oRequest, ChimpokodexRepository $oRepository): JsonResponse
    {
        // Vérification et récupération du chimpokodex passé dans le body
        $aContent = $oRequest->toArray();
        if (!isset($aContent["chimpokodexId"]))
        {
            return new JsonResponse($this->serializer->serialize(["mesage" => "Il n'y a pas de chimpokodexId envoyé."], "json"),
                Response::HTTP_BAD_REQUEST, [], true);
        }

        $oChimpokodex = $oRepository->byIdActivated($aContent["chimpokodexId"]);
        if ($oChimpokodex === [])
        {
            return new JsonResponse($this->serializer->serialize(["mesage" => "Le chimpokodexId doit -être une référence à l'id d'un chimpokodex qui existe."], "json"),
                Response::HTTP_BAD_REQUEST, [], true);
        }
        $oChimpokodex = $oChimpokodex[0];

        // Création du chimpokomon
        $oChimpokomon = new Chimpokomon();
        $oChimpokomon->setName($oChimpokodex->getName())
            ->setPvMax(random_int($oChimpokodex->getMinPv(), $oChimpokodex->getMaxPv()))
            ->setPv($oChimpokomon->getPvMax())
            ->setAttack(random_int($oChimpokodex->getMinAttack(), $oChimpokodex->getMaxAttack()))
            ->setDefense(random_int($oChimpokodex->getMinDefense(), $oChimpokodex->getMaxDefense()))
            ->setChimpokodex($oChimpokodex)
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime())
            ->setStatus("on");

        $this->manager->persist($oChimpokomon);
        $this->manager->flush();

        $location = $this->urlGenerator->generate("chimpokomon_byId_get", ["id" => $oChimpokomon->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $jsonChimpokomon = $this->serializer->serialize($oChimpokomon, "json", ["groups" => "getAllChimpokokomon"]);
        return new JsonResponse($jsonChimpokomon, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
