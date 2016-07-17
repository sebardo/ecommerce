<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Agreement;
use EcommerceBundle\Form\AgreementType;

/**
 * Agreement controller.
 *
 * @Route("/admin/agreement")
 */
class AgreementController extends Controller
{

    /**
     * Lists all Agreement entities.
     *
     * @Route("/", name="agreement")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('EcommerceBundle:Agreement')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Agreement entity.
     *
     * @Route("/", name="agreement_create")
     * @Method("POST")
     * @Template("EcommerceBundle:Agreement:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Agreement();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('agreement_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Agreement entity.
     *
     * @param Agreement $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Agreement $entity)
    {
        $form = $this->createForm(new AgreementType(), $entity, array(
            'action' => $this->generateUrl('agreement_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Agreement entity.
     *
     * @Route("/new", name="agreement_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Agreement();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Agreement entity.
     *
     * @Route("/{id}", name="agreement_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Agreement')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Agreement entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Agreement entity.
     *
     * @Route("/{id}/edit", name="agreement_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Agreement')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Agreement entity.');
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
    * Creates a form to edit a Agreement entity.
    *
    * @param Agreement $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Agreement $entity)
    {
        $form = $this->createForm(new AgreementType(), $entity, array(
            'action' => $this->generateUrl('agreement_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Agreement entity.
     *
     * @Route("/{id}", name="agreement_update")
     * @Method("PUT")
     * @Template("EcommerceBundle:Agreement:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Agreement')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Agreement entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('agreement_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Agreement entity.
     *
     * @Route("/{id}", name="agreement_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('EcommerceBundle:Agreement')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Agreement entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('agreement'));
    }

    /**
     * Creates a form to delete a Agreement entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('agreement_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    /**
     * Deletes a PayPal Agreement.
     *
     * @Route("/{id}/suspend")
     * @Method("GET")
     */
    public function suspendAction($id) 
    {
        $em = $this->getDoctrine()->getManager();
        $checkoutManager = $this->get('checkout_manager');
        $agreement = $em->getRepository('EcommerceBundle:Agreement')->findOneByPaypalId($id);
       
        $checkoutManager->suspendPaypalAgreement($agreement);
        $answer = $checkoutManager->getPaypalAgreement($agreement->getPaypalId());
           
        if($answer['state'] == 'Suspended'){
            $agreement->setStatus($answer['state']);
            $em->flush($agreement);
        }
        $this->get('session')->getFlashBag()->add('warning', 'agreement.suspend.message');

        return $this->redirect($this->generateUrl('ecommerce_contract_show', array('id' => $agreement->getContract()->getId())));
            
    }
    
    /**
     * Re active an PayPal Agreement.
     *
     * @Route("/{id}/re-active")
     * @Method("GET")
     */
    public function reActiveAction($id) {
        $em = $this->getDoctrine()->getManager();
        $checkoutManager = $this->get('checkout_manager');
        $agreement = $em->getRepository('EcommerceBundle:Agreement')->findOneByPaypalId($id);
        
        $checkoutManager->reactivePaypalAgreement($agreement);
        $answer = $checkoutManager->getPaypalAgreement($agreement->getPaypalId());
        
        if($answer['state'] == 'Active'){
            $agreement->setStatus($answer['state']);
            $em->flush($agreement);
        }
        $this->get('session')->getFlashBag()->add('success', 'agreement.reactive.message');

        return $this->redirect($this->generateUrl('ecommerce_contract_show', array('id' => $agreement->getContract()->getId())));
    }
    
    /**
     * Cancel an PayPal Agreement.
     *
     * @Route("/{id}/cancel")
     * @Method("GET")
     */
    public function cancelAction($id) {
        $em = $this->getDoctrine()->getManager();
        $checkoutManager = $this->get('checkout_manager');
        $agreement = $em->getRepository('EcommerceBundle:Agreement')->findOneByPaypalId($id);
        
        $checkoutManager->cancelPaypalAgreement($agreement);
        $answer = $checkoutManager->getPaypalAgreement($agreement->getPaypalId());
        
        if($answer['state'] == 'Cancelled'){
//            $transactions = $agreement->getTransactions();
//            foreach ($transactions as $transaction) {
//                $em->remove($transaction);
//            }
//            $em->remove($agreement);
             $agreement->setStatus('Cancelled');
            $em->flush();
        }
        $this->get('session')->getFlashBag()->add('danger', 'agreement.cancelled.message');

        return $this->redirect($this->generateUrl('ecommerce_contract_show', array('id' => $agreement->getContract()->getId())));
        
    }
    
    /**
     * Deletes a PayPal Agreement.
     *
     * @Route("/{id}/set-outstanding")
     * @Method("POST")
     */
    public function setOutstandingAction(Request $request, $id) 
    {
        $em = $this->getDoctrine()->getManager();
        $checkoutManager = $this->get('checkout_manager');
        $agreement = $em->getRepository('EcommerceBundle:Agreement')->findOneByPaypalId($id);
       
        $amount = $request->get('amount');
        $outstandig = $checkoutManager->setOutstandingPaypalAgreement($agreement, $amount);
        $answer = $checkoutManager->getPaypalAgreement($agreement->getPaypalId());
        
        if($outstandig['status'] == 'error' ){
            $error = json_decode($outstandig['error'], true);
            $this->get('session')->getFlashBag()->add('warning', $error['message']);

        }else{
             if(isset($answer['agreement_details']['outstanding_balance']['value'])){
                $agreement->setOutstandingAmount($answer['agreement_details']['outstanding_balance']['value']);
                $em->flush($agreement);
            }
            $this->get('session')->getFlashBag()->add('warning', 'agreement.setoutstanding.message');

        }
       
        return $this->redirect($this->generateUrl('ecommerce_contract_show', array('id' => $agreement->getContract()->getId())));
            
    }
    
    /**
     * Deletes a PayPal Agreement.
     *
     * @Route("/{id}/bill-outstanding")
     * @Method("POST")
     */
    public function billOutstandingAction(Request $request, $id) 
    {
        $em = $this->getDoctrine()->getManager();
        $checkoutManager = $this->get('checkout_manager');
        $agreement = $em->getRepository('EcommerceBundle:Agreement')->findOneByPaypalId($id);
       
        $amount = $request->get('amount');
        $outstandig = $checkoutManager->billOutstandingPaypalAgreement($agreement, $amount);
        $answer = $checkoutManager->getPaypalAgreement($agreement->getPaypalId());
           
        if($outstandig['status'] == 'error' ){
            $error = json_decode($outstandig['error'], true);
            $this->get('session')->getFlashBag()->add('warning', $error['message']);
        }else{
            if(isset($answer['agreement_details']['outstanding_balance']['value'])){
                $agreement->setOutstandingAmount($answer['agreement_details']['outstanding_balance']['value']);
                $em->flush($agreement);
            }
            $this->get('session')->getFlashBag()->add('warning', 'agreement.billoutstanding.message');

        }
        
       
        return $this->redirect($this->generateUrl('ecommerce_contract_show', array('id' => $agreement->getContract()->getId())));
            
    }
    
}
