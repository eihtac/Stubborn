<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $range = $request->query->get('range');

        $products = match ($range) {
            '10-29' => $productRepository->findByPriceRange(10, 29),
            '30-35' => $productRepository->findByPriceRange(30, 35),
            '35-50' => $productRepository->findByPriceRange(35, 50),
            default => $productRepository->findAll(),
        };

        return $this->render('product/products.html.twig', [
            'products' => $products,
            'selectedRange' => $range,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product', methods: ['GET', 'POST'])]
    public function show(Product $product, Request $request, EntityManagerInterface $entityManager, CartRepository $cartRepository): Response
    {
        if ($request->isMethod('POST')) {
            $size = $request->request->get('size');
            $user = $this->getUser();
            $cart = $cartRepository->findOneBy(['user' => $user, 'status' => 'cart']);

            if (!$cart) {
                $cart = new Cart();
                $cart->setUser($user);
                $cart->setStatus('cart');
                $entityManager->persist($cart);
            }

            $item = new CartItem();
            $item->setCart($cart);
            $item->setProduct($product);
            $item->setSize($size);
            $entityManager->persist($item);
 
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté au panier !');
            return $this->redirectToRoute('app_cart');
        }

        $availableSizes = []; 
        if ($product->getStockXS() > 0) $availableSizes[] = 'XS';
        if ($product->getStockS() > 0) $availableSizes[] = 'S';
        if ($product->getStockM() > 0) $availableSizes[] = 'M';
        if ($product->getStockL() > 0) $availableSizes[] = 'L';
        if ($product->getStockXL() > 0) $availableSizes[] = 'XL';
        
        return $this->render('product/product.html.twig', [
            'product' => $product,
            'availableSizes' =>$availableSizes,
        ]);
    }
}
