<?php

namespace Gearbox\SecurityBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View as RestView;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use Gearbox\SecurityBundle\Entity\User;

use Gearbox\SecurityBundle\Form\UserType;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get user
     *
     * @ApiDoc(
     *  section="Users",
     *  resource=true,
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when user is not found"
     *  },
     *  output={
     *      "class"="Gearbox\SecurityBundle\Entity\User",
     *      "groups"={"details"}
     *  }
     * )
     *
     * @param string $name Name of user
     *
     * @return array
     */
    public function getAction($name)
    {
        $user = $this->getEntity($name);

        $view = $this->view($user);
        $view->setTemplateVar('user');

        $serializationGroups = array('details');

        // @TODO This should be done always and automatically!
        if ($this->getUser() == $user) {
            $serializationGroups[] = 'owner';
        }
        foreach ($this->getUser()->getRoles() as $role) {
            $serializationGroups[] = $role;
        }

        $view->setSerializationContext(SerializationContext::create()->setGroups($serializationGroups));

        return $this->handleView($view);
    }

    /**
     * Get users
     *
     * @ApiDoc(
     *  section="Users",
     *  resource=true,
     *  statusCodes={
     *      200="Returned when successful"
     *  }
     * )
     *
     * @RestView(
     *  serializerGroups={"list"}
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return array
     */
    public function cgetAction()
    {
        $rep = $this->getDoctrine()->getRepository('GearboxSecurityBundle:User');

        $users = $rep->findAll();

        return array('users' => $users);
    }

    /**
     * Create user
     *
     * @ApiDoc(
     *  section="Users",
     *  resource=true,
     *  input="Gearbox\SecurityBundle\Form\UserType",
     *  statusCodes={
     *      201="Returned when created",
     *      400="Returned when validation failed"
     *  }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @return View|FormTypeInterface
     */
    public function postAction(Request $request)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user        = $userManager->createUser();
        $form        = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user->setPlainPassword('Welcome1!');
            $user->setEnabled(true);
            $userManager->updateUser($user);

            return $this->redirectView(
                $this->generateUrl(
                    'api_get_user',
                    array(
                        'name' => $user->getUsername()
                    )
                ),
                Codes::HTTP_CREATED
            );
        }

        return $form;
    }

    /**
     * Update user
     *
     * @ApiDoc(
     *  section="Users",
     *  resource=true,
     *  input="Gearbox\SecurityBundle\Form\UserType",
     *  statusCodes={
     *      204="Returned when successful",
     *      400="Returned when validation failed",
     *      404="Returned when user was not found"
     *  }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param string $name Name of user
     * @param Request $request
     *
     * @return View
     */
    public function putAction($name, Request $request)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user        = $this->getEntity($name);
        $form        = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $userManager->updateUser($user);

            return $this->redirectView(
                $this->generateUrl(
                    'api_get_user',
                    array(
                        'name' => $user->getUsername()
                    )
                ),
                Codes::HTTP_NO_CONTENT
            );
        }

        return $form;
    }

    /**
     * Delete user
     *
     * @ApiDoc(
     *  section="Users",
     *  resource=true,
     *  statusCodes={
     *      204="Returned when successful",
     *      404="Returned when user was not found"
     *  }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param string $name Name of user
     *
     * @return View
     */
    public function deleteAction($name)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user        = $this->getEntity($name);

        $userManager->deleteUser($user);

        return $this->redirectView(null, Codes::HTTP_NO_CONTENT);
    }

    /**
     * @param string $name
     *
     * @return User
     *
     * @throws NotFoundHttpException When user could not be found
     */
    private function getEntity($name)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($name);
        if (!$user instanceof User) {
            throw $this->createNotFoundException(sprintf('User %s not found', $name));
        }

        return $user;
    }
}
