<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'image_url' => $this->image_url,
            'show_prices' => $this->show_prices,
            'show_currency' => $this->show_currency,
            'show_calories' => $this->show_calories,
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
        ];
    }
}
