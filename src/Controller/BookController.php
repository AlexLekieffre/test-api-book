<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookController extends AbstractController
{

    //Read all books
    #[Route('/api/books', name: 'books', methods: ['GET'])]
    public function getAllBooks(BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $bookList = $bookRepository->findAll();
        $jsonBookList = $serializer->serialize($bookList,'json',['groups'=>'getBooks']);

        return new JsonResponse($jsonBookList,Response::HTTP_OK,[],true);
    }

    //Read by id
    #[Route('/api/books/{id}', name: 'detailBook',methods:['GET'])]
    public function getDetailBook(Book $book, SerializerInterface $serializer)
    {
       $jsonBook = $serializer -> serialize($book,'json',[ 'groups' => 'getBooks' ]);
       return new JsonResponse($jsonBook,Response::HTTP_OK,[],true); 
        
    }

    //Delete by id
    #[Route('/api/books/{id}', name: 'deletebook',methods:['DELETE'])]
    public function deleteBook(Book $book, EntityManagerInterface $em): JsonResponse
    {
       $em->remove($book);
       $em->flush();
       return new JsonResponse(null,Response::HTTP_NO_CONTENT); 
        
    }

    //Create
    #[Route('/api/books', name: 'createBook',methods:['POST'])]
    #[IsGranted('ROLE_ADMIN',message:'vous ne possedez pas les droits necessaire a la création d\'un livre')]
    public function createBook(Request $request,SerializerInterface $serializer, EntityManagerInterface $em,
    UrlGeneratorInterface $url,AuthorRepository $authorRepository,ValidatorInterface $validator): JsonResponse
    {
        $book = $serializer -> deserialize($request -> getContent(),Book::class,'json');

        $error = $validator->validate($book);
        if($error->count()>0){
            return new JsonResponse($serializer->serialize($error,'json'),JsonResponse::HTTP_BAD_REQUEST,[],true);
        }

        $em->persist($book);
        $em->flush();

        $content = $request -> toArray();
        $idAuthor = $content['idAuthor'] ?? -1;

        $book -> setAuthor($authorRepository -> find($idAuthor));

        $jsonBook = $serializer->serialize($book,'json',[ 'groups' => 'getBooks' ]);
        $location = $url->generate('detailBook',['id'=>$book->getId()],UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonBook,Response::HTTP_CREATED,["location"=>$location],true); 
        
    }


    #[Route('/api/books/{id}', name: 'updateBook',methods:['PUT'])]
    public function updateBook(Book $currentBook, SerializerInterface $serializer,Request $request,
     EntityManagerInterface $em, AuthorRepository $authorRepository)
    {
        $updateBook = $serializer -> deserialize($request -> getContent(),Book::class ,'json',
        [AbstractNormalizer::OBJECT_TO_POPULATE=>[$currentBook]]);

        $content = $request -> toArray();
        $idAuthor = $content['idAuthor'] ?? -1;

        $updateBook -> setAuthor($authorRepository ->find($idAuthor));

        $em->persist($updateBook);
        $em->flush();

       return new JsonResponse(null,Response::HTTP_NO_CONTENT); 
        
    }


}
