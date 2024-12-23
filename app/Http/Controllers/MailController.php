<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class MailController extends Controller
{
    public function sendCertificate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'certificate' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'number' => 'nullable|string',
            'idcert' => 'nullable|string',
        ]);

        $currentNumber = $data['number'];
        $currentID = $data['idcert'];

        if (is_null($currentNumber)) {
            $currentNumber = DB::table('certificates')->value('current_number');
        }

        if (is_null($currentID)) {
            $currentID = DB::table('certificates')->value('current_id');
        }

        $data['number'] = $currentNumber;
        $data['idcert'] = $currentID;
    
        DB::table('certificates')->update(['current_number' => $currentNumber + 1]);
        DB::table('certificates')->update(['current_id' => $currentID]);
    
        $certificatePath = $request->hasFile('certificate')
            ? $this->generateCertificate(storage_path('app/public/' . $request->file('certificate')->store('certificates', 'public')), $data['name'], $data['number'], $data['idcert'])
            : $this->generateCertificate(public_path('Brurur_Certificate.png'), $data['name'], $data['number'], $data['idcert']);
        
                
        $exploded = explode('/', $certificatePath)[1];
        Log::info($exploded);

        $filePath = $this->download();

        Mail::to($data['email'])->send(new TestMail([
            'title' => 'Congratulations ' . $data['name'],
            'body' => 'http://127.0.0.1:8000/certificates/' . $exploded,
            'attachment' => $filePath,
        ]));
    
        // Optional: Clean up the temporary file after sending the email
        unlink($filePath);
    
        return redirect()->back()->with('success', 'Your Certificate has been Sent!');
    }    

    protected function generateCertificate($imagePath, $name, $number, $idcert)
    {
        $certificatenumber = $number . $idcert;

        // Load the provided image (uploaded or default)
        $img = imagecreatefromstring(file_get_contents($imagePath));
    
        // Allocate a color for the text (black in this case)
        $textColor = imagecolorallocate($img, 0, 0, 0); // RGB for black
    
        // Specify the font size, angle, and y coordinate
        $fontSize = 48; // Font size
        $angle = 0; // No angle
        $y = 630; // Y coordinate
        $y2 = 830; // Y coordinate
    
        // Calculate the width of the text
        $textBox = imagettfbbox($fontSize, $angle, public_path('fonts/tahoma.ttf'), $name);
        $textWidth = abs($textBox[2] - $textBox[0]); // Width of the text

        $textBox2 = imagettfbbox($fontSize, $angle, public_path('fonts/tahoma.ttf'), $certificatenumber);
        $textWidth2 = abs($textBox2[2] - $textBox2[0]); // Width of the text
    
        // Get the image width
        $imgWidth = imagesx($img);
    
        // Calculate the x coordinate to center the text
        $x = ($imgWidth - $textWidth) / 2;
        $x2 = ($imgWidth - $textWidth2) / 2;
    
        // Add the name text to the image
        imagettftext($img, $fontSize, $angle, $x, $y, $textColor, public_path('fonts/tahoma.ttf'), $name);
        imagettftext($img, $fontSize, $angle, $x2, $y2, $textColor, public_path('fonts/tahoma.ttf'), $certificatenumber);
    
        // Save the modified certificate to a new file
        $certificateDir = public_path('certificates');
        if (!file_exists($certificateDir)) {
            mkdir($certificateDir, 0755, true);
        }
    
        $certificatePath = $certificateDir . '/' . uniqid() . '_BRURURCOM_Certificate.png';
        if (!imagepng($img, $certificatePath)) {
            throw new \Exception("Failed to save image to: " . $certificatePath);
        }
        // Free up memory
        imagedestroy($img);

        return $certificatePath;
    }

    public function download()
    {
        // Define the file path
        $filePath = public_path('qr-code.png');

        // Generate and save the QR code
        file_put_contents(
            $filePath,
            QrCode::size(512)
        ->format('png')
        ->merge('/storage/app/inditechno_logo.jpg')
        ->errorCorrection('M')
        ->generate(
            'Hello, world!',
        ));

        return $filePath;
    }
}
