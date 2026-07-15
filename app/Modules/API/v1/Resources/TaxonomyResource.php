<?php

namespace App\Modules\API\v1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxonomyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];

        // Optional extra fields that may exist
        if (isset($this->description)) {
            $data['description'] = $this->description;
        }

        if (isset($this->icon)) {
            $data['icon'] = $this->icon;
        }

        if (isset($this->sort_order)) {
            $data['sort_order'] = $this->sort_order;
        }

        if (isset($this->is_active)) {
            $data['is_active'] = (bool) $this->is_active;
        }

        if (isset($this->short_name)) {
            $data['short_name'] = $this->short_name;
        }

        if (isset($this->group_name)) {
            $data['group_name'] = $this->group_name;
        }

        return $data;
    }
}
