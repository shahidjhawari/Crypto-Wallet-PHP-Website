<?php require('top.php') ?>
<style>
    body {
        background: #070F2B;
        color: white;
    }

    .centered-form {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .form-container {
        width: 100%;
        max-width: 400px;
        padding: 20px;
        border: 1px solid #e3e3e3;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        background: #141E46;
    }
</style>

    <div class="container">
        <div class="centered-form">
            <div class="form-container">
                <div class="text-center mb-4">
                    <img src="img/logo.png" alt="Logo" class="img-fluid" width="300">
                </div>
                <form>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control" id="email" placeholder="Enter email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" class="form-control" id="password" placeholder="Enter password" required>
                    </div>
                    <div class="form-group text-right">
                        <a href="#" class="text-decoration-none">Forgot password?</a>
                    </div>
                    <a href="index.php" type="submit" class="btn btn-primary btn-block">Login</a>
                </form>
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="signup.php" class="text-decoration-none">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>

<?php require('footer.php') ?>