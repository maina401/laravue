<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'pages' => $this->pages,
            'roles' => $this->description,
//            'permissions' => array_map(
//                function ($permission) {
//                    return $permission['name'];
//                },
//                $this->getAllPermissions()->toArray()
//            ),
            'attachment' => 'https://i.pravatar.cc',
        ];
    }
}
