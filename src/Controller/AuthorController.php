<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class AuthorController extends AbstractController
{
    #[Route('/api/authors', name: 'authors', methods: ['GET'])]
    public function getAllAuthors(AuthorRepository $authorRepository,SerializerInterface $serializer): JsonResponse
    {
        $authorList = $authorRepository->findAll();
        $jsonAuthorList = $serializer->serialize($authorList,'json',['groups'=>'getAuthors']);
        return new JsonResponse($jsonAuthorList,Response::HTTP_OK,[],true);
     
    }
    #[Route('/api/authors/{id}', name: 'detailAuthors',methods:['GET'])]
    public function getDetailAuthors(Author $author, SerializerInterface $serializer): JsonResponse
    {
       $jsonAuthor = $serializer->serialize($author,'json',['groups'=>'getAuthors']);
       return new JsonResponse($jsonAuthor,Response::HTTP_OK,[],true); 
        
    }
    #[Route('/api/authors/{id}', name: 'deleteAuthors',methods:['DELETE'])]
    public function deleteAuthors(Author $author, EntityManagerInterface $em): JsonResponse
    {
       $em->remove($author);
       $em->flush();
       return new JsonResponse(null,Response::HTTP_NO_CONTENT); 
        
    }
    #[Route('/api/authors',name:"creatAuthor",methods:'POST')]
    public function createAuthor(Request $request, SerializerInterface $serializer, 
    EntityManagerInterface $em, UrlGeneratorInterface $url):JsonResponse
    {
        $author = $serializer -> deserialize($request ->getContent(),Author::class,'json');
        $em -> persist($author);
        $em->flush();

        $jsonAuthor = $serializer ->serialize($author,'json',['groups'=> 'getAuthors']);
        $location = $url->generate('detailAuthors',['id'=>$author->getId()],UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonAuthor,Response::HTTP_CREATED,["location"=>$location],true);
    }
    #[Route('/api/authors/{id}', name: 'updateAuthors',methods:['PUT'])]
    public function updateAuthors(Author $currentAuthor, SerializerInterface $serializer,
    Request $request,EntityManagerInterface $em): JsonResponse
    {
       $updateAuthor = $serializer->deserialize($request->getContent(),Author::class,'json',
       [AbstractNormalizer::OBJECT_TO_POPULATE=>$currentAuthor]);

       $em->persist($updateAuthor);
       $em->flush();
       return new JsonResponse(null,Response::HTTP_NO_CONTENT); 
        
    }
}
