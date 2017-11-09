<?php
/**
 * This file is part of the MharySettingsBundle package.
 * (c) 2013 Dmitriy Scherbina <http://mhary.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mhary\SettingsBundle\Controller;

use Mhary\SettingsBundle\Entity\SettingsOwnerInterface;
use Mhary\SettingsBundle\Form\Type\SettingsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SettingsController extends Controller
{

    /**
     * @param Request $request
     * @param string $group
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function manageGlobalAction(Request $request,
                                       string $group = 'default')
    {
        $securitySettings = $this->container->getParameter('settings_manager.security');

        if (!empty($securitySettings['manage_global_settings_role']) &&
            !$this->getAuthorizationChecker()->isGranted($securitySettings['manage_global_settings_role'])
        ) {
            throw new AccessDeniedException(
            $this->container->get('translator')->trans(
                'not_allowed_to_edit_global_settings', array(), 'settings'
            )
            );
        }

        return $this->manage($request, null, $group);
    }

    /**
     * @param Request $request
     * @param string $group
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function manageOwnAction(Request $request, string $group = 'default')
    {
        $securityContext = $this->getSecurityContext();

        if (!$securityContext->getToken()) {
            throw new AccessDeniedException(
            $this->get('translator')->trans(
                'must_be_logged_in_to_edit_own_settings', array(), 'settings'
            )
            );
        }

        $securitySettings = $this->container->getParameter('settings_manager.security');
        if (!$securitySettings['users_can_manage_own_settings']) {
            throw new AccessDeniedException(
            $this->get('translator')->trans(
                'not_allowed_to_edit_own_settings', array(), 'settings'
            )
            );
        }

        $user = $securityContext->getToken()->getUser();

        if (!($user instanceof SettingsOwnerInterface)) {
            //For this to work the User entity must implement SettingsOwnerInterface
            throw new AccessDeniedException();
        }

        return $this->manage($request, $user, $group);
    }

    /**
     * @param Request $request
     * @param SettingsOwnerInterface|null $owner
     * @param string $group
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function manage(Request $request,
                              SettingsOwnerInterface $owner = null,
                              $group = 'default')
    {

        $form = $this->createForm(SettingsType::class,
            $this->get('settings_manager')->all($owner), ['group' => $group]);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->get('settings_manager')->setMany($form->getData(), $owner);
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('settings_updated', array(),
                        'settings')
                );

                return $this->redirect($request->getUri());
            }
        }

        return $this->render(
                $this->container->getParameter('settings_manager.template'),
                array(
                'settings_form' => $form->createView(),
                )
        );
    }

    /**
     * Get AuthorizationChecker service.
     *
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface|\Symfony\Component\Security\Core\SecurityContextInterface
     */
    private function getAuthorizationChecker()
    {
        // SF 2.6+
        // http://symfony.com/blog/new-in-symfony-2-6-security-component-improvements
        if ($this->has('security.authorization_checker')) {
            return $this->get('security.authorization_checker');
        }

        // SF < 2.6
        return $this->get('security.context');
    }

    /**
     * Get SecurityContext service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface|\Symfony\Component\Security\Core\SecurityContextInterface
     */
    private function getSecurityContext()
    {
        // SF 2.6+
        // http://symfony.com/blog/new-in-symfony-2-6-security-component-improvements
        if ($this->has('security.token_storage')) {
            return $this->get('security.token_storage');
        }

        // SF < 2.6
        return $this->get('security.context');
    }
}