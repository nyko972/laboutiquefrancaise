<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountAddressController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {

        $this->entityManager = $entityManager;
    }
    #[Route('/compte/adresses', name: 'app_account_address')]
    public function index(): Response
    {
        // dd($this->getUser());

        //je récupère les adresse et les valeurs 
        // dd($this->getUser()->getAddresses()->getValues());

        // Si l'utilisateur n'a pas d'adresses ALORS
        if (!$this->getUser()->getAddresses()->getValues()) {
            // On le redirige vers la page d'ajout d'adresse
            return $this->redirectToRoute('app_account_address_add');
        }

        return $this->render('account/address.html.twig', []);
    }

    #[Route('/compte/ajouter-une-adresse', name: 'app_account_address_add')]
    public function add(Request $request, Cart $cart): Response
    {
        $address = new Address();

        //je passe en paramètres a ma fonction createForm() Le type du formulaire et l'objet
        $form = $this->createForm(AddressType::class, $address);

        //ecoute la requête
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            //récupère 
            $address->setUser($this->getUser());

            // dd($address);

            //fige la data
            $this->entityManager->persist($address);

            //exécute
            $this->entityManager->flush();

            // S'il y a un produit dans le panier
            if ($cart->get()) {
                // JE redirige vers commande
                return $this->redirectToRoute('app_order');
            } else {

                return $this->redirectToRoute('app_account_address');
            }

            return $this->redirectToRoute('app_account_address');
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/compte/modifier-une-adresse/{id}', name: 'app_account_address_edit')]
    public function edit(Request $request, $id): Response
    {
        //je récupère l'adresse en base de donné
        $address = $this->entityManager->getRepository(Address::class)->findOneById($id);

        //s'il n'a aucune adresse OU que l'utilisateur ne correspond pas a celui actuellement connecté 
        if (!$address || $address->getUser() != $this->getUser()) {

            return $this->redirectToRoute('app_account_address');
        }

        //je passe en paramètres a ma fonction createForm() Le type du formulaire et l'objet
        $form = $this->createForm(AddressType::class, $address);

        //ecoute la requête
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            // dd($address)

            //exécute
            $this->entityManager->flush();

            return $this->redirectToRoute('app_account_address');
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/compte/supprimer-une-adresse/{id}', name: 'app_account_address_delete')]
    public function delete($id): Response
    {
        //je récupère l'adresse en base de donné
        $address = $this->entityManager->getRepository(Address::class)->findOneById($id);

        //s'il n'a aucune adresse ET que l'utilisateur  correspond a celui actuellement connecté 
        if ($address && $address->getUser() == $this->getUser()) {

            // supprime l'objet 'address' en base de donné
            $this->entityManager->remove($address);

            //exécute
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_account_address');
    }
}
