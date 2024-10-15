<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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

        $code = '';
        $name = '';
        $id = '';
        $loc = '';
        $lot = '';
        if (count($this['product_id']) ?? [] > 0) {
            $id = $this['product_id'][0];
            if (isset($this['product_id'][1])) {
                $productString = $this['product_id'][1];
                preg_match('/\[(.*?)\] (.*)/', $productString, $matches);

                if (isset($matches[1])) {
                    $code = $matches[1];
                }

                if (isset($matches[2])) {
                    $name = $matches[2];
                }
            }
        }
        if (count($this['location_id']) ?? [] > 0) {
            if (isset($this['location_id'][1])) {
                $loc = $this['location_id'][1];
            }
        }
        if (count($this['lot_id']) ?? [] > 0) {
            if (isset($this['lot_id'][1])) {
                $lot = $this['lot_id'][1];
            }
        }
        return [
            'id'        => $id,
            'quantity'  => $this['quantity'],
            'code'      => $code,
            'name'      => $name,
            'location'  => $loc,
            'lot'       => $lot,
            'expired'   => $this['itds_expired'],
        ];
    }
}
