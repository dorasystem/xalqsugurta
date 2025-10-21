<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->paycom_transaction_id ?? $this->id,
            'time' => isset($this->paycom_time) ? (int) $this->paycom_time : 0,
            'amount' => (float) $this->amount,
            'account' => [
                'order_id' => $this->order_id ?? null,
            ],
            'create_time' => isset($this->paycom_time) ? (int) $this->paycom_time : 0,
            'perform_time' => isset($this->perform_time_unix) ? (int) $this->perform_time_unix : 0,
            'cancel_time' => isset($this->cancel_time) ? (int) $this->cancel_time : 0,
            'transaction' => (string) ($this->id ?? ''),
            'state' => isset($this->state) ? (int) $this->state : 0,
            'reason' => !empty($this->reason) ?  (int) $this->reason : null, // ✅ NULL qaytariladi agar bo‘sh bo‘lsa
        ];
    }
}
