<?php
$name = $_SESSION['user_name'] ?? '';
$email = $_SESSION['user_email'] ?? '';
$phone = $_SESSION['user_phone'] ?? '';
?>

<div class="profile-container">
    <h1>Личный кабинет</h1>
    <form id="profileForm" class="profile-form">
        <div class="form-group">
            <label for="name">Имя:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Телефон:</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Почта:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <div class="form-group">
            <label for="current_password">Текущий пароль (для смены пароля):</label>
            <input type="password" id="current_password" name="current_password" placeholder="Введите текущий пароль">
        </div>

        <div class="form-group">
            <label for="new_password">Новый пароль:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Введите новый пароль">
        </div>

        <div class="form-group">
            <label for="confirm_password">Повторите новый пароль:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Повторите новый пароль">
        </div>

        <button type="submit" class="save-button">Сохранить изменения</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(profileForm);
            formData.append('action', 'profile_update');

            fetch('/config.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Данные обновлены успешно!');
             
                    window.location.reload();
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

    const logoutLink = document.getElementById('logoutLink');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            if (!confirm('Вы уверены, что хотите выйти?')) {
                e.preventDefault();
            }
        });
    }
});
</script>