<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Contract;
use EcommerceBundle\Form\ContractType;
use Symfony\Component\HttpFoundation\JsonResponse;
use EcommerceBundle\Form\PlanType;
use EcommerceBundle\Entity\Plan;

/**
 * Contract controller.
 *
 * @Route("/admin/contract")
 */
class ContractController extends Controller
{

    /**
     * Lists all Contract entities.
     *
     * @Route("/")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('EcommerceBundle:Contract')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    
    /**
     * Returns a list of Brand entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Contract'));

        $response = $jsonList->get();

        return new JsonResponse($response);
    }
    
    /**
     * Returns a list of Brand entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/{id}/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function actorlistJsonAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $actor = $em->getRepository('CoreBundle:Actor')->find($id);

        if (!$actor) {
            throw $this->createNotFoundException('Unable to find Actor entity.');
        }
        
        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Contract'));
        $jsonList->setActor($actor);
        $response = $jsonList->get();

        return new JsonResponse($response);
    }
    
    /**
     * Creates a new Contract entity.
     *
     * @Route("/")
     * @Method("POST")
     * @Template("EcommerceBundle:Contract:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Contract();
        $form = $this->createCreateForm($entity);
        $planForm = $this->createForm(new PlanType(), new Plan(), array(
            'action' => $this->generateUrl('ecommerce_plan_create'),
            'method' => 'POST',
        ));
        
        $form->handleRequest($request);

        if ($form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            
            $agreement = $entity->getAgreement();
            $agreement->setContract($entity);
            $agreement->setStatus('Created');
            
            
            try {
                $em->persist($entity);
                $em->persist($entity->getAgreement());
                $em->flush();
              
            } catch (\Exception $exc) {
                $this->get('session')->getFlashBag()->add('danger', 'contract.duplicated');
            }
            
            
            $checkoutManager = $this->get('checkout_manager');
            //create agreement paypal
            $creditCard = $form->getNormData()->getAgreement()->getCreditCard();
            $cc = array(
                "number" => $creditCard['cardNo'],
                "type" => $creditCard['cardType'],
                "expire_month" => $creditCard['expirationDate']->format('m'),
                "expire_year" =>  $creditCard['expirationDate']->format('Y'),
                "cvv2" =>  $creditCard['CVV'],
                "first_name" => $creditCard['firstname'],
                "last_name" => $creditCard['lastname']
            );  
            $payPalAgreement = $checkoutManager->createPaypalAgreement($entity->getAgreement(), $cc);

            if($entity->getAgreement()->getPaymentMethod() == 'paypal'){
                $this->get('session')->getFlashBag()->add('success', 'contract.created.approval');
                $transactions = $entity->getAgreement()->getTransactions();
                return $this->redirect($this->generateUrl('ecommerce_transaction_show', array('id' => $transactions->last()->getId())));

            }else {
                $this->get('session')->getFlashBag()->add('success', 'contract.created');
            }

            return $this->redirect($this->generateUrl('ecommerce_contract_show', array('id' => $entity->getId())));

        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'planForm' => $planForm->createView(),
        );
    }

    /**
     * Creates a form to create a Contract entity.
     *
     * @param Contract $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Contract $entity)
    {
        $form = $this->createForm(new ContractType(), $entity, array(
            'action' => $this->generateUrl('ecommerce_contract_create'),
            'method' => 'POST',
        ));

//        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Contract entity.
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Contract();
        $form   = $this->createCreateForm($entity);

        $planForm = $this->createForm(new PlanType(), new Plan(), array(
            'action' => $this->generateUrl('ecommerce_plan_create'),
            'method' => 'POST',
        ));
        
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'planForm' => $planForm->createView(),
        );
    }

    /**
     * Finds and displays a Contract entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Contract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Contract entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        //get agreement
        $agreement = $entity->getAgreement();
        $paypalAgreement = $this->get('checkout_manager')->getPaypalAgreement($agreement->getPaypalId());
        
        $transactions = $this->get('checkout_manager')->searchPaypalAgreementTransactions($agreement);
                
        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'transactions' => $transactions,
            'paypalAgreement' => $paypalAgreement
        );
    }

    /**
     * Displays a form to edit an existing Contract entity.
     *
     * @Route("/{id}/edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Contract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Contract entity.');
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
    * Creates a form to edit a Contract entity.
    *
    * @param Contract $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Contract $entity)
    {
        $form = $this->createForm(new ContractType(), $entity, array(
            'action' => $this->generateUrl('ecommerce_contract_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

//        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Contract entity.
     *
     * @Route("/{id}")
     * @Method("PUT")
     * @Template("EcommerceBundle:Contract:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Contract')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Contract entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('ecommerce_contract_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Contract entity.
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
            $entity = $em->getRepository('EcommerceBundle:Contract')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Contract entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('ecommerce_contract_index'));
    }

    /**
     * Creates a form to delete a Contract entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ecommerce_contract_delete', array('id' => $id)))
            ->setMethod('DELETE')
//            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
