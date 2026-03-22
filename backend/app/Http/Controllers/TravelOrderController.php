<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderStatusRequest;
use App\Models\TravelOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $query = TravelOrder::query()->with('user');

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('destination')) {
            $query->where('destination', 'like', '%' . request('destination') . '%');
        }

        if (request()->filled('departure_start')) {
            $query->whereDate('departure_date', '>=', request('departure_start'));
        }

        if (request()->filled('departure_end')) {
            $query->whereDate('departure_date', '<=', request('departure_end'));
        }

        $travelOrders = $query->latest()->get();

        return response()->json([
            'message' => 'Pedidos listados com sucesso.',
            'data' => $travelOrders,
        ]);
    }

    public function store(StoreTravelOrderRequest $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $data = $request->validated();

        $travelOrder = TravelOrder::create([
            'user_id' => $user->id,
            'destination' => $request->destination,
            'departure_date' => $request->departure_date,
            'return_date' => $request->return_date,
            'status' => 'solicitado',
        ]);

        $travelOrder->load('user');

        return response()->json([
            'message' => 'Pedido de viagem criado com sucesso.',
            'data' => $travelOrder,
        ],201);
    }

    public function show(int $id): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $travelOrder = TravelOrder::with('user')->findOrFail($id);

        if ($user->role !== 'admin' && $travelOrder->user_id !== $user->id) {
            return response()->json([
                'message' => 'Você não tem permissão para visualizar este pedido.'
            ], 403);
        }

        return response()->json([
            'message' => 'Pedido encontrado com sucesso.',
            'data' => $travelOrder,
        ]);
    }

    public function updateStatus(UpdateTravelOrderStatusRequest $request, int $id): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $data = $request->validated();

        $travelOrder = TravelOrder::findOrFail($id);

        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Apenas administradores podem alterar o status.'
            ], 403);
        }

        if ($travelOrder->user_id === $user->id) {
            return response()->json([
                'message' => 'O autor do pedido não pode alterar o próprio status.'
            ], 403);
        }

        if ($travelOrder->status === 'aprovado' && $data['status'] === 'cancelado') {
            return response()->json([
                'message' => 'Um pedido aprovado não pode ser cancelado.'
            ], 422);
        }

        $travelOrder->update([
            'status' => $data['status'],
        ]);

        return response()->json([
            'message' => 'Status do pedido atualizado com sucesso.',
            'data' => $travelOrder,
        ]);
    }
}