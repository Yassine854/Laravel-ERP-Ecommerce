<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'blocked' => $this->blocked,
            'subdomain'=>$this->subdomain,
            'tel'=>$this->tel,
            'image'=>$this->image,
            'city'=>$this->city,
            'address'=>$this->address,
            'zip'=>$this->zip,
            'pack_id' => $this->pack_id,
            'offre_id' => $this->offre_id,
            'parametre_id' => $this->parametre_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
