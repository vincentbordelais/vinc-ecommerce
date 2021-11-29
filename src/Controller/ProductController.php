<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ProductRepository;

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
}
