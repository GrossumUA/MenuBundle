<?php

namespace Grossum\MenuBundle\Controller;

use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        $menu = $this
            ->get('grossum_menu.menu.entity.manager')
            ->getRepository()
            ->find($menuId);
        /* @var $menu BaseMenu */

        $root = $this
            ->get('grossum_menu.menu_item.entity.manager')
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
        $menu = $this
            ->get('grossum_menu.menu.entity.manager')
            ->getRepository()
            ->find($menuId);
        /* @var $menu BaseMenu */

        $menuItemManager = $this->get('grossum_menu.menu_item.entity.manager');
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

        if ($uniqid) {
            $this->admin->setUniqid($uniqid);
        }

        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $form = $this->admin->getForm();
        $form->setData($object);
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
