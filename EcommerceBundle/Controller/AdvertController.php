<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Advert;
use EcommerceBundle\Form\AdvertType;
use EcommerceBundle\Form\AdvertEditType;
use Symfony\Component\HttpFoundation\JsonResponse;
use CoreBundle\Entity\PostalCode;

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
     * @Route("/")
     * @Method("POST")
     * @Template("EcommerceBundle:Advert:new.html.twig")
     */
    public function createAction(Request $request)
    {

        $entity = new Advert();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            //add postal codes
            $data = $request->request->get('ecommercebundle_advert');

            $postalCodes = explode(',', $data['codes']);
            $days = $data['days'];
            $sectionCount = count($data['located']);
            
            foreach ($postalCodes as $value) {
                $postalCodeEntity = $em->getRepository('CoreBundle:PostalCode')->findOneByPostalCode($value);
                if($postalCodeEntity instanceof PostalCode){
                    $entity->addPostalCode($postalCodeEntity);
                }
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
                
                $user = $this->container->get('security.token_storage')->getToken()->getUser();  

                return $this->redirect($this->generateUrl('ecommerce_advert_show', array('id' => $entity->getId())));
                
            }else{
                $this->get('session')->getFlashBag()->add('success', 'advert.cancel');
                return $this->redirect($this->generateUrl('ecommerce_advert_index'));
            }
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
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
        $form = $this->createForm(new AdvertType($formConfig), $entity, array(
            'action' => $this->generateUrl('ecommerce_advert_create'),
            'method' => 'POST',
        ));

//        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Advert entity.
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Advert();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Advert entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Advert')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Advert entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Advert entity.
     *
     * @Route("/{id}/edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Advert')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Advert entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
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
        $form = $this->createForm(new AdvertEditType($formConfig), $entity, array(
            'action' => $this->generateUrl('ecommerce_advert_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

//        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Advert entity.
     *
     * @Route("/{id}")
     * @Method("PUT")
     * @Template("EcommerceBundle:Advert:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Advert')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Advert entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'advert.edited');
            
            return $this->redirect($this->generateUrl('ecommerce_advert_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Advert entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('EcommerceBundle:Advert')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Advert entity.');
            }
            
            //remove product purchase, transaction and invoice
            $purchases = $em->getRepository('EcommerceBundle:ProductPurchase')->findByAdvert($id);
            foreach ($purchases as $purchase) {
                $em->remove($purchase);
            }
            $em->remove($entity);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'advert.deleted');
        }

        return $this->redirect($this->generateUrl('ecommerce_advert_index'));
    }

    /**
     * Creates a form to delete a Advert entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ecommerce_advert_delete', array('id' => $id)))
            ->setMethod('DELETE')
//            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
