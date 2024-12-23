<?php

namespace App\Http\Controllers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    public function show()
    {
        $data = QrCode::size(512)
            ->format('png')
            ->merge('/storage/app/inditechno_logo.jpg')
            ->errorCorrection('M')
            ->generate(
                'Hello, world!',
            );

        return response($data)
            ->header('Content-type', 'image/png');
    }
}

?>