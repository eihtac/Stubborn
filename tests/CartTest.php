<?php

namespace App\Tests;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    public function testCartTotal(): void
    {
        $product1 = new Product();
        $product1->setName('Sweat 1');
        $product1->setPrice(25);

        $product2 = new Product();
        $product2->setName('Sweat 2');
        $product2->setPrice(20.99);

        $item1 = new CartItem();
        $item1->setProduct($product1);
        $item1->setSize('M');

        $item2 = new CartItem();
        $item2->setProduct($product2);
        $item2->setSize('L');

        $cart = new Cart();
        $cart->addItem($item1);
        $cart->addItem($item2);

        $this->assertEqualsWithDelta(45.99, $cart->getTotal(), 0.01);
    }
}