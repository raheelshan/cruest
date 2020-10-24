<?php

namespace App\Http\Requests;


namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use App\Core\Response;
use Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class BaseRequest extends FormRequest
{
   protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
   {
      $response = new Response();
      $response->valid = false;
      $response->errors = $validator->errors();

      if(Request::wantsJson()) { 
         throw new HttpResponseException(
            response()->json($response, 200)
         );    
      } else {
         throw (new ValidationException($validator))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());  
      }
   }

   protected function failedAuthorization()
   {
      $response = new Response();
      $response->authorize = false;

      if(Request::wantsJson()) { 
         throw new HttpResponseException(
            response()->json($response, 200)
         );
      } else {
         throw new AuthorizationException;         
      }     
   }   
}