<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Filter\ProduitFilter;
use App\Form\FilterProduitType;
use App\Repository\MarqueRepository;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/all', name: 'app_all_products', methods: ['GET', 'POST'])]
    public function index(ProduitRepository $produitRepository,MarqueRepository $marqueRepository, Request $request ): Response
    {


        $data = new ProduitFilter;
        $form = $this->createForm(FilterProduitType::class, $data);

        $form->handleRequest($request);


        dump($data);


        $produits = $produitRepository->findSearch($data);

        return $this->render('produit/products.html.twig', [
            'produits' => $produits,
            "form" => $form->createView()
        ]);
    }

    
    #[Route('/new', name: 'app_new_product', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitRepository $produitRepository,SluggerInterface $slugger): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file1 = $form->get('photo1')->getData();

            if($file1)
            {
                // création du nom du fichier
                $fileName = $slugger->slug($produit->getModele()) . uniqid() . '.' . $file1->getClientOriginalExtension();

                // Enregistrer le fichier dans le dossier public
                $file1->move(
                    $this->getParameter("produit"),
                    $fileName
                );
                $produit->setPhoto1($fileName);
            }

            $file2 = $form->get('photo2')->getData();

            if($file2)
            {
                // création du nom du fichier
                $fileName = $slugger->slug($produit->getModele()) . uniqid() . '.' . $file2->getClientOriginalExtension();

                // Enregistrer le fichier dans le dossier public
                $file2->move(
                    $this->getParameter("produit"),
                    $fileName
                );

                $produit->setPhoto2($fileName);
            }

            $file3 = $form->get('photo3')->getData();

            if($file3)
            {
                // création du nom du fichier
                $fileName = $slugger->slug($produit->getModele()) . uniqid() . '.' . $file3->getClientOriginalExtension();

                // Enregistrer le fichier dans le dossier public
                    $file3->move(
                    $this->getParameter("produit"),
                    $fileName
                );

                $produit->setPhoto3($fileName);
            }

            $produitRepository->save($produit, true);

            return $this->redirectToRoute('app_all_products', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new_product.html.twig', [
            'produit' => $produit,
            'formProduit' => $form->createView()
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_product', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/view_product.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/update_{id<\d+>}', name: 'app_update_product', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository,SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file1 = $form->get('photo1')->getData();

            if($file1)
            {
                // création du nom du fichier
                $fileName = $slugger->slug($produit->getModele()) . uniqid() . '.' . $file1->getClientOriginalExtenstion();

                // Enregistrer le fichier dans le dossier public
                $file1->move(
                    $this->getParameter("produit"),
                    $fileName
                );

                if($produit->getPhoto1())
                {
                    unlink($this->getParameter("produit") . "/" . $produit->getPhoto1());
                }
                $produit->setPhoto1($fileName);
            }

            $file2 = $form->get('photo2')->getData();

            if($file2)
            {
                // création du nom du fichier
                $fileName = $slugger->slug($produit->getModele()) . uniqid() . '.' . $file2->getClientOriginalExtenstion();

                // Enregistrer le fichier dans le dossier public
                $file2->move(
                    $this->getParameter("produit"),
                    $fileName
                );

                if($produit->getPhoto2())
                {
                    unlink($this->getParameter("produit") . "/" . $produit->getPhoto2());
                }

                $produit->setPhoto2($fileName);
            }

            $file3 = $form->get('photo3')->getData();

            if($file3)
            {
                // création du nom du fichier
                $fileName = $slugger->slug($produit->getModele()) . uniqid() . '.' . $file3->getClientOriginalExtenstion();

                // Enregistrer le fichier dans le dossier public
                $file3->move(
                    $this->getParameter("produit"),
                    $fileName
                );


                if($produit->getPhoto3())
                {
                    unlink($this->getParameter("produit") . "/" . $produit->getPhoto3());
                }

                $produit->setPhoto3($fileName);
            }


            $produitRepository->save($produit, true);

            return $this->redirectToRoute('app_all_products', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new_product.html.twig', [
            'produit' => $produit,
            'formProduit' => $form->createView()
        ]);
        //fichier edit = update
    }

    #[Route('/delete_{id<\d+>}', name: 'app_delete_product', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) 
        {

            if($produit->getPhoto1())
            {
                unlink($this->getParameter("produit") . "/" . $produit->getPhoto1());
            }

            if($produit->getPhoto2())
            {
                unlink($this->getParameter("produit") . "/" . $produit->getPhoto2());
            }

            if($produit->getPhoto3())
            {
                unlink($this->getParameter("produit") . "/" . $produit->getPhoto3());
            }

            $produitRepository->remove($produit, true);
        }

        return $this->redirectToRoute('app_all_products', [], Response::HTTP_SEE_OTHER);
    }
}
