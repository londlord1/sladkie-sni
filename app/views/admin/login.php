<?php /** @var string|null $error */ ?>
<section class="section">
    <div class="container narrow">
        <div class="login-card">
            <p class="eyebrow">Панель управления</p>
            <h1>Вход для администратора</h1>
            <p class="login-lead">Здесь можно просматривать заявки, одобрять и отклонять бронирования.</p>

            <?php if ($error !== null): ?>
                <div class="alert alert-error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('admin.login.post')) ?>">
                <?= csrf_field() ?>

                <div class="field">
                    <label for="login">Логин</label>
                    <input id="login" name="login" type="text"
                           value="<?= e($login ?? '') ?>"
                           autocomplete="username" required autofocus>
                </div>

                <div class="field">
                    <label for="password">Пароль</label>
                    <input id="password" name="password" type="password"
                           autocomplete="current-password" required>
                </div>

                <button class="btn btn-primary btn-block btn-large" type="submit">Войти</button>
            </form>

            <p class="login-note">Доступ только для сотрудников гостиницы.</p>
        </div>
    </div>
</section>
