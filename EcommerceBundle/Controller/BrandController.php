<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Brand;

/**
 * Brand controller.
 *
 * @Route("/admin/brands")
 */
class BrandController extends Controller
{
    /**
     * Lists all Brand entities.
     *
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("EcommerceBundle:Brand:index.html.twig")
     */
    public function indexAction()
    {
        return array();
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
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Brand'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new Brand entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new Brand();
        $form = $this->createForm('EcommerceBundle\Form\BrandType', $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'brand.created');

            return $this->redirectToRoute('ecommerce_brand_show', array('id' => $entity->getId()));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Brand entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Brand $brand)
    {
        $deleteForm = $this->createDeleteForm($brand);

        return array(
            'entity' => $brand,
            'delete_form' => $deleteForm->createView(),
        );
    }

     /**
     * Displays a form to edit an existing Brand entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Brand $brand)
    {
        
        $deleteForm = $this->createDeleteForm($brand);
        $editForm = $this->createForm('EcommerceBundle\Form\BrandType', $brand);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            if($brand->getRemoveImage()){
                $brand->setImage(null);
            }
            
            $em->persist($brand);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'brand.edited');
            
            return $this->redirectToRoute('ecommerce_brand_show', array('id' => $brand->getId()));
        }

        return array(
            'entity' => $brand,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Deletes a Brand entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Brand $brand)
    {
        $form = $this->createDeleteForm($brand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->remove($brand);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'brand.deleted');
        }

        return $this->redirectToRoute('ecommerce_brand_index');
    }

   /**
     * Creates a form to delete a Brand entity.
     *
     * @param Brand $brand The Brand entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Brand $brand)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ecommerce_brand_delete', array('id' => $brand->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
