<?php

namespace Gearbox\SecurityBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Gearbox\SecurityBundle\Entity\User;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends FOSRestController
{
    /**
     * Get user
     *
     * @ApiDoc(
     *  resource=true,
     *  statusCodes={
     *      200="Returned when sucessful",
     *      404="Returned when user is not found"
     *  }
     * )
     *
     * @param string $name Name of user
     *
     * @return \Gearbox\SecurityBundle\Entity\User
     */
    public function getAction($name)
    {
        $user = $this->getEntity($name);

        return $user;
    }

    /**
     * @param string $name
     *
     * @throws NotFoundHttpException When user could not be found
     */
    private function getEntity($name) {
        $rep  = $this->getDoctrine()->getRepository('GearboxSecurityBundle:User');
        $user = $rep->findOneBy(array('username' => $name));
        if (!$user instanceof User) {
            throw $this->createNotFoundException(sprintf('User %s not found', $name));
        }

        return $user;
    }
}
