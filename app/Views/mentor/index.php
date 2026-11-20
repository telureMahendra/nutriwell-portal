<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Nutriwell | Mentor Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="<?php echo base_url(); ?>public/assets/global/plugins/font-awesome/css/font-awesome.min.css"
        rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="favicon.ico" />
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            width: 100vw;
            background: url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center center no-repeat fixed;
            background-size: cover;
            display: flex;
            position: relative;
            overflow: hidden;
        }

        body.modern-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(-45deg, rgba(243, 156, 18, 0.85), rgba(44, 62, 80, 0.9), rgba(211, 84, 0, 0.85), rgba(30, 40, 50, 0.95));
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            z-index: 1;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .login-wrapper {
            position: relative;
            z-index: 2;
            display: flex;
            width: 100%;
            height: 100%;
        }

        .login-left {
            flex: 1.2;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8%;
        }

        .overlay-text {
            max-width: 600px;
            color: white;
            animation: fadeIn 1.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .overlay-text h1 {
            font-size: 4.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.1;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            letter-spacing: -2px;
        }

        .overlay-text p {
            font-size: 1.4rem;
            font-weight: 300;
            opacity: 0.95;
            line-height: 1.6;
            text-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
        }

        .glass-card {
            width: 100%;
            max-width: 480px;
            padding: 50px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.1);
            animation: slideIn 1s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-wrapper {
            /* background: rgba(255, 255, 255, 0.95); */
            padding: 12px 25px;
            border-radius: 16px;
            display: inline-block;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .logo-container .logo {
            width: 150px;
            height: auto;
            vertical-align: middle;
        }

        .form-title {
            color: #ffffff;
            font-size: 2rem;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 8px;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            margin-bottom: 40px;
            font-size: 1rem;
        }

        .modern-input {
            margin-bottom: 25px;
        }

        .modern-input label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.95);
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .input-wrapper .form-control {
            width: 100%;
            padding: 16px 20px 16px 55px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            font-size: 1.05rem;
            color: #ffffff;
            transition: all 0.3s ease;
            box-sizing: border-box;
            font-family: inherit;
        }

        .input-wrapper .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .input-wrapper .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #f39c12;
            box-shadow: 0 0 0 4px rgba(243, 156, 18, 0.25);
            outline: none;
        }

        .input-wrapper .form-control:focus+i,
        .input-wrapper .form-control:valid+i {
            color: #f39c12;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #f39c12 0%, #d35400 100%);
            border: none;
            border-radius: 14px;
            color: white;
            font-size: 1.15rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
            margin-top: 15px;
            box-shadow: 0 10px 25px rgba(211, 84, 0, 0.4);
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(211, 84, 0, 0.5);
        }

        .btn-login:active {
            transform: translateY(1px);
            box-shadow: 0 5px 15px rgba(211, 84, 0, 0.4);
        }

        .custom-alert {
            background: rgba(231, 76, 60, 0.15);
            color: #ff7675;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            border: 1px solid rgba(231, 76, 60, 0.3);
            font-weight: 500;
            backdrop-filter: blur(5px);
        }

        .copyright {
            text-align: center;
            margin-top: 40px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
        }

        @media (max-width: 992px) {
            .login-wrapper {
                flex-direction: column;
            }

            .login-left {
                flex: auto;
                padding: 40px 20px 20px;
                text-align: center;
                align-items: flex-end;
            }

            .overlay-text h1 {
                font-size: 3rem;
            }

            .overlay-text p {
                font-size: 1.1rem;
            }

            .login-right {
                padding: 20px;
                align-items: flex-start;
            }

            .glass-card {
                padding: 40px 25px;
            }
        }
    </style>
</head>

<body class="modern-login">

    <div class="login-wrapper">
        <div class="login-left">
            <div class="overlay-text">
                <h1>Mentor Portal</h1>
                <p>Guiding clients towards a healthier tomorrow with personalized coaching and expertise.</p>
            </div>
        </div>
        <div class="login-right">
            <div class="glass-card">
                <div class="logo-container">
                    <div class="logo-wrapper">
                        <img src="<?php echo base_url(); ?>public/assets/admin/pages/img/logo.png" alt="Nutriwell Logo"
                            class="logo">
                    </div>
                </div>

                <?php
                $attributes = array('class' => 'login-form', 'id' => 'loginform');
                echo form_open_multipart('mentor/check_login', $attributes);
                ?>
                <h3 class="form-title">Welcome Back</h3>
                <p class="subtitle">Please sign in to access your dashboard</p>

                <?php if (isset($error_text)) { ?>
                    <div class="custom-alert">
                        <span><?php echo $error_text; ?></span>
                    </div>
                <?php } ?>

                <div class="modern-input">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <input class="form-control" type="text" autocomplete="off" placeholder="Enter your username"
                            name="username" id="username" required />
                        <i class="fa fa-user"></i>
                    </div>
                </div>

                <div class="modern-input">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <input class="form-control" type="password" autocomplete="off" placeholder="Enter your password"
                            name="password" id="password" required />
                        <i class="fa fa-lock"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Sign In to Dashboard
                </button>
                <?php echo form_close(); ?>

                <div class="copyright">
                    <?php echo date("Y"); ?> &copy; Healthie4U - Mentor Portal.
                </div>
            </div>
        </div>
    </div>

</body>

</html>