<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session/{reference}', name: 'app_stripe_create_session')]
    public function index(EntityManagerInterface $entityManager, Cart $cart, $reference)

    {
        //STRIPE 
        $products_for_stripe = [];
        //mettre le chemin du serveur local
        $YOUR_DOMAIN = 'http://127.0.0.1:8000//public';

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

        if (!$order) {
            new JsonResponse(['error' => 'order']);
        }
        //   dd($order->getOrderDetails()->getValues());

        // dd($order);



        //PRODUITS
        foreach ($order->getOrderDetails()->getValues() as $product) {

            $product_objet = $entityManager->getRepository(Product::class)->findOneByName($product->getProduct());
            $products_for_stripe[] = [
                'price_data' => [
                    //monnaie
                    'currency' => 'eur',
                    //prix unitaire
                    'unit_amount' => $product->getPrice(),
                    //produit base de donnée
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN . "/uploads/" . $product_objet->getIllustration()],
                    ],
                ],
                //quantité du produits 
                'quantity' => $product->getQuantity(),
            ];
        }

        //TRANSPORTEUR


        $products_for_stripe[] = [
            'price_data' => [
                //monnaie
                'currency' => 'eur',
                //prix unitaire
                'unit_amount' => $order->getCarrierPrice(),
                //produit base de donnée
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => ["https://dirigeants-entreprise.com/content/uploads/Apps-Colissimo.jpg"],
                ],
            ],

            //quantité du produits 
            'quantity' => 1,
        ];


        // dd($products_for_stripe);



        Stripe::setApiKey('sk_test_51LieLtLv8IqE4Bc33y5pBGo6yAejtL8r2dWK2dYNIWdtsoberogQoaAVJUR87Eo2P8qWvpjQbkLGvpHqoWmv8CQC002PvXPEkR');


        //method static
        $checkout_session = Session::create([
            //met l'email de l'utilisateur automatiquement dans stripe 
            'customer_email' => $this->getUser()->getEmail(),

            'payment_method_types' => ['card'],
            //ligne des éléments
            'line_items' => [
                $products_for_stripe,
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/success.html',
            'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
        ]);

        // $response = new JsonResponse(['id' => $checkout_session->id]);
        // return $response;

        //redirection vers la page de strip payment
        return $this->redirect($checkout_session->url);
    }

    // dd($products_for_stripe);
    //   dump($checkout_session->id);
    // dd($checkout_session);

}
