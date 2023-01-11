<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class UserController extends AbstractController
{
    /**
     * List of all users.
     *
     * @Route("/api/doc/user", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns all users"
     * )
     * @OA\Tag(name="user")
     * @Security(name="Bearer")
     */
    public function index(UserRepository $users): JsonResponse
    {
        $allUsers = $users->findAll();
        
        $usersData = [];
        foreach ($allUsers as $user) {
            $usersData[] = $user->getUserData();
        }

        return $this->json($usersData);
    }

    /**
     * Get user by id.
     *
     * @Route("/api/doc/user/{id}", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns user"
     * )
     * @OA\Tag(name="user")
     * @Security(name="Bearer")
     */
    public function showOne(int $id, UserRepository $users): JsonResponse
    {
        if ($user = $users->find($id)) {
            $userData = $user->getUserData();
        
            return $this->json($userData);
        }

        return $this->json(['error' => 'User not found']);
    }

    /**
     * Create user
     *
     * @Route("/api/doc/user/", methods={"POST"})
     * @OA\Response(
     *     response=200,
     *     description="Creates an user"
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="room",
     *     in="query",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="user")
     * @Security(name="Bearer")
     */
    public function add(Request $request, UserRepository $users): JsonResponse
    {
        $userName = $request->query->get('name');
        $userRoom = $request->query->get('room');

        if ($userName) {
            $user = new User();
            $user->setName($userName);
            $user->setRoom($userRoom);
            $users->save($user, true);

            return $this->json([]);
        }

        return $this->json(['error' => 'Name is required']);
    }

    /**
     * Remove user
     *
     * @Route("/api/doc/user", methods={"DELETE"})
     * @OA\Response(
     *     response=200,
     *     description="Removes user"
     * )
     * @OA\Parameter(
     *     name="userId",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="user")
     * @Security(name="Bearer")
     */
    public function delete(Request $request, UserRepository $users)
    {
        $userId = $request->query->get('userId');
        
        if ($user = $users->find($userId)) {
            $users->remove($user, true);

            return $this->json([]);
        }

        return $this->json(['error' => 'Room not found']);
    }
}
