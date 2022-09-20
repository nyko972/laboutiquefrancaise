<?php

namespace App\Controller;

use App\Classe\Cart;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{

    #[Route('/mon-panier', name: 'app_cart')]  //chemin vers le panier
    public function index(cart $cart): Response
    { 

        return $this->render('cart/index.html.twig', [
            'cart' => $cart->getFull()
        ]);
    }


    #[Route('/cart/add/{id}', name: 'add_to_cart')]
    public function add(Cart $cart, $id): Response   //ajoute les id de products au panier
    {
        //dd($id);
        $cart->add($id);
        return $this->redirectToRoute('app_cart');
    }


    #[Route('/cart/remove', name: 'remove_my_cart')]
    public function remove(Cart $cart): Response  //supprime le panier
    {
        $cart->remove();

        //dd($cart);
        return $this->redirectToRoute('products');   //redirige vers products
    }

    //pour supprimer un produit
    #[Route('/cart/delete/{id}', name: 'delete_to_cart')] //je passe en parametre Ã  mon url l'id du produit que l'on doit supprimer
    public function delete(Cart $cart, $id): Response  //efface le panier
    {
        $cart->delete($id);

        //dd($cart);
        return $this->redirectToRoute('app_cart');   //redirige vers panier
    }

    // REDUIRE
    #[Route('/cart/descrease/{id}', name: 'decrease_to_cart')]
    public function decrease(Cart $cart, $id)
    {
        // je définis decrease dans l'entité Cart
        $cart->decrease($id);

    
        return $this->redirectToRoute('app_cart');

    }
}