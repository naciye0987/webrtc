<!DOCTYPE html>
<html>
<head>
    <title>Görüntülü Konuşma - Giriş</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #1e4d3b 0%, #2d8659 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            width: 320px;
            backdrop-filter: blur(10px);
        }
        h2 {
            text-align: center;
            color: #1e4d3b;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .input-group {
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        input:focus {
            border-color: #2d8659;
            outline: none;
            box-shadow: 0 0 0 3px rgba(45, 134, 89, 0.2);
        }
        button {
            width: 100%;
            padding: 14px;
            background: #2d8659;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        button:hover {
            background: #1e4d3b;
            transform: translateY(-2px);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo i {
            font-size: 48px;
            color: #2d8659;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-box">
        <div class="logo">
            <i class="fas fa-video"></i>
        </div>
        <h2>Görüntülü Konuşma</h2>
        <form action="{{ route('join.room') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" name="username" placeholder="Kullanıcı Adı" required>
            </div>
            <div class="input-group">
                <input type="text" name="room_id" placeholder="Oda ID" required>
            </div>
            <button type="submit">Odaya Gir <i class="fas fa-arrow-right"></i></button>
        </form>
    </div>
</body>
</html> 