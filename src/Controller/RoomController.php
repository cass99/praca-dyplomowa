<?php

namespace App\Controller;

use App\Entity\Room;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class RoomController extends AbstractController
{
    /**
     * List of all rooms.
     *
     * @Route("/api/doc/room", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns all rooms"
     * )
     * @OA\Tag(name="room")
     * @Security(name="Bearer")
     */
    public function index(RoomRepository $rooms): JsonResponse
    {
        $allRooms = $rooms->findAll();
        
        $roomsData = [];
        foreach ($allRooms as $room) {
            $roomsData[] = $room->getRoomData();
        }

        return $this->json($roomsData);
    }

    /**
     * Get room by id.
     *
     * @Route("/api/doc/room/{id}", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns room"
     * )
     * @OA\Tag(name="room")
     * @Security(name="Bearer")
     */
    public function showOne(int $id, RoomRepository $rooms): JsonResponse
    {
        if ($room = $rooms->find($id)) {
            $roomData = $room->getRoomData();
        
            return $this->json($roomData);
        }

        return $this->json(['error' => 'Room not found']);
    }

    /**
     * Create room
     *
     * @Route("/api/doc/room", methods={"POST"})
     * @OA\Response(
     *     response=200,
     *     description="Adds room"
     * )
     * @OA\Parameter(
     *     name="userId",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="room")
     * @Security(name="Bearer")
     */
    public function add(Request $request, RoomRepository $rooms, UserRepository $users): JsonResponse
    {
        $userId = $request->query->get('userId');

        if ($user = $users->find($userId)) {
            $room = new Room(); 
            $room->addUser($user);
            $rooms->save($room, true);
            
            return $this->json([]);
        }

        return $this->json(['error' => 'User not found']);
    }

    /**
     * Remove room
     *
     * @Route("/api/doc/room", methods={"DELETE"})
     * @OA\Response(
     *     response=200,
     *     description="Removes room"
     * )
     * @OA\Parameter(
     *     name="roomId",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="room")
     * @Security(name="Bearer")
     */
    public function delete(Request $request, RoomRepository $rooms, UserRepository $users)
    {
        $roomId = $request->query->get('roomId');
        
        if ($room = $rooms->find($roomId)) {
            $usersWithRoom = $users->findBy(['room' => $room]);
            foreach ($usersWithRoom as $user) {
                $user->setRoom(null);
            }
            $rooms->remove($room, true);

            return $this->json([]);
        }

        return $this->json(['error' => 'Room not found']);
    }
}
