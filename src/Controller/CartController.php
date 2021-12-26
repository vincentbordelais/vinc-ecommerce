<?php

namespace App\Controller;

use App\Cart\CartService;
use App\Form\CartConfirmationType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class CartController extends AbstractController
{
    protected $productRepository;
    protected $cartService;
    public function __construct(ProductRepository $productRepository, CartService $cartService)
    {
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add", requirements={"id":"\d+"})
     * Permet d'ajouter un produit au panier.
     */
    public function add($id, SessionInterface $session, ProductRepository $productRepository, CartService $cartService): Response
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException("Le produit {$id} n'existe pas !");
        }

        $this->cartService->add($id); // recup du code 

        $this->addFlash('success', 'Le produit a bien été ajouté au pannier');

        return $this->redirectToRoute('product_show', [
            'category_slug' => $product->getCategory()->getSlug(),
            'slug' => $product->getSlug(),
        ]);
    }

    /**
     * @Route("/cart", name="cart_show")
     * Affiche la liste des commandes et le formulaire d'addresse.
     */
    public function show(SessionInterface $session, ProductRepository $productRepository)
    {
        $form = $this->createForm(CartConfirmationType::class);

        $cart = $session->get('cart', []);
        $detailedCart = [];
        $total = 0;
        foreach ($cart as $id => $qty) {
            $product = $this->productRepository->find($id);
            $detailedCart[] = [ // j'oublie toujours les []
                'product' => $product,
                'qty' => $qty,
            ];
            $total += $product->getPrice() * $qty; // le total général
        }
        // dd($detailedCart);
        return $this->render('cart/index.html.twig', [
            'items' => $detailedCart,
            'total' => $total,
            'confirmationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/cart/delete/{id}", name="cart_delete", requirements={"id":"\d+"})
     * Permet de supprimer une ligne de commande.
     *
     * @param mixed $id
     */
    public function delete($id, ProductRepository $productRepository, CartService $cartService)
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException("Le produit {$id} n'existe pas et ne peut pas être supprimé");
        }
        $this->cartService->remove($id);
        $this->addFlash('success', "Le produit '{$product->getName()}' a bien été supprimé du panier.");
        return $this->redirectToRoute('cart_show');
    }

    /**
     * @Route("/cart/increment/{id}", name="cart_increment", requirements={"id":"\d+"})
     * Permet d'incrémenter de 1 la quantité d'un produit.
     *
     * @param mixed $id
     */
    public function increment($id, ProductRepository $productRepository, CartService $cartService)
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException("Le produit {$id} n'existe pas et ne peut pas être supprimé");
        }
        $this->cartService->increment($id);
        $this->addFlash('success', "Le produit '{$product->getName()}' a bien été ajouté du panier.");
        return $this->redirectToRoute('cart_show');
    }

    /**
     * @Route("/cart/decrement/{id}", name="cart_decrement", requirements={"id":"\d+"})
     * Permet de décrémenter de 1 la quantité d'un produit.
     *
     * @param mixed $id
     */
    public function decrement($id, ProductRepository $productRepository, CartService $cartService)
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException("Le produit {$id} n'existe pas et ne peut pas être supprimé");
        }
        $this->cartService->decrement($id);
        $this->addFlash('success', "Le produit '{$product->getName()}' a bien été retiré du panier.");
        return $this->redirectToRoute('cart_show');
    }
}
