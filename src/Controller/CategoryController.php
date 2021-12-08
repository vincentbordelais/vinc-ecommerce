<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CategoryController extends AbstractController
{
    /**
     * @Route("/admin/category/create", name="category_create")
     */
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $category = new Category(); // création d'une catégorie vide
        $form = $this->createForm(CategoryType::class, $category); // récup du formulaire de catégorie
        $form->handleRequest($request); // je veux que tu analyse la requête
        if ($form->isSubmitted() && $form->isValid()) { // si mon formulaire est soumis et valide
            $category->setSlug(strtolower($slugger->slug($category->getName()))); // création du slug
            $em->persist($category);
            $em->flush(); // je sauve ma nouvelle catégorie
            return $this->redirectToRoute('homepage');
        }
        $formView = $form->createView();
        return $this->render('category/create.html.twig', [
            'formView' => $formView,
        ]);
    }
    /**
     * @Route("admin/category/{id}/edit", name="category_edit")
     */
    public function edit($id, Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $category = $categoryRepository->find($id); // récup de la catégorie à éditer
        // dd($category);
        if (!$category) {
            throw new NotFoundHttpException("Cette catégorie n'existe pas");
        }

        // $user = $this->getUser();
        // if (!$user) {
        //     return $this->redirectToRoute('security_login');
        // }
        // if ($user !== $category->getOwner()) {
        //     throw new AccessDeniedHttpException("Vous n'êtes pas le propriétaire de cette catégorie");
        // }

        $form = $this->createForm(CategoryType::class, $category); // récup du formulaire de catégorie pré-rempli
        $form->handleRequest($request); // je veux que tu analyse la requête
        if ($form->isSubmitted() && $form->isValid()) { // si mon formulaire est soumis
            $em->flush(); // je sauve ma nouvelle catégorie
            return $this->redirectToRoute('homepage');
        }

        $formView = $form->createView();
        return $this->render('category/edit.html.twig', [
            'formView' => $formView,
            'category' => $category,
        ]);
    }
}
