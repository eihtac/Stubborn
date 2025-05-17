<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
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

    #[Route('/product/{id}', name: 'app_product')]
    public function show(Product $product): Response
    {
        return $this->render('product/product.html.twig', [
            'product' => $product,
        ]);
    }
}
