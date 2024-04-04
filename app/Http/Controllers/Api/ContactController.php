<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function messager(Request $request){

        $data = $request->all();


        $validator = Validator::make($data, [
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string'
        ],[
            'email.required' => 'La mail e obbligatoria ',
            'email.email' => 'La mail non e valida',
            'subject.required' => 'L\' ogetto e obligatorio',
            'message.reqired' => 'Il messaggio e obbligatorio'
        ]);

        if($validator->fails()){

            $errors = [];
            foreach($validator->errors()->messages() as $field => $message){
                $errors[$field] = $message[0];
            }
            return response()->json(compact('errors'), 422);
        }

        $mail = new ContactMessageMail(
            $data['email'],
            $data['subject'],
            $data['message']
        );
        Mail::to(env('MAIL_TO_ADDRESS'))->send($mail);


        return response(null, 204);
    }
}
