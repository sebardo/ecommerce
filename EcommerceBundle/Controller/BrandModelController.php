<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\BrandModel;
use EcommerceBundle\Entity\Brand;

/**
 * BrandModel controller.
 *
 * @Route("/admin/models")
 */
class BrandModelController extends Controller
{
    /**
     * Lists all BrandModel entities.
     *
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("EcommerceBundle:BrandModel:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns a list of BrandModel entities in JSON format.
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
        $jsonList->setRepository($em->getRepository('EcommerceBundle:BrandModel'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new BrandModel entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new BrandModel();
        $form = $this->createForm('EcommerceBundle\Form\BrandModelType', $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'model.created');

            return $this->redirectToRoute('ecommerce_brandmodel_show', array('id' => $entity->getId()));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Finds and displays a BrandModel entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(BrandModel $brandModel)
    {
        $deleteForm = $this->createDeleteForm($brandModel);

        return array(
            'entity' => $brandModel,
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
    public function editAction(Request $request, BrandModel $brandModel)
    {
        
        $deleteForm = $this->createDeleteForm($brandModel);
        $editForm = $this->createForm('EcommerceBundle\Form\BrandModelType', $brandModel);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            if($brandModel->getRemoveImage()){
                $brandModel->setImage(null);
            }
            
            $em->persist($brandModel);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'model.edited');
            
            return $this->redirectToRoute('ecommerce_brandmodel_show', array('id' => $brandModel->getId()));
        }

        return array(
            'entity' => $brandModel,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Deletes a BrandModel entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, BrandModel $brandModel)
    {
        $form = $this->createDeleteForm($brandModel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->remove($brandModel);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'model.deleted');
        }

        return $this->redirectToRoute('ecommerce_brandmodel_index');
    }

   /**
     * Creates a form to delete a BrandModel entity.
     *
     * @param BrandModel $brandModel The BrandModel entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(BrandModel $brandModel)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ecommerce_brandmodel_delete', array('id' => $brandModel->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     * Returns a list of BrandModel entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/model/{id}", requirements={ "_format" = "json" }, defaults={ "_format" = "json", "id" = "" })
     * @Method("GET")
     */
    public function modelJsonAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $brand = $em->getRepository('EcommerceBundle:Brand')->find($id);

        if (!$brand instanceof Brand){
            throw $this->createNotFoundException('Unable to find Brand entity.');
        }
            
        $entities = $em->getRepository('EcommerceBundle:BrandModel')->findByBrand($brand);

        $returnValues = array();
        foreach ($entities as $entity) {
            $returnValues[$entity->getId()] =  $entity->getName() ;
        }
        return new JsonResponse($returnValues);
    }
}
