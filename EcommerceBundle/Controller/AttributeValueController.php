<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Attribute;
use EcommerceBundle\Entity\AttributeValue;
use EcommerceBundle\Form\AttributeValueType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * AttributeValue controller.
 *
 * @Route("/admin/attributes/{attributeId}/values")
 */
class AttributeValueController extends Controller
{
    /**
     * Lists all AttributeValue entities.
     *
     * @param int $attributeId The attribute id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("EcommerceBundle:AttributeValue:index.html.twig")
     */
    public function indexAction($attributeId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Attribute $attribute */
        $attribute = $em->getRepository('EcommerceBundle:Attribute')->find($attributeId);

        if (!$attribute) {
            throw $this->createNotFoundException('Unable to find Attribute entity.');
        }

        return array(
            'attribute'    => $attribute,
        );
    }

    /**
     * Returns a list of AttributeValue entities in JSON format.
     *
     * @param int $attributeId The attribute id
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction($attributeId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('EcommerceBundle:AttributeValue'));
        $jsonList->setEntityId($attributeId);

        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new AttributeValue entity.
     *
     * @param Request $request     The request
     * @param int     $attributeId The attribute id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/")
     * @Method("POST")
     * @Template("EcommerceBundle:AttributeValue:new.html.twig")
     */
    public function createAction(Request $request, $attributeId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Attribute $attribute */
        $attribute = $em->getRepository('EcommerceBundle:Attribute')->find($attributeId);

        if (!$attribute) {
            throw $this->createNotFoundException('Unable to find Attribute entity.');
        }

        $entity  = new AttributeValue();
        $form = $this->createForm(new AttributeValueType(), $entity);
        $entity->setAttribute($attribute);

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'value.created');

            return $this->redirect($this->generateUrl('ecommerce_attributevalue_show', array('attributeId' => $attributeId, 'id' => $entity->getId())));
        }

        return array(
            'entity'    => $entity,
            'attribute' => $attribute,
            'form'      => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new AttributeValue entity.
     *
     * @param int $attributeId The attribute id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($attributeId)
    {
        $em = $this->getDoctrine()->getManager();

       
        /** @var Attribute $attribute */
        $attribute = $em->getRepository('EcommerceBundle:Attribute')->find($attributeId);

        if (!$attribute) {
            throw $this->createNotFoundException('Unable to find Attribute entity.');
        }

        $entity = new AttributeValue();
        $form   = $this->createForm(new AttributeValueType(), $entity);

        return array(
            'entity'      => $entity,
            'attribute'   => $attribute,
            'form'        => $form->createView(),
        );
    }

    /**
     * Finds and displays a AttributeValue entity.
     *
     * @param int $attributeId The attribute id
     * @param int $id          The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction($attributeId, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Attribute $attribute */
        $attribute = $em->getRepository('EcommerceBundle:Attribute')->find($attributeId);

        if (!$attribute) {
            throw $this->createNotFoundException('Unable to find Attribute entity.');
        }

        /** @var AttributeValue $entity */
        $entity = $em->getRepository('EcommerceBundle:AttributeValue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AttributeValue entity.');
        }

        $deleteForm = $this->createDeleteForm($attributeId, $id);

        return array(
            'entity'      => $entity,
            'attribute'   => $attribute,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing AttributeValue entity.
     *
     * @param int $attributeId The attribute id
     * @param int $id          The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/{id}/edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($attributeId, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Attribute $attribute */
        $attribute = $em->getRepository('EcommerceBundle:Attribute')->find($attributeId);

        if (!$attribute) {
            throw $this->createNotFoundException('Unable to find Attribute entity.');
        }

        /** @var AttributeValue $entity */
        $entity = $em->getRepository('EcommerceBundle:AttributeValue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AttributeValue entity.');
        }

        $editForm = $this->createForm(new AttributeValueType(), $entity);
        $deleteForm = $this->createDeleteForm($attributeId, $id);

        return array(
            'entity'      => $entity,
            'attribute'   => $attribute,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing AttributeValue entity.
     *
     * @param Request $request     The request
     * @param int     $attributeId The attribute id
     * @param int     $id          The entity id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/{id}")
     * @Method("PUT")
     * @Template("EcommerceBundle:Attribute:edit.html.twig")
     */
    public function updateAction(Request $request, $attributeId, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Attribute $attribute */
        $attribute = $em->getRepository('EcommerceBundle:Attribute')->find($attributeId);

        if (!$attribute) {
            throw $this->createNotFoundException('Unable to find Attribute entity.');
        }

        /** @var AttributeValue $entity */
        $entity = $em->getRepository('EcommerceBundle:AttributeValue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AttributeValue entity.');
        }

        $deleteForm = $this->createDeleteForm($attributeId, $id);
        $editForm = $this->createForm(new AttributeValueType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'value.edited');

            return $this->redirect($this->generateUrl('ecommerce_attributevalue_show', array('attributeId' => $attributeId, 'id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'attribute'   => $attribute,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a AttributeValue entity.
     *
     * @param Request $request     The request
     * @param int     $attributeId The attribute id
     * @param int     $id          The entity id
     *
     * @throws NotFoundHttpException
     * @return RedirectResponse
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $attributeId, $id)
    {
        $form = $this->createDeleteForm($attributeId, $id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            /** @var Attribute $attribute */
            $attribute = $em->getRepository('EcommerceBundle:Attribute')->find($attributeId);

            if (!$attribute) {
                throw $this->createNotFoundException('Unable to find Attribute entity.');
            }

            /** @var AttributeValue $entity */
            $entity = $em->getRepository('EcommerceBundle:AttributeValue')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AttributeValue entity.');
            }

            $em->remove($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'attribute.deleted');
        }

        return $this->redirect($this->generateUrl('ecommerce_attributevalue_index', array('attributeId' => $attributeId)));
    }

    /**
     * Creates a form to delete a AttributeValue entity by id.
     *
     * @param int $attributeId The attribute id
     * @param int $id          The entity id
     *
     * @return Form The form
     */
    private function createDeleteForm($attributeId, $id)
    {
        return $this->createFormBuilder(array('attributeId' => $attributeId, 'id' => $id))
            ->add('attributeId', 'hidden')
            ->add('id', 'hidden')
            ->getForm();
    }
}
