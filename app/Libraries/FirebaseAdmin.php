<?php

namespace App\Libraries;

/**
 * Zero-dependency Firebase Admin helper.
 *
 * Handles two jobs with only a Firebase service-account JSON:
 *   1. Mint a Firebase Auth custom token so the portal widget can
 *      signInWithCustomToken(...) and satisfy stricter Firestore rules.
 *   2. Mint a Google OAuth2 access token and use it against:
 *        - FCM HTTP v1      (push notifications)
 *        - Firestore REST   (read a client's chat doc to fetch their FCM token)
 *
 * Place the service account JSON at app/Config/firebase-service-account.json
 * and make sure it stays OUT of version control.
 */
class FirebaseAdmin
{
    /** @var array parsed service-account JSON */
    private $sa;

    /** @var string|null cached OAuth access token */
    private static $cachedToken = null;
    /** @var int|null expiry epoch for cached token */
    private static $cachedTokenExp = null;

    public function __construct()
    {
        $path = APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'firebase-service-account.json';
        if (!is_file($path)) {
            throw new \RuntimeException(
                'Firebase service account not found at ' . $path .
                '. Download it from Firebase console → Project settings → Service accounts → Generate new private key.'
            );
        }
        $raw = file_get_contents($path);
        $this->sa = json_decode($raw, true);
        if (!$this->sa || empty($this->sa['client_email']) || empty($this->sa['private_key'])) {
            throw new \RuntimeException('Service account JSON is malformed.');
        }
    }

    /**
     * Returns true if the service account file is present and parseable.
     * The widget uses this as a feature flag — if false, custom-token sign-in
     * and server-push are silently disabled, and the widget still works in
     * "open rules" mode.
     */
    public static function isConfigured(): bool
    {
        $path = APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'firebase-service-account.json';
        return is_file($path) && is_array(json_decode((string) @file_get_contents($path), true));
    }

    public function projectId(): string
    {
        return $this->sa['project_id'];
    }

    // -------------------------------------------------------------------
    // 1. Custom token for signInWithCustomToken
    // -------------------------------------------------------------------

    /**
     * Mint a Firebase Auth custom token.
     *
     * @param string $uid       stable identity (we use "admin:<id>" / "mentor:<id>")
     * @param array  $claims    extra claims pushed into the JWT; showed on Firestore
     *                          rules via request.auth.token.<claim>
     */
    public function createCustomToken(string $uid, array $claims = []): string
    {
        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $payload = [
            'iss'    => $this->sa['client_email'],
            'sub'    => $this->sa['client_email'],
            'aud'    => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
            'iat'    => $now,
            'exp'    => $now + 3600, // max allowed
            'uid'    => $uid,
        ];
        if (!empty($claims)) {
            $payload['claims'] = $claims;
        }
        return $this->signJwt($header, $payload);
    }

    // -------------------------------------------------------------------
    // 2. OAuth2 access token (for FCM v1 + Firestore REST)
    // -------------------------------------------------------------------

    /**
     * Fetch (and cache) a Google OAuth2 access token via service-account JWT
     * grant. Token valid ~1h, cached in static memory.
     */
    public function accessToken(array $scopes): string
    {
        $scopeKey = implode(' ', $scopes);
        if (self::$cachedToken && self::$cachedTokenExp && self::$cachedTokenExp > time() + 60) {
            return self::$cachedToken;
        }

        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $payload = [
            'iss'   => $this->sa['client_email'],
            'scope' => $scopeKey,
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ];
        $assertion = $this->signJwt($header, $payload);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $assertion,
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $body = json_decode((string) $res, true) ?: [];
        if ($code !== 200 || empty($body['access_token'])) {
            throw new \RuntimeException('OAuth token exchange failed: ' . substr((string) $res, 0, 300));
        }
        self::$cachedToken = $body['access_token'];
        self::$cachedTokenExp = $now + (int) ($body['expires_in'] ?? 3600);
        return self::$cachedToken;
    }

    // -------------------------------------------------------------------
    // 3. Firestore REST — read a chat doc to pull the client's FCM token
    // -------------------------------------------------------------------

    public function getChatDocToken(string $clientId): ?string
    {
        try {
            $tok = $this->accessToken(['https://www.googleapis.com/auth/datastore']);
            $url = 'https://firestore.googleapis.com/v1/projects/' . urlencode($this->projectId()) .
                   '/databases/(default)/documents/chat/' . urlencode($clientId);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tok]);
            $res = curl_exec($ch);
            curl_close($ch);
            $body = json_decode((string) $res, true) ?: [];
            $fields = $body['fields'] ?? [];
            if (isset($fields['token']['stringValue'])) {
                $t = trim($fields['token']['stringValue']);
                return $t !== '' ? $t : null;
            }
            return null;
        } catch (\Throwable $e) {
            log_message('warning', 'getChatDocToken failed: ' . $e->getMessage());
            return null;
        }
    }

    // -------------------------------------------------------------------
    // 4. FCM HTTP v1 — send a push notification
    // -------------------------------------------------------------------

    public function sendFcm(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        if ($deviceToken === '') return false;
        try {
            $tok = $this->accessToken(['https://www.googleapis.com/auth/firebase.messaging']);
            $url = 'https://fcm.googleapis.com/v1/projects/' . urlencode($this->projectId()) . '/messages:send';
            $notificationPayload = ['title' => $title, 'body' => $body];
            if (!empty($data['image_url'])) {
                $notificationPayload['image'] = $data['image_url'];
            }

            $messagePayload = [
                'token' => $deviceToken,
                'notification' => $notificationPayload,
                'data' => array_map('strval', $data),
                'android' => ['priority' => 'HIGH'],
            ];

            if (!empty($data['image_url'])) {
                $messagePayload['apns'] = [
                    'payload' => [
                        'aps' => ['mutable-content' => 1]
                    ],
                    'fcm_options' => [
                        'image' => $data['image_url']
                    ]
                ];
            }

            $payload = [
                'message' => $messagePayload,
            ];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $tok,
                'Content-Type: application/json',
            ]);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code >= 200 && $code < 300) return true;
            log_message('warning', 'FCM send failed (' . $code . '): ' . substr((string) $res, 0, 300));
            return false;
        } catch (\Throwable $e) {
            log_message('warning', 'sendFcm failed: ' . $e->getMessage());
            return false;
        }
    }

    // -------------------------------------------------------------------
    // JWT signer (RS256) — pure PHP with openssl_sign
    // -------------------------------------------------------------------

    private function signJwt(array $header, array $payload): string
    {
        $b64 = function ($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        };
        $segments = $b64(json_encode($header, JSON_UNESCAPED_SLASHES))
                  . '.' . $b64(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $key = openssl_pkey_get_private($this->sa['private_key']);
        if (!$key) {
            throw new \RuntimeException('Failed to parse service account private_key.');
        }
        $sig = '';
        if (!openssl_sign($segments, $sig, $key, 'sha256')) {
            throw new \RuntimeException('openssl_sign failed.');
        }
        return $segments . '.' . $b64($sig);
    }
}
