<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Cart

{
	//private $session;
	private $requestStack;
	private $entityManager;

	//public function __construct(SessionInterface $session)
	public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
	{
	//$this->session = $session;
	$this->requestStack = $requestStack;
	$this->entityManager = $entityManager;
	}

    public function add($id) //ajoute les products dans le panier
    {
        $session = $this->requestStack->getSession();

        $cart = $session->get('cart'); //on récupère les informations du panier à l'aide de la session

        if (!empty($cart[$id])) {

            $cart[$id]++; //on incrémente
        } else {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);
    }


    public function get()  //affiche le panier
    {
        $session = $this->requestStack->getSession();
        return $session->get('cart');
    }


    public function remove()  //vide le panier
    {
        $session = $this->requestStack->getSession();
        return $session->remove('cart');
    }

    public function delete($id)  //supprime un produit du panier 
    {
        $session = $this->requestStack->getSession();  //je lance une session
        $cart = $session->get('cart', []);              //je recupére les infos de ma session cart
        unset($cart[$id]);                           //je supprime de mon tableau  l'element corrspondant

        return $session->set('cart', $cart);        //je redéfins la nouvelle valeur dans la session cart
    }

	// REDUIRE
	public function decrease($id)
	{
		// On récupère les informations du panier à l'aide de la session
		$cart = $this->requestStack->getSession()->get('cart', []);

		// Vrérifier si la quantité de notre produit = 1
		if ($cart[$id] > 1) {
			// Retirer une quantité (-1)
			$cart[$id]--; 
			
		} else {
			// Supprimer mon produit
			unset($cart[$id]);
		}
		
		// Je redéfini la même route que mon panier
		$this->requestStack->getSession()->set('cart',$cart); 
	}

    public function getFull()
    {
        $cartComplete = [];

        if($this->get()) {
            foreach ($this->get() as $id => $quantity) {
                // Je récupère l'ID du produit en base de données
                $product_object = $this->entityManager->getRepository(Product::class)->findOneById($id);

                // SI le produit n'existe pas
                if (!$product_object) {
                    // On le supprime du panier
                    $this->delete($id);
                    continue;
                }

                $cartComplete[] = [
                'product' => $product_object,
                'quantity' => $quantity
                ];
            } 
        }

        return $cartComplete;

    }
}