<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserRequestResource extends JsonResource
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
            'id'                 => $this->id,
            'booking_id'         => $this->booking_id,
            'user_id'            => $this->user_id,
            'provider_id'        => $this->provider_id,
            'current_provider_id'=> $this->current_provider_id,
            'service_type_id'    => $this->service_type_id,
            'before_image'       => $this->before_image,
            'before_comment'     => $this->before_comment,
            'after_image',
            'after_comment',
            'promocode_id',
            'geo_fencing_id',
            'geo_time',
            'status',
            'cancelled_by',
            'cancel_reason',
            'night_fare',
            'payment_mode',
            'paid',
            'is_track',
            'estimated_fare',
            'distance',
            'travel_time',
            'invoice_item',
            'unit',
            'otp',
            's_address',
            's_latitude',
            's_longitude',
            'd_address',
            'd_latitude',
            'd_longitude',
            'track_distance'   => $this->track_distance,
            'track_latitude'   => $this->track_latitude,
            'track_longitude'  => $this->track_longitude,
            'destination_log'  => $this->destination_log,
            'is_drop_location' => $this->is_drop_location,
            'is_instant_ride'  => $this->is_instant_ride,
            'is_dispute'       => $this->is_dispute,
            'assigned_at'      => $this->assigned_at,
            'schedule_at'      => $this->schedule_at,
            'started_at'       => $this->started_at,
            'finished_at'      => $this->finished_at,
            'is_scheduled'     => $this->is_scheduled,
            'user_rated'       => $this->user_rated,
            'provider_rated'   => $this->provider_rated,
            'use_wallet'       => $this->use_wallet,
            'surge'            => $this->surge,
            'route_key'        => $this->route_key,
            'nonce'            => $this->nonce,
            'broadcast'        => $this->broadcast,
            'invoice_email'    => $this->invoice_email,
            'ride_option'      => $this->ride_option,
            'type'             => $this->type,
            'user'             => $this->user,
            'provider',
            'provider_service',
            'service_type'     => ServiceTypeResource::collection($this->childrenRecursive ?? null),
            'rating',
            'payment',
            'ride_otp',
            'ride_toll',
            'reasons',
        ];
    }
}
