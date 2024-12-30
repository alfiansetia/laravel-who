<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        $pecah_code = pecah_code($this['product_id']);
        $id = $pecah_code[0];
        $code = $pecah_code[1];
        $name = $pecah_code[2];
        return [
            'id'        => $id,
            'quantity'  => $this['quantity'],
            'code'      => $code,
            'name'      => $name,
        ];
    }
}
