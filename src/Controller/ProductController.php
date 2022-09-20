<?php

namespace App\Controller;

use App\Classe\Search;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) //injection indépendance
    {

        //récupère données dans base de données 
        $this->entityManager = $entityManager;
    }


    #[Route('/nos-produits', name: 'app_products')]
    public function index(Request $request): Response
    {
        //Je récupère tout les produits dans la base de donné à l'aide de Doctrine qui va se charger de récupéré mon repository (doctrine)

        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);

        $products = $this->entityManager->getRepository(Product::class)->findAll();

        // Ecoute la requête
        $form->handleRequest($request);

        // Recupère mes produits en fonction de mes recherches
            // Nouvelle requetes

            // findWithSearch + création de la fonction findWithSearch dans le repository

       if ($form->isSubmitted() && $form->isValid()) {
            $products = $this->entityManager->getRepository(Product::class)->findWithSearch($search);
            // dd($search);
        } else {
            $products = $this->entityManager->getRepository(Product::class)->findAll();
        }


        return $this->render('product/index.html.twig', [
            'products'=>$products,
            'form' => $form->createView()
        ]);
    }

    // On passe le slug en paramètre dans l'url
    #[Route('/produit/{slug}', name: 'app_product')]
    public function show($slug): Response
    {
        // dd($slug);
        // On recherche en base de donnée un produit associer à son slug.
        //Je récupère tout les produits dans la base de donné à l'aide de Doctrine qui va se charger de récupéré mon repository (doctrine)

        $product = $this->entityManager->getRepository(Product::class)->findOneBySlug($slug);

        // Partie sécurité
        if (!$product){
            return $this->redirectToRoute('app_products');
        }

        return $this->render('product/show.html.twig', [
            'product'=>$product
        ]);
    }
}