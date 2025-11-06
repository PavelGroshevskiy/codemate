<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class BalanceService
{
    private const MIN_AMOUNT = 0.01;

    /**
     * Сервис начисление средств пользователю
     *
     * @param int $userId
     * @param float $amount
     * @param string|null $comment
     * @return array
     * @throws Exception
     */
    public function deposit(int $userId, float $amount, string|null $comment): array
    {
        $this->validateAmount($amount);

        return DB::transaction(function () use ($userId, $amount, $comment) {

            $user = User::lockForUpdate()->find($userId);

            if (!$user) {
                throw new Exception('Пользователь не найден ...', 404);
            }

            $user->balance = bcadd($user->balance, $amount, 2);
            $user->save();

            $transaction = Transaction::create([
                'to_user_id' => $userId,
                'from_user_id' => $userId,
                'type' => Transaction::TYPE_DEPOSIT,
                'amount' => $amount,
                'comment' => $comment
            ]);

            return [
                'user_id' => $user->id,
                'balance' => (float) $user->balance,
                'transaction_id' => $transaction->id,
            ];
        });
    }

    /**
     * Сервис списание средств
     *
     * @param int $userId
     * @param float $amount
     * @param string|null $comment
     * @return array
     * @throws Exception
     */
    public function withdraw(int $userId, float $amount, string|null $comment): array
    {
        $this->validateAmount($amount);
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::lockForUpdate()->find($userId);
            if (!$user) {
                throw new Exception('Пользователь не найден ...', 404);
            }
            if (!$user->isEnoughBalance($amount)) {
                throw new Exception('Не хвататет средств', 409);
            }
            $user->balance = bcsub($user->balance, $amount, 2);
            $user->save();
            $transaction = Transaction::create([
                'from_user_id' => $userId,
                'type' => Transaction::TYPE_WITHDRAW,
                'amount' => $amount,
                'comment' => $comment,
            ]);
            return [
                'user_id' => $user->id,
                'balance' => (float) $user->balance,
                'transaction_id' => $transaction->id,
            ];
        });
    }

    /**
     * Перевод между юзерами
     *
     * @param int $fromUserId
     * @param int $toUserId
     * @param float $amount
     * @param string|null $comment
     * @return array
     * @throws Exception
     */
    public function transfer(int $fromUserId, int $toUserId, float $amount, string|null $comment): array
    {
        $this->validateAmount($amount);
        if ($fromUserId === $toUserId) {
            throw new Exception('Выберите другого пользовател для перевода', 422);
        }
        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $comment) {
            $fromUser = User::lockForUpdate()->find($fromUserId);
            $toUser = User::lockForUpdate()->find($toUserId);
            if (!$fromUser) {
                throw new Exception('Получатель не найден ...', 404);
            }
            if (!$toUser) {
                throw new Exception('Отправитель не найден ...', 404);
            }
            if (!$fromUser->isEnoughBalance($amount)) {
                throw new Exception('Не хвататет средств', 409);
            }

            $fromUser->balance = bcsub($fromUser->balance, $amount, 2);
            $fromUser->save();
            $toUser->balance = bcadd($toUser->balance, $amount, 2);
            $toUser->save();

            $outTransaction = Transaction::create([
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'type' => Transaction::TYPE_TRANSFER_OUT,
                'amount' => $amount,
                'related_user_id' => $toUserId,
                'comment' => $comment,
            ]);

            $inTransaction = Transaction::create([
                'to_user_id' => $toUserId,
                'from_user_id' => $fromUserId,
                'type' => Transaction::TYPE_TRANSFER_IN,
                'amount' => $amount,
                'related_user_id' => $fromUserId,
                'comment' => $comment,
            ]);

            return [
                'from_user_id' => $fromUser->id,
                'from_user_balance' => (float) $fromUser->balance,
                'to_user_id' => $toUser->id,
                'to_user_balance' => (float) $toUser->balance,
                'withdraw_transaction_id' => $outTransaction->id,
                'deposit_transaction_id' => $inTransaction->id,
            ];
        });
    }

    /**
     * Сервис получение баланса пользователя.
     *
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public function getBalance(int $userId): array
    {
        $user = User::find($userId);

        if (!$user) {
            throw new Exception('Пользователь не найден ...', 404);
        }

        return [
            'user_id' => $user->id,
            'balance' => (float) $user->balance,
            'user_name' => $user->name,
            'user_email' => $user->email,
        ];
    }

    /**
     * Валидация суммы.
     *
     * @param float $amount
     * @throws Exception
     */
    private function validateAmount(float $amount): void
    {
        if ($amount < self::MIN_AMOUNT) {
            throw new Exception('Введена некорректная сумма', 422);
        }

        if (!is_finite($amount) || $amount <= 0) {
            throw new Exception('Введена некорректная сумма', 422);
        }
    }
}
