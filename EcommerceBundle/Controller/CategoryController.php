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
use EcommerceBundle\Entity\Category;
use EcommerceBundle\Form\CategoryType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Category controller.
 *
 * @Route("/admin/categories")
 */
class CategoryController extends Controller
{
    /**
     * Lists all Category entities.
     *
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("EcommerceBundle:Category:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns a list of Category entities in JSON format.
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
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Category'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new Category entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new Category();
        $form = $this->createForm('EcommerceBundle\Form\CategoryType', $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'category.created');

            return $this->redirectToRoute('ecommerce_category_show', array('id' => $entity->getId()));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }
 
    /**
     * Finds and displays a Category entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Category $category)
    {
        $deleteForm = $this->createDeleteForm($category);

        return array(
            'entity' => $category,
            'delete_form' => $deleteForm->createView(),
        );
    }

     /**
     * Displays a form to edit an existing Category entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Category $category)
    {
        
        $deleteForm = $this->createDeleteForm($category);
        $editForm = $this->createForm('EcommerceBundle\Form\CategoryType', $category);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            if($category->getRemoveImage()){
                $category->setImage(null);
            }
            
            $em->persist($category);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'category.edited');
            
            return $this->redirectToRoute('ecommerce_category_show', array('id' => $category->getId()));
        }

        return array(
            'entity' => $category,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Deletes a Category entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Category $category)
    {
        $form = $this->createDeleteForm($category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'category.deleted');
        }

        return $this->redirectToRoute('ecommerce_category_index');
    }

   /**
     * Creates a form to delete a Category entity.
     *
     * @param Category $category The Category entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Category $category)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ecommerce_category_delete', array('id' => $category->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
        
    /**
     * Sorts a list of features.
     *
     * @param Request $request
     * @param int     $categoryId
     *
     * @throws NotFoundHttpException
     * @return array|Response
     *
     * @Route("/sort")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function sortAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->isXmlHttpRequest()) {
            $this->get('admin_manager')->sort('EcommerceBundle:Category', $request->get('values'));

            return new Response(0, 200);
        }

        $categories = $em->getRepository('EcommerceBundle:Category')->findBy(
            array('parentCategory' => NULL),
            array('order' => 'asc')
        );

        return array(
            'categories' => $categories
        );
    }
    
}
