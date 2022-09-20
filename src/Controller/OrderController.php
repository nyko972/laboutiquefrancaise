<?php

namespace App\Controller;

use DateTime;

use App\Classe\Cart;
use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    //protégé les donné de ma commande grace au private
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {

        $this->entityManager = $entityManager;
    }


    #[Route('/commande', name: 'app_order')]
    public function index(Cart $cart): Response
    {

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        return $this->render('order/index.html.twig', [

            'form' => $form->createView(),
            'cart' => $cart->getFull()

        ]);
    }

    #[Route('/commande/recapitulatif', name: 'app_order_recap', methods: "POST")]
    public function add(Cart $cart, Request $request): Response
    {


        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        // Ecoute la requête
        $form->handleRequest($request);

        // SI le formulaire est soumis ET le formulaire est valide ALORS
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData()); 

            //objet de la classe dateTime
            $date = new DateTime();

            //je récupère le champ carriere dans le formulaire, pour récupéré la donner ensuite
            $carriers = $form->get('carriers')->getData();
            //    dd($carriers);


            $delivery = $form->get('addresses')->getData();

            $delivery_content = $delivery->getFirstname() . ' ' . $delivery->getLastname();
            $delivery_content .= '<br>' . $delivery->getPhone();

            if ($delivery->getCompany()) {
                $delivery_content .= '<br>' . $delivery->getCompany();
            }

            $delivery_content .= '<br>' . $delivery->getAddress();
            $delivery_content .= '<br>' . $delivery->getPostal() . ' ' . $delivery->getCity();
            $delivery_content .= '<br>' . $delivery->getCountry();

            //    dd($delivery_content);




            //  Enregistrer ma commande  (entityOrder() )
            $order = new Order();

            $reference = $date->format('dmY') . '-' . uniqid();
            $order->setReference($reference);




            $order->setUser($this->getUser());

            // Je passe en paramètre date dans le setCreated pour définir la date de quand il a été créer 
            $order->setCreatedAt($date);


            $order->setCarrierName($carriers->getName());

            //transporteur prix 
            $order->setCarrierPrice($carriers->getPrice());

            //commande
            $order->setDelivery($delivery_content);

            //si c'est payer
            $order->setIsPaid(0);

            //fige la data( prépare a recevoir les données relative a la commande)
            $this->entityManager->persist($order);




            //pour chaque produit que j'ai dans mon panier
            foreach ($cart->getFull() as $product) {
                //orderDetails lié a order

                //on crée une nouvelle instance OrderDetails 

                $orderDetails = new OrderDetails();

                $orderDetails->setMyOrder($order);

                $orderDetails->setProduct($product['product']->getName());

                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);

                // dd($product);

                $this->entityManager->persist($orderDetails);
            }



            //execute
            $this->entityManager->flush();


            //  enregistrer mes produits OrderDetail

            return $this->render('order/add.html.twig', [

                'form' => $form->createView(),
                'cart' => $cart->getFull(),
                'carrier' => $carriers,
                'delivery' => $delivery_content,
                'reference' => $order->getReference()

            ]);
        }

        return $this->redirectToRoute('app_cart');
    }
}
