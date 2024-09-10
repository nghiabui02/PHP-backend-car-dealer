<?php

namespace App\Common;

use Google\Cloud\Storage\Bucket;

class Common
{
    public static function getBrandImageUrl(string $fileName, Bucket $bucket, array|\Illuminate\Http\UploadedFile|null $file): string
    {
        $filePath = 'logo_images/' . $fileName;
        $bucket->upload(file_get_contents($file), [
            'name' => $filePath
        ]);
        $getImage = $bucket->object($filePath);
        $getImage->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);
        return config('services.firebase.storage_url') . $bucket->name() . '/' . $filePath;
    }

}
