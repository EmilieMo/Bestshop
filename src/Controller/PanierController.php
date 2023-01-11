<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
#[Route('/panier', name: 'app_panier')]
    // public function index(): Response
    // {
    //     return $this->render('panier/index.html.twig', [
    //         'controller_name' => 'PanierController',
    //     ]);
    // }

    public function index(SessionInterface $session, ProduitRepository $produitRepository)
    {
        $panier = $session->get("panier", []);
        dump($panier);
        // On "fabrique" les données
        $dataPanier = [];
        $total = 0;

        foreach($panier as $id => $produit){
            $produit = $produitRepository->find($id);
            $dataPanier[] = [
                "produit" => $produit,
            ];
            if(empty($produit->getRemise())){
                $total += $produit->getPrix();
            }
            else{
                $total += $produit->getRemise();
            }
        }

        return $this->render('panier/index.html.twig', compact("dataPanier", "total"));
    }

    #[Route('/add/{id}', name: 'app_add_panier')]
    public function add(Produit $produit, SessionInterface $session)
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $produit->getId();

        if(!empty($panier[$id])){
            $panier[$id]++;
        }else{
            $panier[$id] = 1;
        }

        // On sauvegarde dans la session
        $session->set("panier", $panier);

        return $this->redirectToRoute("app_panier");
    }

    #[Route('/modifier/{id}', name: 'app_modifier_panier')]
    public function remove(Produit $produit, SessionInterface $session)
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $produit->getId();

        if(!empty($panier[$id])){
            if($panier[$id] > 1){
                $panier[$id]--;
            }else{
                unset($panier[$id]);
            }
        }

        // On sauvegarde dans la session
        $session->set("panier", $panier);

        return $this->redirectToRoute("app_panier");
    }

    #[Route('/delete/{id<\d+>}', name: 'app_delete_panier')]
    public function delete(Produit $produit, SessionInterface $session)
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $produit->getId();

        if(!empty($panier[$id])){
            unset($panier[$id]);
        }

        // On sauvegarde dans la session
        $session->set("panier", $panier);

        return $this->redirectToRoute("app_panier");
    }

    #[Route('/delete/all', name: 'app_delete_all_panier')]
    public function deleteAll(SessionInterface $session)
    {
        $session->remove("panier");

        return $this->redirectToRoute("app_panier");
    }

    #[Route('/payer', name: 'app_payer')]
    public function payer(SessionInterface $session, CommandeRepository $commandeRepository, ProduitRepository $produitRepository, DetailCommandeRepository $detailCommandeRepository)
    {

        $panier = $session->get("panier", []);

        $total = 0;
        foreach ($panier as $id) {
            $produit = $produitRepository->find($id);
            $total += $produit->getPrix();
        }

        $commande = new Commande;
        $commande->setUser($this->getUser());
        $commande->setPrix($total);
        $commande->setDateCommande(new \DateTimeImmutable('now'));


        $commandeRepository->save($commande, true);

        foreach($panier as $k => $v)
        {
            $produit = $produitRepository->find($k);

            $detailCommande = new DetailCommande;
            $detailCommande->setCommande($commande);
            $detailCommande->setProduit($produit);
            $detailCommande->setPrix($produit->getPrix());
            $detailCommandeRepository->save($detailCommande, true);

        }
        $session->set("panier", []);

        $this->addFlash("success", "📦 Votre commande est validée! 'Bestshop, plus portable que jamais'🚚);

        return $this->redirectToRoute("app_profil");
    }

}
