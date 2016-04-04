<?php

namespace Grossum\MenuBundle\Controller;

use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sonata\AdminBundle\Controller\CRUDController as Controller;

use Grossum\MenuBundle\Entity\BaseMenu;

class MenuItemAdminController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     * @throws \Twig_Error_Runtime
     */
    public function treeAction(Request $request)
    {
        $menuId = $request->get($this->admin->getParent()->getIdParameter());

        /* @var $menu BaseMenu */
        $menu = $this
            ->get('grossum_menu.entity_manager.menu.manager')
            ->getRepository()
            ->find($menuId);

        $root = $this
            ->get('grossum_menu.entity.manager.menu_item.manager')
            ->getRepository()
            ->findMenuRootItem($menu);

        $formView = $this->admin->getDatagrid()->getForm()->createView();
        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render('GrossumMenuBundle:MenuItemAdmin:tree.html.twig', [
            'action'                  => 'tree',
            'root'                    => $root,
            'menu'                    => $menu,
            'form'                    => $formView,
            'csrf_token'              => $this->getCsrfToken('sonata.batch'),
            'grossum_menu_tree_depth' => $this->getParameter('grossum_menu_tree_depth'),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTreeAction(Request $request)
    {
        $tree = $request->request->get('tree');

        $menuId = $request->get($this->admin->getParent()->getIdParameter());

        /* @var $menu BaseMenu */
        $menu = $this
            ->get('grossum_menu.entity_manager.menu.manager')
            ->getRepository()
            ->find($menuId);

        $menuItemManager = $this->get('grossum_menu.entity.manager.menu_item.manager');
        $verified = $menuItemManager->updateAndVerifyTree($tree, $menu);

        if ($verified !== true) {
            return new JsonResponse(['result' => false]);
        }

        $menuItemManager->flush();

        return new JsonResponse(['result' => true]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEntityIdentifiersByEntityClassAction(Request $request)
    {
        $elementId = $request->get('elementId');
        $uniqid    = $request->get('uniqid');
        $objectId  = $request->get('objectId');

        if ($uniqid) {
            $this->admin->setUniqid($uniqid);
        }

        $subject = $this->admin->getModelManager()->find($this->admin->getClass(), $objectId);
        if ($objectId && !$subject) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $objectId));
        }

        if (!$subject) {
            $subject = $this->admin->getNewInstance();
        }

        $this->admin->setSubject($subject);

        $form = $this->admin->getForm();
        $form->setData($subject);
        $form->handleRequest($request);

        $twig = $this->get('twig');

        /* @var $extension FormExtension */
        $extension =$twig->getExtension('form');
        $extension->initRuntime($twig);

        $elementView = $this->get('sonata.admin.helper')->getChildFormView($form->createView(), $elementId);

        $extension->renderer->setTheme($elementView, $this->admin->getFormTheme());

        return new JsonResponse([
            'result'            => true,
            'entityIdentifiers' => $extension->renderer->searchAndRenderBlock($elementView, 'widget')
        ]);
    }
}
