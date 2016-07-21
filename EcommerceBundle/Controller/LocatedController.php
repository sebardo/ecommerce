<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Located;
use EcommerceBundle\Form\LocatedType;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Located controller.
 *
 * @Route("/admin/located")
 */
class LocatedController extends Controller
{

    /**
     * Lists all Located entities.
     *
     * @Route("/", )
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('EcommerceBundle:Located')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    
    /**
     * Returns a list of Located entities in JSON format.
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
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Located'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }
    
    /**
     * Creates a new Located entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new Located();
        $form = $this->createForm('EcommerceBundle\Form\LocatedType', $entity, array(
            'action' => $this->generateUrl('ecommerce_located_new'),
            'method' => 'POST',
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'located.created');

            return $this->redirectToRoute('ecommerce_located_show', array('id' => $entity->getId()));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Finds and displays a Located entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Located $located)
    {
        $deleteForm = $this->createDeleteForm($located);

        return array(
            'entity' => $located,
            'delete_form' => $deleteForm->createView(),
        );
    }
    
     /**
     * Displays a form to edit an existing Located entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Located $located)
    {
        
        $deleteForm = $this->createDeleteForm($located);
        $editForm = $this->createForm('EcommerceBundle\Form\LocatedType', $located, array(
            'action' => $this->generateUrl('ecommerce_located_edit', array('id' => $located->getId())),
            'method' => 'POST',
        ));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $em->persist($located);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'located.edited');
            
            return $this->redirectToRoute('ecommerce_located_show', array('id' => $located->getId()));
        }

        return array(
            'entity' => $located,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Located entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Located $located)
    {
        $form = $this->createDeleteForm($located);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->remove($located);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'located.deleted');
        }

        return $this->redirectToRoute('ecommerce_located_index');
    }

   /**
     * Located a form to delete a Located entity.
     *
     * @param Located $located The Located entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Located $located)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ecommerce_located_delete', array('id' => $located->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
