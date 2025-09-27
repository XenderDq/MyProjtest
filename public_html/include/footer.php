    <footer>
        <div class="footer-content">
            <p> 2025 Все права защищены</p>
        </div>
    </footer>
</body>
</html>
<script>

document.addEventListener('DOMContentLoaded', function() {
    const registrationForm = document.getElementById('registrationForm');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                alert('Пароли не совпадают!');
                return;
            }

            const formData = new FormData(registrationForm);
            formData.append('action', 'register');

            fetch('/config.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Регистрация успешна!');
                    checkAuthAndRedirect();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Произошла ошибка: ' + error.message);
            });
        });
    }
    function checkAuthAndRedirect() {
        fetch('/config.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=check_auth'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/profile';
            } else {
                setTimeout(() => window.location.href = '/profile', 1000);
            }
        });
    }
});
</script>