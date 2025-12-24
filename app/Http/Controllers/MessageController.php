<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    /**
     * GET /api/messages
     * List messages for the authenticated user. Optional filter by participant_id.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $participantId = $request->query('participant_id');

            $query = Message::with(['sender:id,name,email', 'receiver:id,name,email'])
                ->where(function ($q) use ($userId) {
                    $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
                })
                ->orderByDesc('created_at');

            if ($participantId) {
                $query->where(function ($q) use ($participantId, $userId) {
                    $q->where(function ($qq) use ($participantId, $userId) {
                        $qq->where('sender_id', $userId)->where('receiver_id', $participantId);
                    })->orWhere(function ($qq) use ($participantId, $userId) {
                        $qq->where('sender_id', $participantId)->where('receiver_id', $userId);
                    });
                });
            }

            $messages = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $messages,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Messages fetch failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch messages',
            ], 500);
        }
    }

    /**
     * POST /api/messages
     * Send a message from authenticated user to receiver.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'body' => 'required|string|min:1',
                'status' => 'nullable|string|in:sent,delivered,read',
            ]);

            $message = Message::create([
                'sender_id' => $request->user()->id,
                'receiver_id' => $validated['receiver_id'],
                'body' => $validated['body'],
                'status' => $validated['status'] ?? 'sent',
            ]);

            // Load sender/receiver for response (includes sender_id)
            $message->load(['sender:id,name,email', 'receiver:id,name,email']);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Message send failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
            ], 500);
        }
    }
}
