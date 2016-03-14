<?php

namespace Grossum\MenuBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;

use Sonata\AdminBundle\Controller\CRUDController as Controller;

use Grossum\MenuBundle\Entity\BaseMenu;
use Grossum\MenuBundle\Form\Type\MenuEntityIdentifierType;

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
        $entityClass = $request->request->get('entityClass');

        $data = [];

        if ($entityClass) {
            $entityIdentifiers = $this
                ->get('grossum_menu.menu.manager')
                ->getMenuHandler($entityClass)
                ->getIdentifierList();

            $em = $this->get('grossum_core.entity_manager');

            $classMetadata = $this->get('grossum_core.entity_manager')->getClassMetadata($entityClass);
            $idReader = new IdReader($em, $classMetadata);

            foreach ($entityIdentifiers as $entityIdentifier) {
                $id    = (string) $idReader->getIdValue($entityIdentifier);
                $label = MenuEntityIdentifierType::choiceLabel($entityIdentifier);

                $data[$id] = $label;
            }
        }

        return new JsonResponse([
            'result'            => true,
            'entityIdentifiers' => $data
        ]);
    }
}
