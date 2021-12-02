<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route("/{slug}", name="product_category")
     *
     * @param mixed $slug
     */
    public function category($slug, CategoryRepository $categoryRepository)
    {
        $category = $categoryRepository->findOneBy(['slug' => $slug]);
        // dd($category);
        if (!$category) {
            // throw new NotFoundHttpException("La catégorie demandée n'existe pas");
            throw $this->createNotFoundException("La catégorie demandée n'existe pas"); //on fera plus tard une page d'erreur personnalisée
        }
        return $this->render('product/category.html.twig', [
            'slug' => $slug,
            'category' => $category,
        ]);
    }
    /**
     * @Route("/{category_slug}/{slug}", name="product_show")
     *
     * @param mixed $slug
     */
    public function show($slug, ProductRepository $productRepository)
    {
        $product = $productRepository->findOneBy(['slug' => $slug]);
        // dd($product);
        if (!$product) {
            throw $this->createNotFoundException("Le produit demandé n'existe pas");
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
    /**
     * @Route("/admin/product/create", name="product_create")
     */
    public function create(Request $request, SluggerInterface $slugger, EntityManagerInterface $em)
    {
        $product = new Product(); // on crée un produit vide
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request); // je veux que tu regardes la requête
        if ($form->isSubmitted() && $form->isValid()) {
            $product->setSlug(strtolower(($slugger->slug($product->getName()))));
            $em->persist($product); // persist et flush pour l'enregistrer en bdd
            $em->flush();
            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(),
                'slug' => $product->getSlug(),
            ]);
        }
        $formView = $form->createView();
        return $this->render('product/create.html.twig', [
            'formView' => $formView,
        ]);
    }
    /**
     * @Route("/admin/product/{id}/edit", name="product_edit")
     *
     * @param mixed $id
     */
    public function edit($id, ProductRepository $productRepository, Request $request, EntityManagerInterface $em)
    {
        $product = $productRepository->find($id);
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $product = $form->getData(); // inutile puisque mon formulaire travaille sur $product
            // $em->persist($product); // inutile puisque product existe déjà en bdd
            $em->flush();
            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(), // merci Doctrine et ses relations
                'slug' => $product->getSlug(),
            ]);
        }
        $formView = $form->createView();
        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'formView' => $formView,
        ]);
    }
}
