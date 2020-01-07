<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/create-user", name="create_user", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function createUser(Request $request)
    {
        $requestContent = $this->getRequestContent($request);

        foreach (['id', 'currency', 'token'] as $param) {
            if (!isset($requestContent[$param])) {
                return $this->parameterNotFound($param);
            }
        }

        $user = new User();
        $user
            ->setCurrency($requestContent['currency'])
            ->setToken($requestContent['token'])
            ->setBalance($requestContent['balance'] ?? 0);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json([
            'status'   => 'success',
            'message'  => 'user was created',
            'currency' => $user->getCurrency(),
            'balance'  => $user->getBalance(),
            'token'    => $user->getToken()
        ], Response::HTTP_OK, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/delete-user", name="delete_user", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUser(Request $request)
    {
        $requestContent = $this->getRequestContent($request);
        if (!isset($requestContent['token'])) {
            return $this->parameterNotFound('token');
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['token' => $requestContent['token']]);
        if (!$user) {
            return $this->userNotFound();
        }

        $em->remove($user);
        $em->flush();

        return $this->json([
            'status'  => 'success',
            'message' => 'user was deleted',
        ], Response::HTTP_OK, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/get-balance", name="get_balance", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getBalance(Request $request)
    {
        $requestContent = $this->getRequestContent($request);

        foreach (['id', 'token'] as $param) {
            if (!isset($requestContent[$param])) {
                return $this->parameterNotFound($param);
            }
        }

        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['token' => $requestContent['token']]);
        if (!$user) {
            return $this->userNotFound();
        }


        return $this->json([
            'token'    => $user->getToken(),
            'currency' => $user->getCurrency(),
            'balance'  => $user->getBalance(),
            'status'   => 'success'
        ], Response::HTTP_OK, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/cashin-user", name="cashin_user", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function cashinUser(Request $request): JsonResponse
    {
        $requestContent = $this->getRequestContent($request);

        foreach (['id', 'amount', 'token'] as $param) {
            if (!isset($requestContent[$param])) {
                return $this->parameterNotFound($param);
            }
        }

        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['token' => $requestContent['token']]);
        if (!$user) {
            return $this->userNotFound();
        }

        $em = $this->getDoctrine()->getManager();
        $user->addBalance($requestContent['amount']);

        $em->flush();

        return $this->json([
            'status'   => 'success',
            'message'  => 'balance was updated',
            'currency' => $user->getCurrency(),
            'balance'  => $user->getBalance(),
            'wlid'     => $user->getId()
        ], Response::HTTP_OK, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/cashout-user", name="cashout_user", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function cashoutUser(Request $request)
    {
        $requestContent = json_decode($request->getContent(), true);

        foreach (['id', 'amount', 'token'] as $param) {
            if (!isset($requestContent[$param])) {
                return $this->parameterNotFound($param);
            }
        }

        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['token' => $requestContent['token']]);
        if (!$user) {
            return $this->userNotFound();
        }

        if ($user->getBalance() < $requestContent['amount']) {
            return $this->json([
                'status'  => 'error',
                'message' => 'low balance'
            ], Response::HTTP_OK, ['Access-Control-Allow-Origin' => '*']);
        }

        $em = $this->getDoctrine()->getManager();
        $user->addBalance(-$requestContent['amount']);
        $em->flush();

        return $this->json([
            'status'   => 'success',
            'message'  => 'balance was updated',
            'currency' => $user->getCurrency(),
            'balance'  => $user->getBalance(),
            'token'    => $user->getToken(),
        ], Response::HTTP_OK, ['Access-Control-Allow-Origin' => '*']);
    }

    private function parameterNotFound($param)
    {
        return $this->json([
            'status'  => 'error',
            'message' => "parameter '$param' not found"
        ], Response::HTTP_OK, ['Access-Control-Allow-Origin' => '*']);
    }

    private function userNotFound(): JsonResponse
    {
        return $this->json([
            'status'  => 'error',
            'message' => 'user not found'
        ], Response::HTTP_OK, ['Access-Control-Allow-Origin' => '*']);

    }

    private function getRequestContent(Request $request): ?array
    {
        return json_decode($request->getContent(), true);
    }

}
