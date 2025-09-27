<div class="login-container">
    <div class="login-title">Вход в аккаунт</div>
    <form method="post" id="loginForm" class="login-form">
        <div class="form-group">
            <label for="login">Почта или телефон:</label>
            <input type="text" id="login" name="login" required placeholder="Введите ваш email или телефон">
        </div>
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required placeholder="Введите ваш пароль">
        </div>
        <!-- Yandex SmartCaptcha -->
        <div class="form-group">
            <div id="captcha-container" class="smart-captcha"></div>
        </div>
        <input type="hidden" id="smartcaptcha_token" name="smartcaptcha_token" value="">
        <button type="button" id="loginSubmit">Войти</button>
        <div class="form-group">
            <p>Нет аккаунта? <a href="/registration">Зарегистрируйтесь</a></p>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginSubmit');
    const hiddenTokenInput = document.getElementById('smartcaptcha_token');
    let captchaWidget = null;

    function initCaptcha() {
        if (typeof window.smartCaptcha === 'undefined') {
            console.error('SmartCaptcha script not loaded');
            return;
        }

        captchaWidget = window.smartCaptcha.render('captcha-container', {
            sitekey: 'ysc1_WgFNdr2MAZyGw8eqEgDDqhcup2hu7cnGtnglO2j9186643f0',
            invisible: false,
            hideShield: false,
            callback: function(token) {
                hiddenTokenInput.value = token;
            }
        });

        if (typeof captchaWidget === 'object' && captchaWidget !== null) {
            window.captchaWidgetRef = captchaWidget;
        }
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', function(evt) {
            evt.preventDefault();

            const login = document.getElementById('login').value.trim();
            const password = document.getElementById('password').value;

            if (!login || !password) {
                alert('Заполните все поля');
                return;
            }

            if (!hiddenTokenInput.value) {
                alert('Пожалуйста, пройдите проверку "Я не робот"');
                return;
            }

            submitBtn.textContent = 'Вход...';
            submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('login', login);
            formData.append('password', password);
            formData.append('smartcaptcha_token', hiddenTokenInput.value);

            fetch('/config.php', {
                method: 'POST',
                body: formData
            })
            .then(resp => resp.json())
            .then(data => {
                if (data.success) {
                    alert('Вход выполнен успешно!');
                    window.location.href = '/profile';
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                    resetCaptcha();
                }
            })
            .catch(err => {
                console.error('Ошибка:', err);
                alert('Произошла ошибка: ' + err.message);
            })
            .finally(() => {
                submitBtn.textContent = 'Войти';
                submitBtn.disabled = false;
            });
        });
    }

    function resetCaptcha() {
        if (window.captchaWidgetRef && typeof window.captchaWidgetRef.reset === 'function') {
            try {
                window.captchaWidgetRef.reset();
                hiddenTokenInput.value = '';
            } catch (e) {
                console.error('Ошибка при сбросе капчи:', e);
                setTimeout(() => {
                    if (document.getElementById('captcha-container')) {
                        document.getElementById('captcha-container').innerHTML = '';
                        initCaptcha();
                    }
                }, 100);
            }
        } else {
            setTimeout(() => {
                if (document.getElementById('captcha-container')) {
                    document.getElementById('captcha-container').innerHTML = '';
                    initCaptcha();
                }
            }, 100);
        }
    }
    initCaptcha();
});
</script>
