<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class LotResource extends JsonResource
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

        $loc = get_name($this['location_id']);
        $lot = get_name($this['lot_id']);
        $pecah_code = pecah_code($this['product_id']);
        $id = $pecah_code[0];
        $code = $pecah_code[1];
        $name = $pecah_code[2];
        $expired = $this['itds_expired'];
        try {
            $expired = Carbon::createFromFormat('Y-m-d H:i:s', $this['itds_expired'], 'UTC')
                ->setTimezone(config('app.timezone'))
                ->format('Y.m.d');
        } catch (\Throwable $th) {
            //throw $th;
        }
        return [
            'id'        => $id,
            'quantity'  => $this['quantity'],
            'code'      => $code,
            'name'      => $name,
            'location'  => $loc,
            'lot'       => $lot,
            'expired'   => $expired,
            'expired_ori'   => $this['itds_expired'],

        ];
    }
}
