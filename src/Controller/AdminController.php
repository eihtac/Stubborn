<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository, SluggerInterface $slugger): Response
    {
        $products = $productRepository->findAll();

        $newProduct = new Product();
        $formAdd = $this->createForm(ProductFormType::class, $newProduct);
        $formAdd->handleRequest($request);
        
        if ($formAdd->isSubmitted() && $formAdd->isValid()) {
            $imageFile = $formAdd->get('imageFile')->getData();
            $newFilename = $slugger->slug($newProduct->getName()) . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/images',
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('error', "Erreur lors de l'envoi de l'image");
                return $this->redirectToRoute('app_admin');
            }

            $newProduct->setImageFileName($newFilename);

            $entityManager->persist($newProduct);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('app_admin');
        }

        $formEdit = [];
        foreach ($products as $product) {
            $form = $this->createForm(ProductFormType::class, $product, [
                'attr' => ['id' => 'edit-form-' . $product->getId()],
            ]);
            $formEdit[$product->getId()] = $form->createView();
        }

        return $this->render('admin/admin.html.twig', [
            'products' => $products,
            'formAdd' => $formAdd->createView(),
            'formEdit' => $formEdit,
        ]);
    }

    #[Route('/admin/product/{id}/edit', name: 'admin_product_edit', methods: ['POST'])]
    public function edit(Product $product, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if ($request->request->has('delete')) {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit supprimé');
            return $this->redirectToRoute('app_admin');
        }

        $oldImage = $product->getImageFileName();

        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $newFilename = $slugger->slug($product->getName()) . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/images',
                        $newFilename
                    );
                    $product->setImageFileName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', "Erreur lors de la modification de l'image");
                    return $this->redirectToRoute('app_admin');
                }
            } else {
                $product->setImageFileName($oldImage);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Produit modifié');
        }

        return $this->redirectToRoute('app_admin');
    }
}
