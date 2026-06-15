<?php

namespace App;

use App\Models\User;

class Auth
{
    const REMEMBER_ME_COOKIE = 'remember_me';
    const REMEMBER_ME_LIFETIME = 2592000;

    public static function login($user, $rememberMe = false)
    {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
        ];

        if ($rememberMe) {
            self::setRememberMeCookie($user);
        } else {
            self::clearRememberMeCookie();
        }
    }

    public static function rememberLogin()
    {
        if (isset($_SESSION['user']['id']) || empty($_COOKIE[self::REMEMBER_ME_COOKIE])) {
            return false;
        }

        $cookieData = self::parseRememberMeCookie($_COOKIE[self::REMEMBER_ME_COOKIE]);

        if ($cookieData === false || $cookieData['expires'] < time()) {
            self::clearRememberMeCookie();
            return false;
        }

        $user = User::getById($cookieData['user_id']);

        if (!$user) {
            self::clearRememberMeCookie();
            return false;
        }

        $expectedSignature = self::buildRememberMeSignature($user, $cookieData['user_id'], $cookieData['expires']);

        if (!hash_equals($expectedSignature, $cookieData['signature'])) {
            self::clearRememberMeCookie();
            return false;
        }

        self::login($user, true);

        return true;
    }

    public static function logout()
    {
        self::clearRememberMeCookie();

        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    private static function setRememberMeCookie($user)
    {
        $expires = time() + self::REMEMBER_ME_LIFETIME;
        $signature = self::buildRememberMeSignature($user, $user['id'], $expires);
        $value = $user['id'] . ':' . $expires . ':' . $signature;
        $params = session_get_cookie_params();

        setcookie(
            self::REMEMBER_ME_COOKIE,
            $value,
            $expires,
            $params['path'],
            $params['domain'],
            $params['secure'],
            true
        );
    }

    private static function clearRememberMeCookie()
    {
        $params = session_get_cookie_params();

        setcookie(
            self::REMEMBER_ME_COOKIE,
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            true
        );

        unset($_COOKIE[self::REMEMBER_ME_COOKIE]);
    }

    private static function parseRememberMeCookie($cookieValue)
    {
        $parts = explode(':', $cookieValue);

        if (count($parts) !== 3 || !ctype_digit($parts[0]) || !ctype_digit($parts[1])) {
            return false;
        }

        return [
            'user_id' => (int) $parts[0],
            'expires' => (int) $parts[1],
            'signature' => $parts[2],
        ];
    }

    private static function buildRememberMeSignature($user, $userId, $expires)
    {
        return hash_hmac('sha256', $userId . '|' . $expires, $user['password'] . $user['salt']);
    }
}
