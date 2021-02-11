<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleController extends AbstractController
{

    /**
     * @Route("/user/add", name="userAdd", methods={"POST"})
     */
    public function registration(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $var =  $request->getContent();
        try {
            $user = $serializer->deserialize($var, User::class, 'json');
            $user->setDatecreation(new \DateTime());

            $errors = $validator->validate($user);

            if(count($errors) > 0) {
                return $this->json($errors, 400);
            }

            $em->persist($user);
            $em->flush();

            return $this->json([
                'status' => 201,
                'message' => 'Utilisateur bien créé'
            ]);
        } catch(NotEncodableValueException $e){
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @Route("/user/login", name="login", methods={"GET"})
     */
    public function connection(UserRepository $userRepository, Request $request, SerializerInterface $serializer): Response
    {
        $var =  $request->getContent();
        $user = $serializer->decode($var, 'json');
        $userFind = $userRepository->findOneBy(['username' => $user['username']]);

        if ($userFind == NULL){
            return $this->json([
                'status' => 400,
                'message' => 'Utilisateur inconnu'
            ]);
        }

        if($userFind->getPassword() != $user['password']){
            return $this->json([
                'status' => 400,
                'message' => 'Mot de passe erroné'
            ]);
        }
        return $this->json($userFind, 200);
    }

    /**
     * @Route("/article/add", name="articleCreate", methods={"POST"})
     */
    public function createArticle(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $var =  $request->getContent();
        try {
            $article = $serializer->deserialize($var, Article::class, 'json');
            $article->setDate(new \DateTime());

            $errors = $validator->validate($article);

            if (count($errors) > 0) {
                return $this->json($errors, 400);
            }

            $em->persist($article);
            $em->flush();

            return $this->json([
                'status' => 201,
                'message' => 'Article bien créé'
            ]);
        } catch
            (NotEncodableValueException $e) {
                return $this->json([
                    'status' => 400,
                    'message' => $e->getMessage()
                ]);
        }
    }
    /**
     * @Route("/article", name="article", methods={"GET"})
     */
    public function getAllArticle(ArticleRepository $articleRepository): Response
    {
        $allArticle = $articleRepository->findAll();
        $datas = array();
        foreach ($allArticle as $key => $article) {
            $datas[$key]['id'] = $article->getId();
            $datas[$key]['title'] = $article->getTitle();
            $datas[$key]['subTitle'] = $article->getSubTitle();
            $datas[$key]['author'] = $article->getAuthor();
            $datas[$key]['text'] = $article->getText();
            $datas[$key]['image'] = $article->getImage();
            $datas[$key]['date'] = $article->getDate();
            $datas[$key]['categorie'] = $article->getCategorie();
        }
        return new JsonResponse($datas);
    }

    /**
     * @Route("/article/get", name="articleFindOne", methods={"GET"})
     */
    public function getOneArticle(ArticleRepository $articleRepository, Request $request, SerializerInterface $serializer): Response
    {
        $var =  $request->getContent();
        $json = json_decode($var);
        dd($json);
        //$article = $articleRepository->find($id);
    }

    /**
     * @Route("/article/update", name="articleUpdate", methods={"PUT"})
     */
    public function updateArticle(ArticleRepository $articleRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $em): Response
    {
        $article = $serializer->decode($request->getContent(), 'json');
        $articleFind = $articleRepository->findOneBy([
            'id' => $article['id']
        ]);

        if ($articleFind == NULL) {
            return $this->json([
                'status' => 400,
                'message' => 'une erreure s\'est produite'
            ]);
        }

        $articleFind->setTitle($article['title']);
        $articleFind->setSubTitle($article['sub_title']);
        $articleFind->setAuthor($article['author']);
        $articleFind->setText($article['text']);
        $articleFind->setImage($article['image']);
        $articleFind->setCategorie($article['categorie']);

        $em->persist($articleFind);
        $em->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Article mis a jour',
        ]);
    }

    /**
     * @Route("/article/delete", name="articleDelete", methods={"DELETE"})
     */
    public function deleteArticle(Request $request, ArticleRepository $articleRepository, SerializerInterface $serializer, EntityManagerInterface $em): Response
    {
        $id = $serializer->decode($request->getContent(), 'json');
        $article = $articleRepository->find($id);

        $em->remove($article);
        $em->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Article supprimé',
        ]);
    }
}
