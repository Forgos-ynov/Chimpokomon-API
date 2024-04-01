<?php

namespace App\Controller;

use App\Entity\Chimpokomon;
use App\Entity\Team;
use App\Repository\ChimpokodexRepository;
use App\Repository\ChimpokomonRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class TeamController extends AbstractController
{
    private SerializerInterface $serializer;
    private EntityManagerInterface $manager;
    private UrlGeneratorInterface $urlGenerator;
    private TeamRepository $repository;

    public function __construct(SerializerInterface   $serializer, EntityManagerInterface $manager,
                                UrlGeneratorInterface $urlGenerator, TeamRepository $repository)
    {
        $this->serializer = $serializer;
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
        $this->repository = $repository;
    }

    /**
     * Renvoie une team suivant son id
     *
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/api/teams/{id}', name: 'team_byId_get', methods: "GET")]
    public function byId(int $id): JsonResponse
    {
        $aTeam= $this->repository->byIdActivated($id);

        if (sizeof($aTeam) === 1) {
            $jsonTeam = $this->serializer->serialize($aTeam[0], "json", ["groups" => "getAllTeam"]);
            return new JsonResponse($jsonTeam, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(["message" => "Ressource not found :'("], Response::HTTP_NOT_FOUND);
    }

    /**
     * Création d'un chimpokomon suivant un chimpokodexId
     *
     * @param Request $oRequest
     * @param ChimpokodexRepository $oChimpokodexRepository
     * @param ChimpokomonRepository $oChimpokomonRepository
     * @param UserRepository $oUserRepository
     * @param JWTTokenManagerInterface $tokenManager
     * @return JsonResponse
     * @throws \Random\RandomException
     */
    #[Route('/api/teams/add', name: 'team_addTeam_post', methods: "POST")]
    public function addTeam(Request               $oRequest, ChimpokodexRepository $oChimpokodexRepository,
                            ChimpokomonRepository $oChimpokomonRepository, UserRepository $oUserRepository,
                            JWTTokenManagerInterface $tokenManager): JsonResponse
    {
        // Vérification et récupération du chimpokodex passé dans le body
        $aContent = $oRequest->toArray();
        if (isset($aContent["chimpokodexId"]) || isset($aContent["chimpokomonId"]))
        {
            // Création du chimpokomon à ajouter dans la team
            if (isset($aContent["chimpokodexId"]))
            {
                $oChimpokodex = $oChimpokodexRepository->byIdActivated($aContent["chimpokodexId"]);
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
                    ->setPicture($oChimpokodex->getPicture())
                    ->setCreatedAt(new DateTime())
                    ->setUpdatedAt(new DateTime())
                    ->setStatus("on");

                $this->manager->persist($oChimpokomon);
            } else
            {
                $oChimpokomon = $oChimpokomonRepository->byIdActivated($aContent["chimpokomonId"]);
                if ($oChimpokomon === [])
                {
                    return new JsonResponse($this->serializer->serialize(["mesage" => "Le chimpokomonId doit -être une référence à l'id d'un chimpokomon qui existe."], "json"),
                        Response::HTTP_BAD_REQUEST, [], true);
                }
                $oChimpokomon = $oChimpokomon[0];
            }
        } else
        {
            return new JsonResponse($this->serializer->serialize(["mesage" => "Un Chimpokodex ou Chimpokomon id sont nécessaire"], "json"),
                Response::HTTP_BAD_REQUEST, [], true);
        }

        // Création de la team ou ajout du chimpokomon à la team de l'utilisateur connecté
        $authorizationHeader = $oRequest->headers->get('Authorization');
        $token = null;
        if ($authorizationHeader && preg_match('/^Bearer\s+(.*?)$/', $authorizationHeader, $matches)) {
            $token = $matches[1];
        }

        $oUser = $oUserRepository->getUserByUsername($tokenManager->parse($token)["username"]);
        $oTeam = $oUser->getTeam();

        if (is_null($oTeam))
        {
            $oTeam = new Team();
            $oTeam->setStatus("on")
                ->setCreatedAt(new DateTime())
                ->setTrainer($oUser)
                ->setFavorite(true);
        }

        $oTeam->setUpdatedAt(new DateTime())->setChimpokomon($oChimpokomon);
        $this->manager->persist($oTeam);
        $this->manager->flush();

        $location = $this->urlGenerator->generate("team_byId_get", ["id" => $oChimpokomon->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse(null, Response::HTTP_CREATED, ["Location" => $location]);
    }
}
