<?php
header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function verifySmartCaptcha($token, $secretKey) {
    $url = 'https://smartcaptcha.yandexcloud.net/validate';
    $data = [
        'secret' => $secretKey,
        'token' => $token,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    $post = http_build_query($data);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($errno) {
            error_log("cURL error verifySmartCaptcha: $err");
            return null;
        }
        return json_decode($result, true);
    }

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $post,
            'timeout' => 10
        ]
    ];

    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    if ($result === false) {
        error_log("verifySmartCaptcha file_get_contents failed");
        return null;
    }
    return json_decode($result, true);
}

class Database {
    private $host = 'localhost';
    private $db_name = 'raulcil9_3333';
    private $username = 'raulcil9_3333';
    private $password = 'xr&wSVk3OAQr';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Ошибка подключения: " . $exception->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
            exit;
        }
        return $this->conn;
    }
}

class User {
    private $conn;
    private $table_name = "users";
    public $id;
    public $name;
    public $email;
    public $phone;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($login, $password) {
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'phone';
        
        $query = "SELECT id, name, email, phone, password FROM " . $this->table_name . " WHERE {$field} = :login LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":login", $login);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->email = $row['email'];
                $this->phone = $row['phone'];
                return true;
            }
        }
        return false;
    }

    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function phoneExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE phone = :phone LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name, email=:email, phone=:phone, password=:password";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":password", $this->password);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name=:name, email=:email, phone=:phone WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }
}

class Validator {
    public static function validateName($name) {
        $errors = [];
        if (empty($name)) $errors[] = 'Имя обязательно';
        if (!empty($name) && strlen($name) < 2) $errors[] = 'Имя должно содержать минимум 2 символа';
        if (!empty($name) && strlen($name) > 50) $errors[] = 'Имя не должно превышать 50 символов';
        if (!empty($name) && !preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $name)) $errors[] = 'Имя может содержать только буквы, пробелы и дефисы';
        if (!empty($name) && trim($name) === '') $errors[] = 'Имя не может состоять только из пробелов';
        return $errors;
    }

    public static function validatePhone($phone) {
        $errors = [];
        if (empty($phone)) $errors[] = 'Телефон обязателен';
        if (!empty($phone) && !preg_match('/^\+?[0-9\s\-\(\)]{10,20}$/', $phone)) $errors[] = 'Некорректный формат телефона';
        if (!empty($phone) && strlen($phone) < 10) $errors[] = 'Телефон должен содержать минимум 10 цифр';
        if (!empty($phone) && strlen($phone) > 20) $errors[] = 'Телефон не должен превышать 20 символов';
        if (!empty($phone) && trim($phone) === '') $errors[] = 'Телефон не может состоять только из пробелов';
        return $errors;
    }

    public static function validateEmail($email) {
        $errors = [];
        if (empty($email)) $errors[] = 'Email обязателен';
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный формат email';
        if (!empty($email) && strlen($email) > 100) $errors[] = 'Email не должен превышать 100 символов';
        if (!empty($email) && trim($email) === '') $errors[] = 'Email не может состоять только из пробелов';
        if (!empty($email) && substr_count($email, '@') !== 1) $errors[] = 'Некорректный формат email';
        if (!empty($email) && strpos($email, '..') !== false) $errors[] = 'Некорректный формат email';
        if (!empty($email) && preg_match('/[<>]/', $email)) $errors[] = 'Email содержит недопустимые символы';
        return $errors;
    }

    public static function validatePassword($password, $confirm_password = null) {
        $errors = [];
        if (empty($password)) $errors[] = 'Пароль обязателен';
        if (!empty($password) && strlen($password) < 6) $errors[] = 'Пароль должен содержать минимум 6 символов';
        if (!empty($password) && strlen($password) > 50) $errors[] = 'Пароль не должен превышать 50 символов';
        if (!empty($password) && !preg_match('/[A-Z]/', $password)) $errors[] = 'Пароль должен содержать хотя бы одну заглавную букву';
        if (!empty($password) && !preg_match('/[a-z]/', $password)) $errors[] = 'Пароль должен содержать хотя бы одну строчную букву';
        if (!empty($password) && !preg_match('/[0-9]/', $password)) $errors[] = 'Пароль должен содержать хотя бы одну цифру';
        if (!empty($password) && $confirm_password !== null && $password !== $confirm_password) $errors[] = 'Пароли не совпадают';
        return $errors;
    }

    public static function validateLogin($login) {
        $errors = [];
        if (empty($login)) $errors[] = 'Поле логина обязательно';
        
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
        $isPhone = preg_match('/^\+?[0-9\s\-\(\)]{10,20}$/', $login);
        
        if (!empty($login) && !$isEmail && !$isPhone) {
            $errors[] = 'Введите корректный email или телефон';
        }
        
        return $errors;
    }
}

class AjaxHandler {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    private function setUserSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_phone'] = $user->phone;
        $_SESSION['logged_in'] = true;
    }

    public function handleRegistration($data) {
        $errors = [];

        $errors = array_merge($errors, Validator::validateName($data['name']));
        $errors = array_merge($errors, Validator::validateEmail($data['email']));
        $errors = array_merge($errors, Validator::validatePhone($data['phone']));
        $errors = array_merge($errors, Validator::validatePassword($data['password'], $data['confirm_password']));

        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }

        $this->user->email = $data['email'];
        $this->user->phone = $data['phone'];

        if ($this->user->emailExists()) {
            return ['success' => false, 'message' => 'Пользователь с таким email уже существует'];
        }

        if ($this->user->phoneExists()) {
            return ['success' => false, 'message' => 'Пользователь с таким телефоном уже существует'];
        }

        $this->user->name = $data['name'];
        $this->user->password = $data['password'];

        if ($this->user->create()) {
            if ($this->user->login($data['email'], $data['password'])) {
                $this->setUserSession($this->user);
                return ['success' => true, 'message' => 'Регистрация успешна!'];
            }
            return ['success' => true, 'message' => 'Регистрация успешна! Авторизуйтесь.'];
        } else {
            return ['success' => false, 'message' => 'Ошибка при создании пользователя'];
        }
    }

    public function handleLogin($data) {
        $login = $data['login'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($login) || empty($password)) {
            return ['success' => false, 'message' => 'Логин и пароль обязательны'];
        }

        $loginErrors = Validator::validateLogin($login);
        if (!empty($loginErrors)) {
            return ['success' => false, 'message' => implode(', ', $loginErrors)];
        }

        if ($this->user->login($login, $password)) {
            $this->setUserSession($this->user);
            return ['success' => true, 'message' => 'Вход выполнен успешно!'];
        } else {
            return ['success' => false, 'message' => 'Неверный логин или пароль'];
        }
    }

    public function handleProfileUpdate($data) {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Необходима авторизация'];
        }

        $this->user->id = $_SESSION['user_id'];
        $errors = [];

        $errors = array_merge($errors, Validator::validateName($data['name']));
        $errors = array_merge($errors, Validator::validateEmail($data['email']));
        $errors = array_merge($errors, Validator::validatePhone($data['phone']));

        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }

        $query = "SELECT id FROM users WHERE (email = :email OR phone = :phone) AND id != :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":phone", $data['phone']);
        $stmt->bindParam(":id", $this->user->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email или телефон уже используются другим пользователем'];
        }

        $this->user->name = $data['name'];
        $this->user->email = $data['email'];
        $this->user->phone = $data['phone'];

        if ($this->user->update()) {
            $_SESSION['user_name'] = $this->user->name;
            $_SESSION['user_email'] = $this->user->email;
            $_SESSION['user_phone'] = $this->user->phone;
            
            return ['success' => true, 'message' => 'Данные обновлены успешно!'];
        } else {
            return ['success' => false, 'message' => 'Ошибка при обновлении данных'];
        }
    }

    public function checkAuth() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (($action === 'login') && !isset($_POST['skip_captcha'])) {
        $captchaToken = $_POST['smartcaptcha_token'] ?? '';
        $captchaSecretKey = 'ysc2_WgFNdr2MAZyGw8eqEgDDDB4X38IIcKX9X8Lvu6WSe6298f38';

        if (empty($captchaToken)) {
            echo json_encode(['success' => false, 'message' => 'Токен капчи не получен']);
            exit;
        }

        $captchaResult = verifySmartCaptcha($captchaToken, $captchaSecretKey);

        if (!$captchaResult || !isset($captchaResult['status']) || $captchaResult['status'] !== 'ok') {
            echo json_encode(['success' => false, 'message' => 'Проверка капчи не пройдена']);
            exit;
        }
    }

    $handler = new AjaxHandler();
    switch ($action) {
        case 'register':
            $result = $handler->handleRegistration($_POST);
            echo json_encode($result);
            break;
        case 'login':
            $result = $handler->handleLogin($_POST);
            echo json_encode($result);
            break;
        case 'profile_update':
            $result = $handler->handleProfileUpdate($_POST);
            echo json_encode($result);
            break;
        case 'check_auth':
            $result = ['success' => $handler->checkAuth()];
            echo json_encode($result);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Метод не поддерживается']);
}
?>