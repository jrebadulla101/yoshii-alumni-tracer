<?php
header('Content-Type: application/json');

// OCR.Space API Key - Replace with your actual API key
$apiKey = 'K89629484988957'; // Get your free API key from https://ocr.space/ocrapi

// Get the form data
$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';

// Handle file upload
if (isset($_FILES['document_upload'])) {
    $file = $_FILES['document_upload'];
    $uploadDir = 'uploads/';
    $tempName = uniqid() . '_' . basename($file['name']);
    $uploadPath = $uploadDir . $tempName;

    // Create uploads directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Prepare image for OCR.Space API
        $imageData = base64_encode(file_get_contents($uploadPath));

        // Call OCR.Space API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.ocr.space/parse/image');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'apikey' => $apiKey,
            'base64Image' => 'data:image/jpeg;base64,' . $imageData,
            'language' => 'eng',
            'detectOrientation' => 'true',
            'scale' => 'true',
            'OCREngine' => '2'
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        // Delete the temporary file
        unlink($uploadPath);

        if ($result) {
            $ocrResult = json_decode($result, true);
            
            if (isset($ocrResult['ParsedResults'][0]['ParsedText'])) {
                $extractedText = $ocrResult['ParsedResults'][0]['ParsedText'];
                
                // Convert everything to lowercase for case-insensitive comparison
                $extractedText = strtolower($extractedText);
                $firstName = strtolower($firstName);
                $lastName = strtolower($lastName);

                // Check if both first name and last name are found in the extracted text
                $nameFound = strpos($extractedText, $firstName) !== false && 
                            strpos($extractedText, $lastName) !== false;

                echo json_encode([
                    'success' => true,
                    'verified' => $nameFound,
                    'message' => $nameFound ? 'ID Verified Successfully!' : 'Name Mismatch! Verification Failed.'
                ]);
                exit;
            }
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Error processing the image. Please try again.'
        ]);
        exit;
    }
}

echo json_encode([
    'success' => false,
    'message' => 'Error uploading file. Please try again.'
]); 