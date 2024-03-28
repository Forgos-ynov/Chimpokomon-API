<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PersonaRepository;
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
    #[Route("/api/register", name: "user_post_register", methods: "POST")]
    public function register(PersonaRepository $oPersonnaRepository, Request $oRequest, SerializerInterface $serializer, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $manager, ValidatorInterface $validator, JWTTokenManagerInterface $tokenManager): JsonResponse
    {
        $aContent = $oRequest->toArray();
        $oUser = new User();
        $oUser->setPassword($userPasswordHasher->hashPassword($oUser, $aContent["password"]));
        $oUser->setUsername($aContent["username"]);
        $oUser->setPersona($oPersonnaRepository->getFirstPersonna()[0]);
        $oUser->setRoles(["PUBLIC"]);

        $aErrors = $validator->validate($oUser);
        if ($aErrors->count() > 0) {
            return new JsonResponse($serializer->serialize($aErrors, "json"), Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        }

        $manager->persist($oUser);
        $manager->flush();

        $jwtToken = $tokenManager->create($oUser);
        $jsonToken = $serializer->serialize(["token" => $jwtToken], "json");
        return new JsonResponse($jsonToken, Response::HTTP_CREATED, [], true);
    }
}
