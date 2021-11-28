<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage(EntityManagerInterface $em, ProductRepository $productRepository)
    {
        // $productRepository = $em->getRepository(Product::class);
        // $product = $productRepository->find(1); // j’appelle un produit
        // $em->remove($product); // je marque ce produit comme étant supprimé
        // $em->flush(); // j’envoie la requête sql
        $products = $productRepository->findBy([], [], 3); // pas de critère de recherche, pas de critère d’ordonancement, limite=3
        // dd($products); // ok, voit nos 3 produits
        return $this->render('home.html.twig', ['products' => $products]);
    }
}
