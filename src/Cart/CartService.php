<?php

namespace App\Cart;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    protected $session;
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    public function add(int $id)
    {
        // 1 Retrouver le pannier dans la session (sous forme de tableau)
        // 2 S'il n'existe pas encore je le crée en prenant un tableau vide
        $cart = $this->session->get('cart', []);
        // 3 Voir si le produit ($id) existe déjà dans le tableau
        if (array_key_exists($id, $cart)) {
            // 4 Si c'est le cas, simplement augmenter la quantité
            $cart[$id]++;
            // 5 Sinon ajouter le produit avec la quantité 1
        } else {
            $cart[$id] = 1;
        }
        // 6 Enregistrer le tableau mis à jour dans la session
        $this->session->set('cart', $cart);
    }

    public function remove(int $id)
    {
        $cart = $this->session->get('cart', []);
        unset($cart[$id]); // on supprime la donnée qui est dans le tableau cart
        $this->session->set('cart', $cart);
    }

    public function increment(int $id)
    {
        $cart = $this->session->get('cart', []);
        if (array_key_exists($id, $cart)) {
            $cart[$id]++;
        }
        $this->session->set('cart', $cart);
    }

    public function decrement(int $id)
    {
        $cart = $this->session->get('cart', []);
        if (!array_key_exists($id, $cart)) { // Si le produit ($id) n'existe pas dans le tableau
            return; // rien à faire
        }
        if (1 === $cart[$id]) { // si la qté=1 il faut le supprimer
            $this->remove($id);
            return;
        }
        if ($cart[$id] >= 1) { // si la qté>=1 il faut le décrémenter
            --$cart[$id];
        }
        $this->session->set('cart', $cart);
    }

    public function empty()
    {
        $this->session->set('cart', []);
    }

    // public function getTotal(): int
    // {
    //     $total = 0;

    //     // foreach ($this->session->get('cart', []) as $id => $qty) {
    //     foreach ($this->getCart() as $id => $qty) {
    //         $product = $this->productRepository->find($id);

    //         if (!$product) {
    //             continue;
    //         }

    //         $total += $product->getPrice() * $qty;
    //     }

    //     return $total;
    // }

    // /**
    //  * @return CartItem[] // aide vs code à comprendre que la fonction renvoie un tableau de classes CartItem
    //  */
    // public function getDetailedCartItems(): array
    // {
    //     // dd($session->get('cart'));
    //         $detailedCart = []; // [ 12=>['produit'=>..., 'quantity'=>qté] ]
    //         // foreach ($this->session->get('cart', []) as $id => $qty) {
    //         foreach ($this->getCart() as $id => $qty) {
    //             $product = $this->productRepository->find($id);

    //             if (!$product) {
    //                 continue;
    //             }

    //             // $detailedCart[] = [
    //             //     'product' => $product,
    //             //     'qty' => $qty,
    //             // ];
    //             $detailedCart[] = new CartItem($product, $qty);
    //         }
    //     // dd($detailCart);
    //     return $detailedCart;
    // }

    // protected function getCart(): array
    // {
    //     return $this->session->get('cart', []);
    // }

    // protected function saveCart(array $cart)
    // {
    //     $this->session->set('cart', $cart);
    // }
}
