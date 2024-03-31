<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PersonaRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    private SerializerInterface $serializer;
    private UserPasswordHasherInterface $userPasswordHasher;
    private EntityManagerInterface $manager;
    private ValidatorInterface $validator;
    private JWTTokenManagerInterface $tokenManager;
    private UserRepository $repository;

    public function __construct(SerializerInterface      $serializer, UserPasswordHasherInterface $userPasswordHasher,
                                EntityManagerInterface   $manager, ValidatorInterface $validator,
                                JWTTokenManagerInterface $tokenManager, UserRepository $repository)
    {
        $this->serializer = $serializer;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->manager = $manager;
        $this->validator = $validator;
        $this->tokenManager = $tokenManager;
        $this->repository = $repository;
    }

    /**
     * Route d'inscription d'un utilisateur
     *
     * @param PersonaRepository $oPersonnaRepository
     * @param Request $oRequest
     * @return JsonResponse
     */
    #[Route("/api/register", name: "user_post_register", methods: "POST")]
    public function register(PersonaRepository $oPersonnaRepository, Request $oRequest): JsonResponse
    {
        $aContent = $oRequest->toArray();
        $today = new DateTime();
        $oUser = new User();
        $oUser->setPassword($this->userPasswordHasher->hashPassword($oUser, $aContent["password"]))
            ->setUsername($aContent["username"])
            ->setPersona($oPersonnaRepository->getFirstPersonna()[0])
            ->setRoles(["PUBLIC"])
            ->setStatus("on")
            ->setCreatedAt($today)
            ->setUpdatedAt($today);

        $aErrors = $this->validator->validate($oUser);
        if ($aErrors->count() > 0)
        {
            return new JsonResponse($this->serializer->serialize($aErrors, "json"), Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        }

        $this->manager->persist($oUser);
        $this->manager->flush();

        $jwtToken = $this->tokenManager->create($oUser);
        $jsonToken = $this->serializer->serialize(["token" => $jwtToken], "json");
        return new JsonResponse($jsonToken, Response::HTTP_CREATED, [], true);
    }

    /**
     * Fonction permettant de récupérer tous les informations d'un utilisateur
     *
     * @return JsonResponse
     */
    #[Route("/api/users", name: "user_get_all", methods: "GET")]
    public function all(): JsonResponse
    {
        $jsonUsers = $this->serializer->serialize($this->repository->getAllUsersActivated(), "json", ["groups" => "getAllUsers"]);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    /**
     * Permet de récupérer la team de l'utilisateur courrant
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route("/api/users/team", name: "user_get_teamConnectedUser", methods: "GET")]
    public function teamConnectedUser(Request $request): JsonResponse
    {
        $authorizationHeader = $request->headers->get('Authorization');
        $token = null;
        if ($authorizationHeader && preg_match('/^Bearer\s+(.*?)$/', $authorizationHeader, $matches)) {
            $token = $matches[1];
        }

        $oUser = $this->repository->getUserByUsername($this->tokenManager->parse($token)["username"]);
        $oTeam = $oUser->getTeam();

        if (is_null($oTeam))
        {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        $jsonTeam = $this->serializer->serialize($oTeam, "json");
        return new JsonResponse($jsonTeam, Response::HTTP_OK, [], true);
    }
}
