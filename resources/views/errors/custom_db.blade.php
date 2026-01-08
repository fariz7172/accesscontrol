<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Error</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #22252C, #181B21);
            height: 100vh;
            font-family: 'Nunito', sans-serif;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        .container {
            text-align: center;
            z-index: 10;
        }

        h1 {
            font-size: 5rem;
            margin: 0;
            background: -webkit-linear-gradient(#eee, #aaa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
        }

        h2 {
            font-size: 2rem;
            margin: 10px 0;
            color: #FF6B6B;
        }

        p {
            font-size: 1.2rem;
            color: #ccc;
            margin-bottom: 30px;
        }

        .btn {
            padding: 12px 30px;
            background-color: #4ECDC4;
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(78, 205, 196, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 205, 196, 0.6);
        }

        /* Astronaut & Space Animation */
        .astronaut {
            width: 250px;
            height: 300px;
            position: absolute;
            z-index: 1;
            top: calc(50% - 150px);
            right: 15%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .star {
            width: 2px;
            height: 2px;
            background: #fff;
            position: absolute;
            border-radius: 50%;
            opacity: 0.8;
            animation: twinkle 2s infinite ease-in-out;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 0.2; }
        }

        /* Generate random stars */
        .star:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .star:nth-child(2) { top: 20%; left: 80%; animation-delay: 0.5s; }
        .star:nth-child(3) { top: 80%; left: 20%; animation-delay: 1s; }
        .star:nth-child(4) { top: 70%; left: 90%; animation-delay: 1.5s; }
        .star:nth-child(5) { top: 40%; left: 50%; animation-delay: 0.2s; }
        .star:nth-child(6) { top: 90%; left: 10%; animation-delay: 0.8s; }
        .star:nth-child(7) { top: 15%; left: 40%; animation-delay: 1.2s; }

        /* Simple Astronaut SVG (Inline) */
        .astro-svg {
            width: 100%;
            height: 100%;
        }

        @media (max-width: 768px) {
            .astronaut {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="star"></div>
    <div class="star"></div>
    <div class="star"></div>
    <div class="star"></div>
    <div class="star"></div>
    <div class="star"></div>
    <div class="star"></div>

    <div class="container">
        <h1>Ooops!</h1>
        <h2>Database atau Password Salah</h2>
        <p>Koneksi ke database terputus. Silakan periksa konfigurasi .env atau hubungi Administrator.</p>
        <a href="{{ route('sesi') }}" class="btn">COBA LAGI</a>
        
        <div style="margin-top: 20px; font-size: 0.8rem; color: #666;">
            Error Code: 1045 (Access Denied)
        </div>
    </div>

    <div class="astronaut">
        <!-- SVG Astronaut -->
        <svg class="astro-svg" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
            <g id="astronaut">
                <path fill="#FFFFFF" d="M365.4,383.6c-13.4,19-35.1,31.3-59.5,31.3h-112c-24.4,0-46.1-12.4-59.5-31.3l-28-39.7h287L365.4,383.6z"/>
                <path fill="#E6E6E6" d="M305.9,414.9H193.9V344h112V414.9z"/>
                <path fill="#FFFFFF" d="M250,58c49.7,0,90,40.3,90,90s-40.3,90-90,90s-90-40.3-90-90S200.3,58,250,58 M250,38 c-60.8,0-110,49.2-110,110s49.2,110,110,110s110-49.2,110-110S310.8,38,250,38L250,38z"/>
                <circle fill="#333333" cx="250" cy="148" r="80"/>
                <path fill="#FFFFFF" d="M290,130c0,11-9,20-20,20s-20-9-20-20s9-20,20-20S290,119,290,130z"/>
                <path fill="#E6E6E6" d="M312,258h-124c-11,0-20,9-20,20v66h164v-66C332,267,323,258,312,258z"/>
            </g>
        </svg>
    </div>

</body>
</html>
