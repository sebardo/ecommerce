<?php

namespace EcommerceBundle\Controller;

use EcommerceBundle\Form\ProductRelatedType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Product;
use EcommerceBundle\Form\ProductType;
use EcommerceBundle\Form\ProductAttributesType;
use EcommerceBundle\Form\ProductFeaturesType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CoreBundle\Entity\Image;

/**
 * Product controller.
 *
 * @Route("/admin/products")
 */
class ProductController extends Controller
{
    /**
     * Lists all Product entities.
     *
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("EcommerceBundle:Product:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns a list of Product entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();
        $adminManager = $this->get('admin_manager');
        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Product'));
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user->isGranted('ROLE_USER')) {
            $jsonList->setActor($user);
        }
        
        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Returns a list of Actor entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/stats/{id}/{from}/{to}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function statsAction($id, $from, $to)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Actor $entity */
        $entity = $em->getRepository('EcommerceBundle:Product')->find($id);
        
        /** @var FrontManager $frontManager */
        $adminManager =  $this->container->get('admin_manager');
        $stats = $adminManager->getProductStats($entity, $from, $to);

        $label = array();
        $visits = array();
        foreach ($stats as  $stat) {
            $label[] = $stat['day'];
            $visits[] = $stat['visits'];
        }
        
        $returnValues = new \stdClass();
        $returnValues->count = count($stats);
        $returnValues->labels = implode(',', $label);
        $returnValues->visits = implode(',', $visits);

        return new JsonResponse($returnValues);
    }
    
    /**
     * Creates a new Product entity.
     *
     * @param Request $request The request
     *
     * @return array|RedirectResponse
     *
     * @Route("/")
     * @Method("POST")
     * @Template("EcommerceBundle:Product:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $formConfig = array();
        $user = $this->container->get('security.context')->getToken()->getUser();  
        if ($user->isGranted('ROLE_USER')) {
            $formConfig['actor'] = $user;
        }
        $entity  = new Product();
        $form = $this->createForm(new ProductType($formConfig), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush(); 

            //send emails to admin and actor
            $this->get('core.mailer')->sendActorNewProduct($entity);
            
            $this->get('session')->getFlashBag()->add('success', 'product.created');

            return $this->redirect($this->generateUrl('ecommerce_product_edit', array('id' => $entity->getId(), 'images' => 1)));
        }
        

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Product entity.
     *
     * @return array
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        $formConfig = array();
        
        $user = $this->container->get('security.context')->getToken()->getUser();  
        if ($user->isGranted('ROLE_USER')) {
            $formConfig['actor'] = $user;
            //disable notification
            $admin = $em->getRepository('CoreBundle:Actor')->find(1);//admin
            $notificationManager = $this->container->get('notification_manager');
            $notificationManager->disableNotificationByDetail(
                    $admin, //current user
                    $user,//admin
                    'add_product', 
                    '"actor":'.$user->getId().'}'
                );
        }
        $entity  = new Product();
        $form = $this->createForm(new ProductType($formConfig), $entity);

        
         
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * @Route("/dashboard-product")
     * @Template()
     */
    public function dashboardAction()
    {
        $em = $this->getDoctrine()->getManager();
        $totalVisits = $em->getRepository('EcommerceBundle:Product')->countTotalStatsVisits();
        $totalActorVisited = $em->getRepository('EcommerceBundle:Product')->countProductVisited();
        $totalActors = $em->getRepository('EcommerceBundle:Product')->countTotal();
        $bounceRate = $em->getRepository('EcommerceBundle:Product')->bounceRate();
        $stats = $em->getRepository('EcommerceBundle:Product')->countProductStatsLine();
        $report = $em->getRepository('EcommerceBundle:Product')->countVisitsByProduct();
        $cuponCount = $em->getRepository('EcommerceBundle:Transaction')->getCountCupon();
        $bestSeller = $em->getRepository('EcommerceBundle:Transaction')->getBestSellerProducts(1);
        $totalAmount = $em->getRepository('EcommerceBundle:Transaction')->totalAmountCharged();
        $totalTransactions = $em->getRepository('EcommerceBundle:Transaction')->countTotal();
        
        
        return array(
            'totalVisits' => $totalVisits,
            'totalProductVisited' => $totalActorVisited,
            'totalProducts' => $totalActors,
            'bounceRate' => $bounceRate,
            'stats' => $stats,
            'report' => $report,
            'cuponCount' => $cuponCount,
            'bestSeller' => $bestSeller,
            'totalAmount' => $totalAmount,
            'totalTransactions' => $totalTransactions
        );
   }
   
    /**
     * Finds and displays a Product entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Product $entity */
        $entity = $em->getRepository('EcommerceBundle:Product')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        //disable notification
        $admin = $em->getRepository('CoreBundle:Actor')->find(1);//admin
        $notificationManager = $this->container->get('notification_manager');
        $notificationManager->disableNotificationByDetail(
                $admin, //current user
                $admin,//admin
                'new_product', 
                '"product":'.$entity->getId().'}'
                );
        
        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Product entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/{id}/edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Product $entity */
        $entity = $em->getRepository('EcommerceBundle:Product')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }
        
        //access control
        $user = $this->container->get('security.context')->getToken()->getUser();  
        if ($user->isGranted('ROLE_USER') && $entity->getActor()->getId() != $user->getId()) {
            return $this->redirect($this->generateUrl('ecommerce_product_index'));
        }
        

        $formConfig = array();
        $user = $this->container->get('security.context')->getToken()->getUser();  
        if ($user->isGranted('ROLE_USER')) {
            $formConfig['actor'] = $user;
        }
        $editForm = $this->createForm(new ProductType($formConfig), $entity);
        $attributesForm = $this->createForm(new ProductAttributesType($entity->getCategory()), $entity);
        $featuresForm = $this->createForm(new ProductFeaturesType($entity->getCategory()), $entity);
        $relatedProductsForm = $this->createForm(new ProductRelatedType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'          => $entity,
            'edit_form'       => $editForm->createView(),
            'attributes_form' => $attributesForm->createView(),
            'features_form'   => $featuresForm->createView(),
            'related_form'    => $relatedProductsForm->createView(),
            'delete_form'     => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Product entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/{id}")
     * @Method("PUT")
     * @Template("EcommerceBundle:Product:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Product $entity */
        $entity = $em->getRepository('EcommerceBundle:Product')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        
        $formConfig = array();
        $user = $this->container->get('security.context')->getToken()->getUser();  
        if ($user->isGranted('ROLE_USER')) {
            $formConfig['actor'] = $user;
        }
        $editForm = $this->createForm(new ProductType($formConfig), $entity);
        $attributesForm = $this->createForm(new ProductAttributesType($entity->getCategory()), $entity);
        $featuresForm = $this->createForm(new ProductFeaturesType($entity->getCategory()), $entity);
        $relatedProductsForm = $this->createForm(new ProductRelatedType(), $entity);

        $form = $editForm;
        $redirectParams = array('id' => $id);

        if ($request->request->has('ecommercebundle_productattributestype')) {

            // attributes were submitted
            $form = $attributesForm;
            $redirectParams = array_merge($redirectParams, array('attributes' => 1));

        } else if ($request->request->has('ecommercebundle_productfeaturestype')) {

            // features were submitted
            $form = $featuresForm;
            $redirectParams = array_merge($redirectParams, array('features' => 1));

        } else if ($request->request->has('ecommercebundle_productrelatedtype')) {

            // related products were submitted
            $form = $relatedProductsForm;
            $redirectParams = array_merge($redirectParams, array('related' => 1));

        }

        $form->bind($request);

        if ($form->isValid()) {

            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'product.edited');

            return $this->redirect($this->generateUrl('ecommerce_product_show', $redirectParams));
        }else{
            $string = (string) $form->getErrors(true, false);
            print_r($string);
            die('asd');
        }

        return array(
            'entity'          => $entity,
            'edit_form'       => $editForm->createView(),
            'attributes_form' => $attributesForm->createView(),
            'features_form'   => $featuresForm->createView(),
            'delete_form'     => $deleteForm->createView(),
            'related_form'    => $relatedProductsForm->createView(),
        );
    }

    /**
     * Deletes a Product entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return RedirectResponse
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var Product $entity */
            $entity = $em->getRepository('EcommerceBundle:Product')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Product entity.');
            }
 
            try {
                $em->remove($entity);
                $em->flush();
                $this->get('session')->getFlashBag()->add('info', 'product.deleted');
            } catch (\Exception $exc) {
                $this->get('session')->getFlashBag()->add('warning', 'product.deleted.error');
            }
        }

        return $this->redirect($this->generateUrl('ecommerce_product_index'));
    }

    /**
     * Manages a product image
     *
     * @return array
     *
     * @Route("/{id}-{slug}/image")
     */
    public function manageImage()
    {
        $this->get('upload_handler');

        return new Response();
    }

    /**
     * Manages a product image
     *
     * @return array
     *
     * @Route("/update/image")
     */
    public function updateImage(Request $request)
    {
        $fileName = $request->query->get('file');
        $type = $request->query->get('type');
        $value = $request->query->get('value');
      
        /** @var Image $entity */
        $qb = $this->getDoctrine()->getManager()->getRepository('CoreBundle:Image')
                ->createQueryBuilder('i');
        $image = $qb->where($qb->expr()->like('i.path', ':path'))
            ->setParameter('path','%'.$fileName.'%')
            ->getQuery()
            ->getOneOrNullResult();

//        $image = $this->getDoctrine()->getManager()->getRepository('CoreBundle:Image')->findOneBy(array('path' => $fileName));
       
        if (!$image) {
            throw new NotFoundHttpException('Unable to find Image entity.');
        }
        

        if($type == 'title') $image->setTitle($value);
        if($type == 'alt') $image->setAlt($value);
        
        $this->getDoctrine()->getManager()->flush();
            

        return new JsonResponse(array('success' => true));
    }
    
    /**
     * Creates a form to delete a Product entity by id.
     *
     * @param int $id The entity id
     *
     * @return Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
    
    
}
