<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'message' => 'Notificações listadas com sucesso.',
            'data' => $notifications,
        ]);
    }

    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notificação marcada como lida com sucesso.',
        ]);
    }
}