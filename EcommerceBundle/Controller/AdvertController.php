<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use EcommerceBundle\Entity\Advert;

/**
 * Advert controller.
 *
 * @Route("/admin/advert")
 */
class AdvertController extends Controller
{

    /**
     * Lists all Advert entities.
     *
     * @Route("/")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('EcommerceBundle:Advert')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    
    /**
     * Returns a list of Advert entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Advert'));

        $response = $jsonList->get();

        return new JsonResponse($response);
    }
    
    /**
     * Creates a new Advert entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new Advert();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            //add postal codes
            $data = $request->request->get('advert');
            $days = $data['days'];
            $sectionCount = count($data['located']);
            $em->persist($entity);
            $em->flush();
            
            //calculate total
            $unitPrice = $this->container->getParameter('ecommerce.advert_unit_price');
            $quantity = 1;
            $discount = 0;
            $subtotal = (($unitPrice * $quantity* $days) * $sectionCount ) - $discount;
            $totalPrice = ($subtotal * 0.21) + $subtotal;
            
            $creditCard = $form->getNormData()->getCreditCard();
            //proccess sale
            $checkoutManager = $this->get('checkout_manager');
            $transaction = $checkoutManager->createAdvertTransaction(
                    $entity,
                    $unitPrice,
                    $quantity, 
                    $discount, 
                    $subtotal, 
                    $totalPrice
                    );
            $date =  $creditCard['expirationDate'];
            $answer = $checkoutManager->processPaypalSaleAdvert($transaction, null, array(
                "number" => $creditCard['cardNo'],
                "type" => $creditCard['cardType'],
                "expire_month" =>  $date->format('m'),
                "expire_year" =>  $date->format('Y'),
                "cvv2" =>  $creditCard['CVV'],
                "first_name" =>  $creditCard['firstname'],
                "last_name" =>  $creditCard['lastname']
            ));

            if($answer->redirectUrl == '/response-ok'){
                $this->get('session')->getFlashBag()->add('success', 'advert.created');
                return $this->redirect($this->generateUrl('ecommerce_advert_show', array('id' => $entity->getId())));
            }else{
                $this->get('session')->getFlashBag()->add('success', 'advert.cancel');
                return $this->redirect($this->generateUrl('ecommerce_advert_index'));
            }
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Creates a form to create a Advert entity.
     *
     * @param Advert $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Advert $entity)
    {
        $formConfig = array();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();  
        if ($user->isGranted('ROLE_USER')) {
            $formConfig['actor'] = $user;
        }
        $form = $this->createForm('EcommerceBundle\Form\AdvertType', $entity, array_merge($formConfig, array(
            'action' => $this->generateUrl('ecommerce_advert_new'),
            'method' => 'POST',
        )));

        return $form;
    }

    /**
     * Finds and displays a Advert entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Advert $advert)
    {
        $deleteForm = $this->createDeleteForm($advert);

        return array(
            'entity' => $advert,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Advert entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Advert $advert)
    {
        
        $deleteForm = $this->createDeleteForm($advert);
        $editForm = $this->createEditForm($advert);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            if($advert->getRemoveImage()){
                $advert->setImage(null);
            }
            
            $em->persist($advert);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'advert.edited');
            
            return $this->redirectToRoute('ecommerce_advert_edit', array('id' => $advert->getId()));
        }

        return array(
            'entity' => $advert,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
     

    /**
    * Creates a form to edit a Advert entity.
    *
    * @param Advert $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Advert $entity)
    {
        
        $formConfig = array();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();  
        if ($user->isGranted('ROLE_ADMIN') || $user->isGranted('ROLE_SUPER_ADMIN')) {
            $formConfig['admin'] = true;
        }
        $form = $this->createForm('EcommerceBundle\Form\AdvertEditType', $entity, array_merge($formConfig, array(
            'action' => $this->generateUrl('ecommerce_advert_edit', array('id' => $entity->getId())),
            'method' => 'POST',
        )));
        
        return $form;
    }
    
    /**
     * Deletes a Category entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Advert $advert)
    {
        $form = $this->createDeleteForm($advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            //remove product purchase, transaction and invoice
            $purchases = $em->getRepository('EcommerceBundle:ProductPurchase')->findByAdvert($advert);
            foreach ($purchases as $purchase) {
                $em->remove($purchase);
            }
            $em->remove($advert);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'advert.deleted');
        }

        return $this->redirectToRoute('ecommerce_advert_index');
    }

   /**
     * Creates a form to delete a Advert entity.
     *
     * @param Advert $advert The Advert entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Advert $advert)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ecommerce_advert_delete', array('id' => $advert->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
   
}
