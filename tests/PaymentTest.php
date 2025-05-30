<?php

namespace App\Tests;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    public function testPayment(): void
    {
        $product = new Product();
        $product->setName('Sweat Test');
        $product->setPrice(25);
        $product->setStockM(5);

        $item = new CartItem();
        $item->setProduct($product);
        $item->setSize('M');

        $cart = new Cart();
        $cart->addItem($item);

        switch ($item->getSize()) {
            case 'M':
                $product->setStockM($product->getStockM() - 1);
                break;
        }

        $cart->setStatus('order');

        $this->assertSame(4, $product->getStockM());
        $this->assertSame('order', $cart->getStatus());
    }
}