<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AtkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id'            => $this->id,
            'code'          => $this->code,
            'name'          => $this->name,
            'satuan'        => $this->satuan,
            'desc'          => $this->desc,
            'stok'          => $this->stok,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'transactions'  => $this->whenLoaded('transactions'),
        ];
    }
}
