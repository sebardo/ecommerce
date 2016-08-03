<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Plan;
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
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new Plan();
        $form = $this->createForm('EcommerceBundle\Form\PlanType', $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

            return $this->redirectToRoute('ecommerce_plan_show', array('id' => $entity->getId()));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

     /**
     * Finds and displays a Plan entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Plan $plan)
    {
        $deleteForm = $this->createDeleteForm($plan);
        //get plan
        $paypalPlan = $this->get('checkout_manager')->getPaypalPlan($plan);
        
        return array(
            'entity' => $plan,
            'delete_form' => $deleteForm->createView(),
            'paypalPlan' => $paypalPlan
        );
    }

    /**
     * Displays a form to edit an existing Plan entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Plan $plan)
    {
        
        $deleteForm = $this->createDeleteForm($plan);
        $editForm = $this->createForm('EcommerceBundle\Form\PlanType', $plan);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($plan);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'plan.edited');
            
            return $this->redirectToRoute('ecommerce_plan_edit', array('id' => $plan->getId()));
        }

        return array(
            'entity' => $plan,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Deletes a Plan entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Plan $plan)
    {
        $form = $this->createDeleteForm($plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->remove($plan);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'plan.deleted');
        }

        return $this->redirectToRoute('ecommerce_plan_index');
    }
    
    /**
     * Creates a form to delete a Plan entity.
     *
     * @param Plan $plan The Plan entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Plan $plan)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ecommerce_plan_delete', array('id' => $plan->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
   
}
