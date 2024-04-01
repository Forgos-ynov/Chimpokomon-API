<?php

namespace App\DataFixtures;

use App\Entity\Chimpokodex;
use App\Entity\Persona;
use App\Entity\Picture;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Random\RandomException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * @var
     */
    private $userPasswordHasher;

    /**
     * @var Generator
     */
    private Generator $faker;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->faker = Factory::create('fr_FR');
        $this->userPasswordHasher = $userPasswordHasher;
    }

    /**
     * Load New datas
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $today = new DateTime();

        $aPictureEntries = [];
        $aChimpokodexEntries = [];

        // Picture
        $aPicturesPath = glob(stream_resolve_include_path("public/medias/pictures") . "/*.*");
        foreach ($aPicturesPath as $picturePath)
        {
            $pictureRealPath = explode("/pictures/", $picturePath)[1];
            $oFile = new File($picturePath, true);
            $mimeType = strrchr($pictureRealPath, ".");
            $nameWithoutMime = substr($pictureRealPath, 0, strrpos($pictureRealPath, '-'));
            $name = $nameWithoutMime . $mimeType;

            $oPicture = new Picture();

            $oPicture->setStatus("on")
                ->setName($name)
                ->setCreatedAt($today)
                ->setUpdatedAt($today)
                ->setRealName($name)
                ->setMimeType($oFile->getMimeType())
                ->setRealPath($pictureRealPath)
                ->setPublicPath("/public/medias/pictures")
                ->setFile($oFile);

            $aPictureEntries[] = $oPicture;
            $manager->persist($oPicture);
        }

        // Chimpoxodex
        for ($i = 0; $i < 100; $i++)
        {
            $oChimpokodex = new Chimpokodex();
            $created = $this->faker->dateTimeBetween("-1 week");
            $updated = $this->faker->dateTimeBetween($created);
            $oChimpokodex
                ->setName($this->generateChimpokoName())
                ->setMaxPv(random_int(150, 250))
                ->setMinPv(random_int(100, $oChimpokodex->getMaxPv()))
                ->setMaxAttack(random_int(50, 150))
                ->setMinAttack(random_int(25, $oChimpokodex->getMaxAttack()))
                ->setMaxDefense(random_int(50, 100))
                ->setMinDefense(random_int(10, $oChimpokodex->getMaxDefense()))
                ->setCreatedAt($created)
                ->setUpdatedAt($updated)
                ->setStatus("on")
                ->setPicture($aPictureEntries[array_rand($aPictureEntries)]);

            $aChimpokodexEntries[] = $oChimpokodex;
            $manager->persist($oChimpokodex);
        }

        $oPersona = new Persona();
        $oPersona->setName("John Doe")
            ->setStatus("on")
            ->setEmail("johnDoe@nothing.com")
            ->setUpdatedAt($this->faker->dateTime())
            ->setCreatedAt($this->faker->dateTime())
            ->setAnonymous(true)
            ->setGender(0)
            ->setSurname("johnny")
            ->setBirthdate($this->faker->dateTimeBetween("-1 year"));
        $manager->persist($oPersona);

        // Set public User
        $oPublicUser = new User();
        $oPublicUser->setUsername("public")
            ->setRoles(["PUBLIC"])
            ->setPassword($this->userPasswordHasher->hashPassword($oPublicUser, "public"))
            ->setPersona($oPersona)
            ->setStatus("on")
            ->setCreatedAt($today)
            ->setUpdatedAt($today);
        $manager->persist($oPublicUser);

        // Set admin User
        $oAdminUser = new User();
        $oAdminUser->setUsername("admin")
            ->setRoles(["ADMIN"])
            ->setPassword($this->userPasswordHasher->hashPassword($oAdminUser, "password"))
            ->setPersona($oPersona)
            ->setStatus("on")
            ->setCreatedAt($today)
            ->setUpdatedAt($today);
        $manager->persist($oAdminUser);

        $manager->flush();
    }

    private function generateChimpokoName(): string
    {
        $aPrefixeName = ["meca", "shmi", "ane", "chau", "vey", "ki", "kri", "chini", "ra", "croke", "zipo", "refi"];
        $aPatternName = ["zouzou", "ro", "ssure", "lsri", "ffoun", "pipo", "poko", "tasdera", "lsi", "rugo", "xyde"];
        $aSuffixeName = ["mon"];

        $havePrefixe = random_int(0, 1);
        $haveSuffixe = $havePrefixe ? random_int(0, 1) : 1;
        $havePatternName = ($havePrefixe && $haveSuffixe) ? random_int(0, 1) : 1;

        $prefixe = $havePrefixe ? $aPrefixeName[array_rand($aPrefixeName)] : "";
        $patternName = $havePatternName ? $aPatternName[array_rand($aPatternName)] : "";
        $suffixe = $haveSuffixe ? $aSuffixeName[array_rand($aSuffixeName)] : "";

        return $prefixe . $patternName . $suffixe;

    }
}


