<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    text-decoration: none;
    color: inherit; 
}
body {
    font-family: 'Arial', sans-serif;
    line-height: 1.6;
}
.non_find {
    display: flex;
    justify-content: center;
    font-size: 70px;
    font-style: normal;
    margin-top: 200px;
    margin-left: 20px;
    margin-right: 20px;
    color: #b61616ff
}
.hel_main {
    display: flex;
    justify-content: center;
    font-size: 36px;
    font-style: normal;
    margin-top: 200px;
    color: #1d9927ff
}
.nav_header--menu {
    display: flex;
    justify-content: center;
    font-size: 24px;
    font-style: normal;
    gap: 30px;
    margin-top: 30px;
}
.reg_title {
    display: flex;
    justify-content: center;
    font-size: 41px;
    margin-top: 30px;
    margin-bottom: 30px;
}
.main_form {
    display: flex;
    flex-direction: column;
    flex-wrap: wrap;
    align-content: center;
    justify-content: center;
    align-items: center;
}
.form-group {
    margin: 15px;
    width: 600px;
}
label {
    display: block;
    margin-bottom: 5px;
}
input[type="text"],
input[type="tel"],
input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
}
button {
    margin-top:30px;
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    cursor: pointer;
    display: flex;
    justify-content: center;
}
button:hover {
    background-color: #45a049;
}
.footer-content {
    display: flex;
    justify-content: center;
    font-size: 60px;
    margin-top: 300px;
}
.login-container {
    margin-top: 200px;
    display: flex;
    flex-direction: column;
    flex-wrap: wrap;
    align-content: center;
    justify-content: center;
    align-items: center;
}
#loginForm {
    display: flex;
    flex-wrap: wrap;
    align-content: center;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
.login-title {
    display: flex;
    justify-content: center;
    font-size: 41px;
    margin-top: 30px;
    margin-bottom: 30px;
}
</style>

<?php
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = trim($path, '/');

$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>

<html lang="ru">
<head>
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
    <script src="/include/assets/JS/script.js"></script>
    <title><?= $path ?: 'Главная' ?></title>
</head>
<body>
    <header>
        <nav class="nav_header">
            <div class="nav_header--menu">
                <div class="nav_header_menu-item"><a href="/">Главная</a></div>
                <?php if ($isLoggedIn): ?>
                    <div class="nav_header_menu-item"><a href="/profile">Профиль</a></div>
                    <div class="nav_header_menu-item">
                        <span>Привет, <?php echo htmlspecialchars($userName); ?>!</span>
                    </div>
                    <div class="nav_header_menu-item"><a href="/logout">Выйти</a></div>
                <?php else: ?>
                    <div class="nav_header_menu-item"><a href="/registration">Регистрация</a></div>
                    <div class="nav_header_menu-item"><a href="/login">Логин</a></div>
                <?php endif; ?>
            </div>
        </nav>
    </header>