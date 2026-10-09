<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        $loc = get_name($this['location_id']);
        $lot = get_name($this['lot_id']);
        $pecah_code = pecah_code($this['product_id']);
        $id = $pecah_code[0];
        $code = $pecah_code[1];
        $name = $pecah_code[2];
        $expired = $this['itds_expired'];
        try {
            $expired = odoo_datetime($this['itds_expired'], 'Y.m.d');
        } catch (\Throwable $th) {
            // throw $th;
        }

        return [
            'id' => $id,
            'quantity' => $this['quantity'],
            'code' => $code,
            'name' => $name,
            'location' => $loc,
            'lot' => $lot,
            'expired' => $expired,
            'expired_ori' => $this['itds_expired'],

        ];
    }
}
