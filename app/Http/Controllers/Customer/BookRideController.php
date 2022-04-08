<?php

namespace App\Http\Controllers\Customer;

use App\Common\RideComplete;
use App\Http\Controllers\Controller;
use App\Models\BookRide;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookRideController extends Controller
{
    use ApiResponser;
    public function bookRide(Request $request){

        $validator = Validator::make($request->all(), [
            'pickup_loc' => 'required',
            'dest_loc' => 'required',
        ]);

        if($validator->fails()){
            return $this->error('Whoops! Something went wrong', $validator->errors(), 'null', 400);
        }else{
            $create = BookRide::create([
                'pickup_loc' => $request->pickup_loc,
                'dest_loc' => $request->dest_loc,
                'customer_id' => auth('sanctum')->user()->id,
                'is_ride_completed' => RideComplete::initiate
            ]);
            if($create){
                return $this->success('Ride initiated successfully. Waiting for captain to accept', null, 'null', 201);
            }else{
                return $this->error('Whoops! Something went wrong', null, 'null', 500);
            }
        }
    }
}
