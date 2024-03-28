<?php

namespace App\DataFixtures;

use App\Entity\Chimpokodex;
use App\Entity\Persona;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Random\RandomException;
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
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        $chimpokodexEntries = [];
        for ($i = 0; $i < 100; $i++) {
            $chimpokodex = new Chimpokodex();
            $created = $this->faker->dateTimeBetween("-1 week");
            $updated = $this->faker->dateTimeBetween($created);
            $chimpokodex
                ->setName($this->generateChimpokoName())
                ->setPvMax(100)
                ->setCreatedAt($created)
                ->setUpdatedAt($updated)
                ->setStatus("on");

            $chimpokodexEntries[] = $chimpokodex;
            $manager->persist($chimpokodex);
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
            ->setPersona($oPersona);
        $manager->persist($oPublicUser);

        // Set admin User
        $oAdminUser = new User();
        $oAdminUser->setUsername("admin")
            ->setRoles(["ADMIN"])
            ->setPassword($this->userPasswordHasher->hashPassword($oAdminUser, "password"))
            ->setPersona($oPersona);
        $manager->persist($oAdminUser);



        /**
        $chimpokodexEntries = [];
        for ($i = 0; $i < 100; $i++) {
            //Instantiate new Chimpokodex Entity to Fullfill
            $chimpokodex = new Chimpokodex();
            //Handle created && updated datetime
            $created = $this->faker->dateTimeBetween("-1 week", "now");
            $updated = $this->faker->dateTimeBetween($created, "now");
            //Asign Properties to Entity
            $chimpokodex
                ->setName($this->faker->word())
                ->setPvMax(100)
                ->setCreatedAt($created)
                ->setUpdatedAt($updated)
                ->setStatus("on");

            //stock Chimpokodex Entry
            $chimpokodexEntries[] = $chimpokodex;
            //Add to transaction
            $manager->persist($chimpokodex);
        }

        //Execute transaction
        foreach ($chimpokodexEntries as $key => $chimpokodexEntry) {
            $evolution = $chimpokodexEntries[array_rand($chimpokodexEntries, 1)];
            $chimpokodexEntry->addEvolution($evolution);
            $manager->persist($chimpokodexEntry);
        }
*/
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


