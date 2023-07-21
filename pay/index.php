<?php

// Step 1: Register your application and obtain a client ID and client secret key

// Step 2: Redirect the user to JustiFi's authorization page
$client_id = 'test_OxyfpRmAFlS3gRzAj08JwCqNokI4kM6S';
$client_secret = 'test_Vay3djiuNg3_0yMjwugjWR1z7dS0KQJ_aQQjYB3-aehaJdOa9iGcamE1ftdlBmeg';
$redirect_uri = 'https://steelbng.com/check';
$authorization_url = 'https://api.justifi.ai/oauth/authorize?client_id=' . $client_id . '&redirect_uri=' . urlencode($redirect_uri) . '&response_type=code';

header('Location: ' . $authorization_url);

// Step 3: Exchange the authorization code for an access token
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $token_url = 'https://api.justifi.ai/oauth/token';
    $data = array(
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $redirect_uri
    );

    $options = array(
        'http' => array(
            'header' => "Authorization: Basic " . base64_encode("$client_id:$client_secret") . "\r\n" .
                        "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ),
    );

    $context = stream_context_create($options);
    $result = file_get_contents($token_url, false, $context);

    if ($result === false) {
        // Handle error
    } else {
        $response_data = json_decode($result, true);
        $access_token = $response_data['access_token'];

        // Step 4: Make an API request with the access token in the request header
        $url = 'https://api.justifi.ai/v1/payments';
        $headers = array(
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
            'Idempotency-Key: 497f6eca-6276-4993-bfeb-53cbbbba6f08',
            'Seller-Account: string',
            'Sub-Account: string'
        );
        $data = array(
            'amount' => 1000,
            'currency' => 'usd',
            'capture_strategy' => 'automatic',
            'email' => 'example@test.com',
            'description' => 'Charging $10 to the test card',
            'payment_method' => array(
                'card' => array(
                    'name' => 'Sylvia Fowles',
                    'number' => '4111111111111111',
                    'verification' => '123',
                    'month' => '3',
                    'year' => '2040',
                    'address_postal_code' => '55555'
                )
            )
        );
        $data_json = json_encode($data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);

        if ($response === false) {
            // Handle error
        } else {
            echo $response;
        }

        curl_close($curl);
    }
}

?>
