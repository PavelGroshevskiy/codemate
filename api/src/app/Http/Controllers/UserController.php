<?php

namespace App\Http\Controllers;

use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected BalanceService $balanceService;

    public function __construct(BalanceService $balanceService) {
        $this->balanceService = $balanceService;
    }

    /**
     * Получение баланса пользователя.
     *
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function getBalance(Request $request, int $userId): JsonResponse
    {
        try {
            $result = $this->balanceService->getBalance($userId);
            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ],$e->getCode());
        }
    }

    /**
     * Пополнение баланса пользователя.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addBalanceCash(Request $request): JsonResponse
    {
        $userId = (int)$request->get('userId');
        $amount = (int)$request->get('amount');
        $comment = $request->get('comment');
        try {
            $result = $this->balanceService->deposit($userId, $amount, $comment);
            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ],$e->getCode());
        }
    }

    /**
     * Списание средств
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function withdrawCash(Request $request): JsonResponse
    {
        ['userId' => $userId, 'amount' => $amount, 'comment' => $comment] = $request->all();
        try {
            $result = $this->balanceService->withdraw($userId, $amount, $comment);
            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ],$e->getCode());
        }
    }

    /**
     * Перевод между пользователями
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function userTransfer(Request $request): JsonResponse
    {
        [
            'fromUserId' => $fromUserId,
            'toUserId' => $toUserId,
            'amount' => $amount,
            'comment' => $comment
        ] = $request->all();
        try {
            $result = $this->balanceService->transfer($fromUserId,$toUserId, $amount, $comment);
            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ],$e->getCode());
        }
    }
}
