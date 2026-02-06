<?php

use App\Events\NotifyUser;
use App\Models\GeneralSetting;
use App\Models\Notification;
use Illuminate\Support\Str;
use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Twilio\Rest\Client;

function allSetting($array = NULL)
{
    if (!isset($array[0])) {
        $allSettings = GeneralSetting::get();
        if ($allSettings) {
            $output = [];
            foreach ($allSettings as $setting) {
                $output[$setting->slug] = $setting->value;
            }
            return $output;
        }
        return FALSE;
    } else if (is_array($array)) {
        $allSettings = GeneralSetting::whereIn('slug', $array)->get();
        if ($allSettings) {
            $output = [];
            foreach ($allSettings as $setting) {
                $output[$setting->slug] = $setting->value;
            }
            return $output;
        }
        return FALSE;
    } else {
        $allSettings = GeneralSetting::where(['slug' => $array])->first();
        if ($allSettings) {
            $output = $allSettings->value;
            return $output;
        }
        return FALSE;
    }
}


function setEnvironmentValue(array $values)
{
    $envFile = app()->environmentFilePath();
    $str = file_get_contents($envFile);
    $str .= "\r\n";

    if (count($values) > 0) {
        foreach ($values as $envKey => $envValue) {
            $keyPosition = strpos($str, "$envKey=");
            $endOfLinePosition = strpos($str, "\n", $keyPosition);
            $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

            if (is_bool($keyPosition) && $keyPosition === false) {
                // variable doesnot exist
                $str .= "$envKey=$envValue";
                $str .= "\r\n";
            } else {
                // variable exist
                $str = str_replace($oldLine, "$envKey=$envValue", $str);
            }
        }
    }
    $str = substr($str, 0, -1);
    if (!file_put_contents($envFile, $str)) {
        return false;
    }
    app()->loadEnvironmentFrom($envFile);
    Artisan::call('optimize:clear');
    return true;
}


function errorResponse($message = null, $status = 400, $data = null,)
{
    $message = $message ? $message :  'Something went wrong';
    $response  = ['error' => true, 'status' => $status, 'msg' => $message, 'data' =>  $data];
    return response()->json($response, $status);
}

function successResponse($message = null, $data = null, $status = 200)
{
    $message = $message ? $message :  'success';
    $response  = ['error' => false, 'status' => $status, 'msg' => $message, 'data' =>  $data];
    return response()->json($response, $status);
}

function otpVerify($email = null, $otp = null, $type = null)
{

    $message = 'Done';
    $status = true;
    // $otp = Otp::where(['otp' => $request->otp, 'phone' => str_replace(' ', '', $request->phone)])->first();
    $otp = Otp::where(['otp' => str_replace(' ', '', $otp), 'email' => $email])->first();

    if (empty($otp)) {
        return [
            'message' => __("OTP is not valid"),
            'status' => false
        ];
    }
    if (!$otp->type === $type) {
        return [
            'message' => __("OTP is not valid"),
            'status' => false
        ];
    }

    if (Carbon::now() > Carbon::parse($otp->expired_at)) {
        return [
            'message' => __("OTP has been expired"),
            'status' => false
        ];
    }
    if ($status == true) {
        $otp->delete();
    }

    return [
        'message' => $message,
        'status' => $status
    ];
}

/**
 * array response
 */

function errorReturn($message = null, $status = 400, $data = null,)
{
    $message = $message ? $message :  __('wrong_message');
    return  ['success' => false, 'status' => $status, 'message' => $message, 'data' =>  $data];
}

function successReturn($message = null, $data = [], $status = 200)
{
    $message = $message ? $message :  __('success_message');
    return ['success' => true, 'status' => $status, 'message' => $message, 'data' =>  $data];
}



// Send Mail
function sendOtp($email,$sitedata, $otp){
    $data = array('name'=>$sitedata->title, 'email' => $sitedata->email,'phone' => $sitedata->phone,'otp' => $otp);
    Mail::send('emails.sendOtp', $data, function($message) use ($email) {
       $message->to($email, env('APP_NAME'))->subject
          ('Verification OTP');
       $message->from($email, env('APP_NAME'));
    });
 }


/**
 * File upload
 */
function fileUploadAWS($file, $path, $old_file = null)
{
    try {
        $path = Storage::disk('s3')->put($path, $file, 'public');
        $url = Storage::disk('s3')->url($path);
        // return ["url" => $url,"status" => true];
        if($old_file != null){
            $file = explode(allSetting('AWS_URL'), $old_file);
            if(isset($file[1])){
                Storage::disk('s3')->delete($file[1]);
            }

        }
        return $url;
    } catch (Exception $e) {
        // return ["status" => false, "message" => $e->getMessage()];
        return $e->getMessage() ;
    }
}


function fileRemoveAWS($path){
    if(Storage::disk('s3')->delete($path)){
        return true;
    }else{
        return false;
    }
}

function fileUploadLocal($file, $path, $old_file = null)
{
    try {
        if (!file_exists(public_path($path))) {
            mkdir(public_path($path), 0777, true);
        }
        $file_name = time() . '_' . randomNumber(16) . '_' . $file->getClientOriginalName();
        $destinationPath = public_path($path);

        $file_name = str_replace(' ','_',$file_name);
        # old file delete
        if ($old_file) {
            removeFileLocal($path, $old_file);
        }
        # resize image
        // if (filesize($file) / 1024 > 2048) {

        //     // enable extension=gd2
        //     // $file->orientate(); //so that the photo does not rotate automatically

        //     Image::make($file)->orientate()->save($destinationPath . $file_name, 60);
        //     // quality = 60 low, 75 medium, 80 original
        // } else {
        //     #original image upload
        //     $file->move($destinationPath, $file_name);
        // }

        $file->move($destinationPath, $file_name);

        return $file_name;
    } catch (Exception $e) {
        return null;
    }
}


function removeFileLocal($path, $old_file)
{
    $url =  public_path($path);
    $old_file_name = str_replace($url . '/', '', $old_file);

    if (isset($old_file) && $old_file != "" && file_exists($path . $old_file_name)) {
        unlink($path . $old_file_name);
    }
    return true;

}



/**
 * Random number
 */
function randomNumber($a = 10)
{
    $x = '0123456789';
    $c = strlen($x) - 1;

    $z = rand(1, $c);       # first number never taken 0

    for ($i = 0; $i < $a - 1; $i++) {
        $y = rand(0, $c);
        $z .= substr($x, $y, 1);
    }

    return $z;
}

/**
 * unique slug for products
 */
function slug($mode, $name, $id = null)
{
    $slugInc = null;
    $productData['slug'] = Str::slug($name);

    do {
        $productData['slug'] = $slugInc ? Str::slug($name . '_' . $slugInc) : Str::slug($name);
        if ($id) {
            $existSlug = $mode::where('slug', $productData['slug'])->where('id', '!=', $id)->exists();
        } else {
            $existSlug = $mode::where('slug', $productData['slug'])->exists();
        }
        if ($slugInc >= 1) {
            $slugInc++;
        } else {
            $slugInc = 1;
        }
    } while ($existSlug);

    return  $productData['slug'];
}

// Send mail
function sendMailToUser( $to ,$message,$subject = null)
{
    if (is_array($to)) {
        $success = true;
        foreach ($to as $key => $value) {
            if(!$subject){
                $subject = "Send From" . env('APP_NAME');
            }
            $data = array('name' => "Homestick", 'email' => $value,'subject' => $subject, 'text' => $message);
            try {
                Mail::send('marketing::Emails.mail', $data, function ($message) use ($value, $subject) {
                    $message->to($value, env('APP_NAME'))->subject($subject);
                    $message->from('support@'.env('APP_NAME').'.com', env('APP_NAME'));
                });
                $success = true;
            } catch (Exception $exception) {
                $success = $exception->getMessage();
            }
        }
        return $success;

    }elseif (is_string($to)) {
        if(!$subject){
            $subject = "Send From" . env('APP_NAME');
        }

        $data = array('name' => "Homestick", 'email' => $to,'subject' => $subject, 'text' => $message);
        try {
            Mail::send('marketing::Emails.mail', $data, function ($message) use ($to, $subject) {
                $message->to($to, env('APP_NAME'))->subject($subject);
                $message->from('support@'.env('APP_NAME').'.com', env('APP_NAME'));
            });
            return true;
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }
}

// twilio sms
// function sendMessages($message, $recipients)
// {
//     if (is_array($recipients)) {
//         $success = true;
//         foreach ($recipients as $key => $value) {
//            if(sendMessages($message, $value)){
//                $success = true;
//            }else{
//                $success = false;
//            }
//         }
//         return $success;
//     } elseif (is_string($recipients)) {
//         sendMessage($message, $recipients);
//     }
// }



// Send Message
// function sendMessage($message, $recipients)
// {

//     try {
//         $account_sid = allSetting("TWILIO_SID");
//         $auth_token = allSetting("TWILIO_AUTH_TOKEN");
//         $twilio_number = allSetting("TWILIO_NUMBER");
//         $client = new Client($account_sid, $auth_token);

//         $client->messages->create(
//             $recipients,
//             ['from' => $twilio_number, 'body' => $message]
//         );
//         return true;
//     } catch (Exception $exception) {
//         // generalErrorLog($exception);
//         return false;
//     }
// }

// function sendWhatsappMessage($message, $recipients)
// {
//     try {
//         $account_sid = allSetting("TWILIO_SID");
//         $auth_token = allSetting("TWILIO_AUTH_TOKEN");
//         $twilio_number = allSetting("TWILIO_NUMBER");
//         $client = new Client($account_sid, $auth_token);

//         $client->messages->create(
//             "whatsapp:" . $recipients,
//             ['from' => "whatsapp:" . $twilio_number, 'body' => $message]
//         );
//         return true;
//     } catch (Exception $exception) {
//         generalErrorLog($exception);
//         return false;
//     }
// }

// Send notification

function SendNotification($user_id, $title, $type, $image)
{

    try {
        // create notification
        $notification = Notification::create([
            'user_id' => $user_id,
            'title' => $title,
            'type' => $type,
            'image' => $image,  
        ]);
        $id = $notification->id;
        $message = $notification->title;
        $user_id = $notification->user_id;
        $type = $notification->type;
        $image = $notification->image;
        $date = $notification->created_at;
    
        // Trigger the event
        broadcast(new NotifyUser($id, $message, $user_id, $type, $image, $date));
    
        return true;
    } catch (Exception $exception) {
        // return ["status" => false, "message" => $exception->getMessage()];
        return $exception->getMessage() ;
    }
    
}