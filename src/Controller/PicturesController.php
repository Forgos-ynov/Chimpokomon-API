<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PicturesController extends AbstractController
{
    #[Route('/', name: 'app_pictures')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PicturesController.php',
        ]);
    }

    #[Route("/api/pictures/{id}", name: "pictures_get_byId", methods: ["GET"])]
    public function byId(int $id, PictureRepository $repository, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer): JsonResponse
    {
        $oPicture = $repository->find($id);

        // Génération de la localisation
        $location = $urlGenerator->generate("app_pictures", [], UrlGeneratorInterface::ABSOLUTE_URL);
        $location = $location . str_replace("/public/", "", $oPicture->getPublicPath() . "/" . $oPicture->getRealPath());

        return $oPicture ?
            new JsonResponse($serializer->serialize($oPicture, "json"), Response::HTTP_OK, ["Location" => $location], true):
            new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route("/api/pictures", name: "pictures_post_create", methods: ["POST"])]
    public function create(Request $oRequest, EntityManagerInterface $manager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $oPicture = new Picture();
        $file = $oRequest->files->get("file");

        $oPicture->setFile($file);
        $oPicture->setMimeType($file->getClientMimeType());
        $oPicture->setRealName($file->getClientOriginalName());
        $oPicture->setName($file->getClientOriginalName());
        $oPicture->setPublicPath("/public/medias/pictures");
        $oPicture->setStatus("on")->setCreatedAt(new DateTime())->setUpdatedAt(new DateTime());

        $manager->persist($oPicture);
        $manager->flush();

        $jsonPicture = $serializer->serialize($oPicture, "json");
        $location = $urlGenerator->generate("pictures_get_byId", ["id" => $oPicture->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonPicture, Response::HTTP_CREATED, ["Location" => $location], "json");
    }
}
