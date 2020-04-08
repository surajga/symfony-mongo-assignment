<?php

namespace App\Controller;

use App\Document\User;
use App\Form\Type\UserType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="user_index", methods={"GET"})
     */
    public function indexAction(DocumentManager $dm)
    {
        $allUsers = $dm->createQueryBuilder(User::class)
                ->getQuery();
        return $this->render('users/index.html.twig', [
            'all_users' => $allUsers,
        ]);
    }

    /**
     * @Route("/add", name="user_add", methods={"GET","POST"})
     */
    public function addAction(DocumentManager $dm,Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

		if ($form->isSubmitted()) {
            $userData = $form->getData();
			$dm->persist($userData);
            $dm->flush();
            return $this->redirectToRoute('user_index');
        }

        return $this->render('users/add.html.twig', [
            'edit_id' => 0,
            'form' => $form->createView(), 
        ]);        
    }

    /**
     * @Route("/edit/{id}", name="user_edit", methods={"PUT"})
     */
    public function editAction(DocumentManager $dm,Request $request, $id)
    {
        $user = $dm->find(User::class, $id);
        if(!$user){
            throw $this->createNotFoundException(
                'There are no user with the following id: ' . $id
                );            
        }
        $form = $this->createForm(UserType::class, $user,['method'=>'PUT']);
        // $form->setMethod('put');
        $form->handleRequest($request);

		if ($form->isSubmitted()) {
            $userData = $form->getData();
			$dm->persist($userData);
            $dm->flush();
            return $this->redirectToRoute('user_index');
        }

        return $this->render('users/edit.html.twig', [
            'edit_id' => $id,
            'form' => $form->createView(), 
        ]);

    }

    /**
     * @Route("/delete/{id}", name="user_delete", methods={"DELETE"})
     */
    public function deleteAction(DocumentManager $dm,$id)
    {
        $user = $dm->find(User::class, $id);
        if($user){
            $dm->remove($user);
            $dm->flush();
        }else{
            throw $this->createNotFoundException(
                'There are no user with the following id: ' . $id
                );
        }
        return $this->redirectToRoute('user_index');
    }
}