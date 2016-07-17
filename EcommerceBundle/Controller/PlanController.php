<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Plan;
use EcommerceBundle\Form\PlanType;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Plan controller.
 *
 * @Route("/admin/plan")
 */
class PlanController extends Controller
{

    /**
     * Lists all Plan entities.
     *
     * @Route("/")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('EcommerceBundle:Plan')->findAll();

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
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Plan'));

        $response = $jsonList->get();

        return new JsonResponse($response);
    }
    
    /**
     * Creates a new Plan entity.
     *
     * @Route("/")
     * @Method("POST")
     * @Template("EcommerceBundle:Plan:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Plan();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);

            $checkoutManager = $this->get('checkout_manager');
            $checkoutManager->createPaypalPlan($entity);
            $checkoutManager->activePaypalPlan($entity);
            
            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $entity->getId(), 
                            'name' => $entity->getName()
                        ));
            }
            
            $this->get('session')->getFlashBag()->add('success', 'plan.created');
            
            return $this->redirect($this->generateUrl('ecommerce_plan_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Plan entity.
     *
     * @param Plan $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Plan $entity)
    {
        $form = $this->createForm(new PlanType(), $entity, array(
            'action' => $this->generateUrl('ecommerce_plan_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Displays a form to create a new Plan entity.
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Plan();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Plan entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Plan')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Plan entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        //get plan
        $paypalPlan = $this->get('checkout_manager')->getPaypalPlan($entity);
                
        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'paypalPlan' => $paypalPlan
        );
    }

    /**
     * Displays a form to edit an existing Plan entity.
     *
     * @Route("/{id}/edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Plan')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Plan entity.');
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
    * Creates a form to edit a Plan entity.
    *
    * @param Plan $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Plan $entity)
    {
        $form = $this->createForm(new PlanType(), $entity, array(
            'action' => $this->generateUrl('ecommerce_plan_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

//        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Plan entity.
     *
     * @Route("/{id}")
     * @Method("PUT")
     * @Template("EcommerceBundle:Plan:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EcommerceBundle:Plan')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Plan entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('ecommerce_plan_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Plan entity.
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
            $entity = $em->getRepository('EcommerceBundle:Plan')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Plan entity.');
            }

            $em->remove($entity);
            $em->flush();
            
             $this->get('session')->getFlashBag()->add('success', 'plan.deleted');
        }

        return $this->redirect($this->generateUrl('ecommerce_plan_index'));
    }

    /**
     * Creates a form to delete a Plan entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ecommerce_plan_delete', array('id' => $id)))
            ->setMethod('DELETE')
//            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
