<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $filePath = __DIR__ . '/products.json';

        if (!file_exists($filePath)) {
            throw new \RuntimeException('Fichier JSON introuvable : ' . $filePath);
        }

        $json = file_get_contents($filePath);
        $products = json_decode($json, true);

        foreach ($products as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setPrice($data['price']);
            $product->setHighlight($data['highlight']);
            $product->setImageFileName($data['imageFileName']);
            $product->setStockXS($data['stockXS']);
            $product->setStockS($data['stockS']);
            $product->setStockM($data['stockM']);
            $product->setStockL($data['stockL']);
            $product->setStockXL($data['stockXL']);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
