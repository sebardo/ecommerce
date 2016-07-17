<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Advert;
use EcommerceBundle\Form\AdvertFrontType;
use EcommerceBundle\Form\AdvertEditType;
use CoreBundle\Entity\PostalCode;
use CoreBundle\Entity\Actor;


class AdvertFrontController extends Controller
{

    /**
     * @Route("/publicidad")
     * @Template("EcommerceBundle:AdvertFront:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $entity = new Advert();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
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
        $form = $this->createForm(new AdvertFrontType(), $entity, array(
            'action' => $this->generateUrl('ecommerce_advertfront_create'),
            'method' => 'POST',
        ));

        return $form;
    }
   
    /**
     * Creates a new Advert entity.
     *
     * @Route("/publicidad-create")
     * @Method("POST")
     * @Template("EcommerceBundle:AdvertFront:index.html.twig")
     */
    public function createAction(Request $request)
    {

        $entity = new Advert();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        
       $formRegistration = $this->createForm(new RegistrationBrandType(), new RegistrationBrand());
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            //add postal codes
            $data = $request->request->get('advertfront');

            $postalCodes = explode(',', $data['codes']);
            $days = $data['days'];
            $sectionCount = count($data['located']);
            
            foreach ($postalCodes as $value) {
                $postalCodeEntity = $em->getRepository('CoreBundle:PostalCode')->findOneByPostalCode($value);
                if($postalCodeEntity instanceof PostalCode){
                    $entity->addPostalCode($postalCodeEntity);
                }
            }
            $user = $this->container->get('security.context')->getToken()->getUser();
            if($user instanceof Actor){
                $entity->setActor($user);
            }
            
            $em->persist($entity);
            $em->flush();
            
            
            //calculate total
            $params = $this->getParameter('core');
            $unitPrice = $params['advert_unit_price'];
            $quantity = count($postalCodes);
            $discount = 0;
            $subtotal = (($unitPrice * $quantity* $days) * $sectionCount ) - $discount;
            $totalPrice = ($subtotal * 0.21) + $subtotal;
             
            $creditCard = $form->getNormData()->getCreditCard();
            //proccess sale
            $checkoutManager = $this->get('checkout_manager');
            $transaction = $checkoutManager->createAdvertTransactionFront(
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
                //return $this->redirect($answer->redirectUrl);
                
                if($this->get('security.context')->getToken()->getUser() instanceof Actor){
                    return $this->redirect($this->generateUrl('core_profile_index', array('adverts' => true )));
                }
            }else{
                die('invalid');
            }
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'form_reg' => $formRegistration->createView()
        );
    }
    
    /**
     * Finds and displays a Advert entity.
     *
     * @Route("/publicidad/{id}")
     * @Method("GET")
     * @Template("EcommerceBundle:AdvertFront:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Advert')->find($id);
        $purchase = $em->getRepository('EcommerceBundle:ProductPurchase')->findOneByAdvert($entity);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Advert entity.');
        }

        $form = $this->createEditForm($entity);
        
        return array(
            'entity'      => $entity,
            'purchase'   => $purchase,
            'form' => $form->createView(),
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
        $user = $this->container->get('security.context')->getToken()->getUser();  
        if ($user->isGranted('ROLE_ADMIN') || $user->isGranted('ROLE_SUPER_ADMIN')) {
            $formConfig['admin'] = true;
        }
        $form = $this->createForm(new AdvertEditType($formConfig), $entity, array(
            'action' => $this->generateUrl('ecommerce_advertfront_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

//        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    
    /**
     * Edits an existing Advert entity.
     *
     * @Route("/publicidad/{id}")
     * @Method("PUT")
     * @Template("EcommerceBundle:AdvertFront:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Advert')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Advert entity.');
        }

//        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'advert.updated');
            
            return $this->redirect($this->generateUrl('coreprofile_index', array('adverts' => true)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
        );
    }
}
