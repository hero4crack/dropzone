<?php
class DiscordAuth {
    private $clientId = '1441099329069777098';
    private $clientSecret = 'ijtwfQjDRzGsIKHEGwlFoAjLMkDT1uBU';
    private $redirectUri = 'http://localhost/dropzone/dropzone-login/auth/callback.php';
    private $apiEndpoint = 'https://discord.com/api/v10';
    
    public function getAuthUrl() {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'identify email'
        ];
        
        return 'https://discord.com/oauth2/authorize?' . http_build_query($params);
    }
    
    public function getAccessToken($code) {
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'identify email'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint . '/oauth2/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    public function getUserData($accessToken) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint . '/users/@me');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
?>