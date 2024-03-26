<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HeartbeatResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'code'              => $this->code,
            'expired_at'        => $this->expired_at,
            'error_message'     => $this->error_message,
            'auto_heal_job_class' => $this->auto_heal_job_class,
        ];
    }
}
