<?php

namespace Gearbox\SecurityBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;

use FOS\RestBundle\Controller\Annotations\View as RestView;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\Serializer\SerializationContext;
use \FOS\RestBundle\View\View;

use Gearbox\SecurityBundle\Entity\User;

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
     *  output="Gearbox\SecurityBundle\Entity\User"
     * )
     *
     * @RestView(
     *  serializerGroups={"details"}
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
            $serializationGroups[] = 'me';
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
     *  statusCodes={
     *      201="Returned when created",
     *      400="Returned when validation failed"
     *  }
     * )
     *
     * @return View
     */
    public function postAction()
    {

    }

    /**
     * Update user
     *
     * @ApiDoc(
     *  section="Users",
     *  resource=true,
     *  statusCodes={
     *      204="Returned when successful",
     *      400="Returned when validation failed",
     *      404="Returned when user was not found"
     *  }
     * )
     *
     * @param string $name Name of user
     *
     * @return View
     */
    public function putAction($name)
    {

    }

    /**
     * Update user password
     *
     * @ApiDoc(
     *  section="Users",
     *  resource=true,
     *  statusCodes={
     *      204="Returned when successful",
     *      400="Returned when validation failed",
     *      404="Returned when user was not found"
     *  }
     * )
     *
     * @param string $name Name of user
     *
     * @return View
     */
    public function patchPasswordAction($name) {

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
     * @param string $name Name of user
     *
     * @return View
     */
    public function deleteAction($name)
    {

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
        $rep  = $this->getDoctrine()->getRepository('GearboxSecurityBundle:User');
        $user = $rep->findOneBy(array('username' => $name));
        if (!$user instanceof User) {
            throw $this->createNotFoundException(sprintf('User %s not found', $name));
        }

        return $user;
    }
}
