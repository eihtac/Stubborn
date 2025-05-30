<?php 

namespace App\Service;

use App\Entity\Cart;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    public function createCheckoutSession(Cart $cart): string
    {
        $lineItems = [];

        foreach ($cart->getItems() as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $item->getProduct()->getPrice() * 100, 
                    'product_data' => [
                        'name' => $item->getProduct()->getName() . ' - ' . $item->getSize(),
                    ],
                ],
                'quantity' => 1,
            ];
        }

        $successUrl = $this->urlGenerator->generate('app_payment_success', ['cartId' => $cart->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $cancelUrl = $this->urlGenerator->generate('app_cart', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        return $session->id;
    }
}