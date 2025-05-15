<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column]
    private float $price;

    #[ORM\Column(length: 255)]
    private string $imageFileName;

    #[ORM\Column]
    private bool $highlight = false;

    #[ORM\Column]
    private int $stockXS = 2;

    #[ORM\Column]
    private int $stockS = 2;

    #[ORM\Column]
    private int $stockM = 2;

    #[ORM\Column]
    private int $stockL = 2;

    #[ORM\Column]
    private int $stockXL = 2;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getImageFileName(): string
    {
        return $this->imageFileName;
    }

    public function setImageFileName(string $imageFileName): static
    {
        $this->imageFileName = $imageFileName;

        return $this;
    }

    public function isHighlight(): bool
    {
        return $this->highlight;
    }

    public function setHighlight(bool $highlight): static
    {
        $this->highlight = $highlight;

        return $this;
    }

    public function getStockXS(): int
    {
        return $this->stockXS;
    }

    public function setStockXS(int $stockXS): static
    {
        $this->stockXS = $stockXS;

        return $this;
    }

    public function getStockS(): int
    {
        return $this->stockS;
    }

    public function setStockS(int $stockS): static
    {
        $this->stockS = $stockS;

        return $this;
    }

    public function getStockM(): int
    {
        return $this->stockM;
    }

    public function setStockM(int $stockM): static
    {
        $this->stockM = $stockM;

        return $this;
    }

    public function getStockL(): int
    {
        return $this->stockL;
    }

    public function setStockL(int $stockL): static
    {
        $this->stockL = $stockL;

        return $this;
    }

    public function getStockXL(): int
    {
        return $this->stockXL;
    }

    public function setStockXL(int $stockXL): static
    {
        $this->stockXL = $stockXL;

        return $this;
    }
}
