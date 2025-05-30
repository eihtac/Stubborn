<?php

namespace App\Controller;

use App\Repository\CartRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartRepository $cartRepository): Response
    {
        $user = $this->getUser();

        $cart = $cartRepository->findOneBy(['user' => $user, 'status' => 'cart']);

        return $this->render('cart/cart.html.twig', [
            'cart' => $cart,
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
        ]);
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove_item', methods: ['POST'])]
    public function removeItem(int $id, CartRepository $cartRepository, EntityManagerInterface $entityManager): Response
    {
        $cart = $cartRepository->findOneBy(['user' => $this->getUser(), 'status' => 'cart']);

        $item = $cart->getItems()->filter(fn($i) => $i->getId() === $id)->first();

        $cart->removeItem($item);
        $entityManager->remove($item);
        $entityManager->flush();

        $this->addFlash('success', 'Le produit a été retiré du panier');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/checkout', name: 'app_checkout', methods: ['POST'])]
    public function checkout(CartRepository $cartRepository, StripeService $stripeService): JsonResponse
    {
        $user = $this->getUser();

        $cart = $cartRepository->findOneBy(['user' => $user, 'status' => 'cart']);

        if (!$cart || count($cart->getItems()) === 0) {
            return $this->json(['error' => 'Panier vide'], 400);
        }

        $sessionId = $stripeService->createCheckoutSession($cart);

        return $this->json(['id' => $sessionId]);
    }

    #[Route('/payment-success', name: 'app_payment_success')]
    public function paymentSuccess(Request $request, EntityManagerInterface $entityManager, CartRepository $cartRepository): Response
    {
        $user = $this->getUser();
        $cartId = $request->query->get('cartId');
        $cart = $cartRepository->find($cartId);

        if (!$cart || $cart->getUser() !== $user || $cart->getStatus() !== 'cart') {
            $this->addFlash('error', 'Commande invalide');
            return $this->redirectToRoute('app_cart');
        }

        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $size = $item->getSize();

            switch ($size) {
                case 'XS': $product->setStockXS($product->getStockXS() - 1); break;
                case 'S':  $product->setStockS($product->getStockS() - 1); break;
                case 'M':  $product->setStockM($product->getStockM() - 1); break;
                case 'L':  $product->setStockL($product->getStockL() - 1); break;
                case 'XL': $product->setStockXL($product->getStockXL() - 1); break;
            }
        }

        $cart->setStatus('order');
        $entityManager->flush();

        $this->addFlash('success', 'Merci pour votre commande !');
        return $this->redirectToRoute('app_home');
    }
}
