<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Image;
class Banner extends Model
{
    /**
     * Define o escopo de ativo para os cupons
     */
    public function scopeMobile($query){
        return $query->where('device', 1);        
    }

    public function upload(Request $request)
    {
        $banner = Banner::where(["id" => $request->banner_id])->first();
        
        if(isset($banner)){
            $errImage = [];
            if ($request->image) {
                try{
                    $data = $request->input('image');
                    
                    $extension = explode('/', mime_content_type($data))[1];
        
                    $name = uniqid();
                    $filepath = '/images/banners/'.$name.'.'.$extension;
                    $data = $request->input('image');
                    $data = str_replace('data:image/png;base64,', '', $data);
                    $data = str_replace('data:image/jpeg;base64,', '', $data);
                    $data = str_replace('data:image/jpg;base64,', '', $data);
                    $data = str_replace(' ', '+', $data);
                    $data = base64_decode($data);
                    $filePutPath = public_path().$filepath;
                    file_put_contents($filePutPath, $data);
                    if (file_exists($filePutPath)) {
                        $img = Image::make($filePutPath);

                        $banner->img = $filepath;
                        $banner->save();
                    } else {
                        $errImage[] = 'banner';
                    }
                } catch(\Exception $e) {
                    $errImage[] = 'banner';
                }
            }

            if (sizeof($errImage) > 0) {
                return (object) ['error' => true, 'data' => $errImage];
            } else {
                $banner->save();
            }

            return (object) ['error' => false];
        }

        return false;
    }
    
}
