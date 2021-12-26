<?php

namespace App\Controller\Purchase;

use DateTime;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PurchasePersister extends AbstractController
{
    protected $productRepository;
    protected $em;
    public function __construct(ProductRepository $productRepository, EntityManagerInterface $em)
    {
        $this->productRepository = $productRepository;
        $this->em = $em;
    }
    public function storePurchase(Purchase $purchase, SessionInterface $session)
    {
        // 6. Nous allons la lier avec l’utilisateur actuellement connecté (Security)
        $purchase
            ->setUser($this->getUser())
            ->setPurchasedAt(new DateTime());
        $this->em->persist($purchase);
        // dd($purchase);
        // 7. Nous allons la lier avec les produits qui sont dans le panier (CartService)
        $cart = $session->get('cart', []);
        // dd($cart);
        $cartItems = [];
        foreach ($cart as $id => $qty) {
            $product = $this->productRepository->find($id);
            $cartItems[] = [ // j'oublie toujours les []
                'product' => $product,
                'qty' => $qty,
            ];
        }
        $total = 0;
        // dd($cartItems);
        foreach ($cartItems as $cartItem) {
            // dd($cartItem);
            // dd($cartItem['product']); // c'est une class
            // dd($cartItem['product']->getPrice());
            $purchaseItem = new PurchaseItem(); // ligne de commande
            // dd($purchaseItem);
            $purchaseItem
                ->setProduct($cartItem['product'])
                ->setPurchase($purchase)
                ->setProductName($cartItem['product']->getName())
                ->setProductPrice($cartItem['product']->getPrice())
                ->setQuantity($cartItem['qty'])
                ->setTotal($cartItem['qty'] * $cartItem['product']->getPrice());
            // $total += $cartItem->getTotalPerProduct();
            $total += $purchaseItem->getTotal();
            $this->em->persist($purchaseItem);
            // dd($purchaseItem);
        }
        $purchase->setTotal($total);
        // dd($purchase);
    }
}
