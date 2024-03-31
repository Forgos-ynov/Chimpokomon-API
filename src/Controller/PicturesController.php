<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
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

    /**
     * Permet de récupérer les informations d'une image
     *
     * @param int $id
     * @param PictureRepository $repository
     * @param UrlGeneratorInterface $urlGenerator
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route("/api/pictures/{id}", name: "pictures_get_byId", methods: ["GET"])]
    public function byId(int $id, PictureRepository $repository, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer): JsonResponse
    {
        $oPicture = $repository->find($id);

        if (is_null($oPicture))
        {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        // Génération de la localisation
        $location = $urlGenerator->generate("app_pictures", [], UrlGeneratorInterface::ABSOLUTE_URL);
        $location = str_replace("/public/", "/", $location . $oPicture->getPublicPath() . "/" . $oPicture->getRealPath());

        $jsonPicture = $serializer->serialize($oPicture, "json", ["groups" => "getOnPicture"]);
        return new JsonResponse($jsonPicture, Response::HTTP_OK, ["Location" => $location], true);

    }

    /**
     * Permet d'imprter une image
     *
     * @param Request $oRequest
     * @param EntityManagerInterface $manager
     * @param SerializerInterface $serializer
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
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
