<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $listAuthor=[];
        for($i =1;$i < 10; $i++){
            $author=new Author();
            $author->setFirstName('prenom ' . $i);
            $author->setLastName('nom ' . $i);
            $manager->persist($author);
            $listAuthor[]=$author;
        }

        for($i =1;$i < 20; $i++){
            $book=new Book();
            $book->setTitle('livre ' . $i);
            $book->setCoverText('description du livre ' . $i);
            $book->setAuthor($listAuthor[array_rand($listAuthor)]);
            $manager->persist($book);

        
    }
    $manager->flush();
}
}