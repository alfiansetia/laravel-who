<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PoResource extends JsonResource
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

        $akl = get_name($this['akl']);
        $pecah_code = pecah_code($this['product_id']);
        $id = $pecah_code[0];
        $code = $pecah_code[1];
        $name = $pecah_code[2];
        // if (count($this['product_id']) ?? [] > 0) {
        //     $id = $this['product_id'][0];
        //     if (is_array($this['product_id']) && isset($this['product_id'][1])) {
        //         $productString = $this['product_id'][1];
        //         preg_match('/\[(.*?)\] (.*)/', $productString, $matches);

        //         if (isset($matches[1])) {
        //             $code = $matches[1];
        //         }

        //         if (isset($matches[2])) {
        //             $name = $matches[2];
        //         }
        //     }
        // }
        // if (is_array($this['akl']) && count($this['akl']) ?? [] > 0) {
        //     if (isset($this['akl'][1])) {
        //         $akl = $this['akl'][1];
        //     }
        // }
        return [
            'id'        => $id,
            'quantity'  => $this['product_qty'],
            'qty_ri'    => $this['qty_received'],
            'code'      => $code,
            'name'      => $name,
            'akl'       => $akl,
        ];
    }
}
