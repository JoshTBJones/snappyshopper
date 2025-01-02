<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'organisation_uuid' => $this->organisation->uuid,
            'postcode' => $this->postcode->postcode,
            'name' => $this->name,
            'open' => $this->open,
            'max_delivery_distance' => $this->max_delivery_distance,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'distance' => $this->when(isset($this->distance), $this->distance),
            'categories' => CategoryResource::collection($this->categories),
        ];
    }
}
