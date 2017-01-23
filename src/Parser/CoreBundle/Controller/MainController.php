<?php

namespace Parser\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Parser\CoreBundle\Entity\Post;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{

    /**
     * @Route("/api/v1/parse")
     */
    public function indexAction(Request $request)
    {
        $parser = $this->get('parser_service.instagram');
        $parser->parse();

        $parser = $this->get('parser_service.twitter');
        $parser->parse();

        return $this->makeJsonResponse(array('success' => 1));
    }

    /**
     * @Route("/api/v1/posts")
     */
    public function postListAction(Request $request)
    {
        $page = abs(intval($request->get('page')));
        $perPage = $request->get('perPage') ? abs(intval($request->get('perPage'))) : 20;

        $list = $this->getDoctrine()->getManager()->getRepository(Post::ALIAS)->getPostList($page, $perPage);

        return $this->makeJsonResponse($list);
    }

    /**
     * @Route("/")
     */
    public function defaultAction(Request $request)
    {
        return $this->makeJsonResponse(array('success' => 0));
    }

    protected function makeJsonResponse($data)
    {
        $response = new \Symfony\Component\HttpFoundation\JsonResponse();
        $response->setData($data);

        return $response;
    }

}
