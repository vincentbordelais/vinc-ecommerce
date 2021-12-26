<?php

namespace App\Controller\Purchase;

use DateTime;
use App\Entity\Purchase;
use App\Cart\CartService;
use App\Entity\PurchaseItem;
use App\Form\CartConfirmationType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\Purchase\PurchasePersister;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PurchaseConfirmationController extends AbstractController
{
    protected $productRepository;
    protected $em;
    protected $cartService;
    protected $purchasePersister;
    public function __construct(ProductRepository $productRepository, EntityManagerInterface $em, CartService $cartService, PurchasePersister $purchasePersister)
    {
        $this->productRepository = $productRepository;
        $this->em = $em;
        $this->cartService = $cartService;
        $this->purchasePersister = $purchasePersister;
    }
    /**
     * @Route("/purchase/confirm", name="purchase_confirm")
     * @IsGranted("ROLE_USER", message="Vous devez être connété pour confirmer une commande")
     */
    public function confirm(Request $request, SessionInterface $session)
    {
        // 1. Nous voulons lire les données du formulaire (J’ai besoin de FormFactoryInterface et de Request)
        $form = $this->createForm(CartConfirmationType::class);
        // Analyse la request :
        $form->handleRequest($request);
        // 2. Si le formulaire n’a pas été soumis : dégager
        if (!$form->isSubmitted()) {
            // voir ajouter un message Flash
            $this->addFlash('warning', 'Vous devez remplir le formulaire de confirmation');
            return $this->redirectToRoute('cart_show');
        }
        // 3. Si je ne suis pas connecté (Security) : dégager
        // $user = $this->getUser();
        // 4. Si il n’y a pas de produit dans mon panier (SessionInterface ou CartService) : dégager
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
        // dd($cartItems);
        if (0 === count($cartItems)) {
            $this->addFlash('warning', 'Vous ne pouvez pas confirmer une commande avec un panier vide');
            // return new RedirectResponse($this->router->generate('cart_show'));
            return $this->redirectToRoute('cart_show');
        }
        // 5. Nous allons créer une purchase
        /** @var Purchase */
        $purchase = $form->getData();
        // dd($purchase);
        // 6. Nous allons la lier avec l’utilisateur actuellement connecté (Security)
        // dd($purchase);
        // 7. Nous allons la lier avec les produits qui sont dans le panier (CartService)
        // dd($purchase);
        $this->purchasePersister->storePurchase($purchase, $session);

        // // 8. Nous allons enregistrer la commande (Doctrine EntityManager)
        $this->em->flush();
        $this->cartService->empty();
        $this->addFlash('success', 'La commande a bien été enregistrée');
        return $this->redirectToRoute('purchase_index');
    }
}
