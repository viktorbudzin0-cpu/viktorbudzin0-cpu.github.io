<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vrime CheckerX | Обнаружение читов</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Russo+One&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        :root {
            --primary: #4a4a4a;
            --secondary: #2b2b2b;
            --dark: #1a1a1a;
            --darker: #0f0f0f;
            --light: #f0f0f0;
            --accent: #606060;
            --accent2: #808080;
            --accent3: #ff6b6b;
            --accent4: #4ecdc4;
            --minecraft: #5b9c4d;
            --rust: #c46e3a;
            --csgo: #966b3c;
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        body {
            background: linear-gradient(135deg, var(--darker), var(--dark));
            color: var(--light);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* Контейнер для параллакс эффекта */
        .parallax-container {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -3;
            overflow: hidden;
        }

        /* Слои параллакса */
        .parallax-layer {
            position: absolute;
            width: 120%;
            height: 120%;
            top: -10%;
            left: -10%;
            transition: transform 0.1s ease-out;
        }

        .layer-1 {
            background:
                radial-gradient(circle at 20% 30%, rgba(128, 128, 128, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 80% 70%, rgba(96, 96, 96, 0.1) 0%, transparent 20%);
            z-index: 1;
        }

        .layer-2 {
            background:
                radial-gradient(circle at 60% 10%, rgba(74, 74, 74, 0.15) 0%, transparent 25%),
                radial-gradient(circle at 30% 80%, rgba(42, 42, 42, 0.15) 0%, transparent 25%);
            z-index: 2;
        }

        /* Треугольники, следующие за курсором - ТОЛЬКО ВНИЗУ */
        .cursor-triangles {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 30vh;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        .triangle {
            position: absolute;
            width: 0;
            height: 0;
            transition: all 0.5s ease-out;
            opacity: 0.3;
            bottom: 0;
        }

        .triangle-1 {
            border-left: 20px solid transparent;
            border-right: 20px solid transparent;
            border-bottom: 35px solid var(--accent3);
            animation: floatTriangle 15s infinite ease-in-out;
        }

        .triangle-2 {
            border-left: 15px solid transparent;
            border-right: 15px solid transparent;
            border-top: 25px solid var(--accent4);
            animation: floatTriangle 12s infinite ease-in-out reverse;
        }

        .triangle-3 {
            border-bottom: 12px solid transparent;
            border-top: 12px solid transparent;
            border-left: 21px solid var(--accent2);
            animation: floatTriangle 18s infinite ease-in-out;
        }

        @keyframes floatTriangle {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(30px, -40px) rotate(90deg);
            }
            50% {
                transform: translate(10px, -80px) rotate(180deg);
            }
            75% {
                transform: translate(-20px, -40px) rotate(270deg);
            }
        }

        /* Интерактивный фон с частицами */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -2;
        }

        /* Анимированные градиентные круги */
        .gradient-circles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -3;
            overflow: hidden;
            opacity: 0.5;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.3;
            animation: float 15s infinite linear;
        }

        .circle:nth-child(1) {
            background: var(--accent);
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
            animation-duration: 20s;
        }

        .circle:nth-child(2) {
            background: var(--accent2);
            width: 500px;
            height: 500px;
            bottom: -250px;
            right: -250px;
            animation-duration: 25s;
            animation-delay: -5s;
        }

        .circle:nth-child(3) {
            background: var(--primary);
            width: 400px;
            height: 400px;
            top: 50%;
            left: 70%;
            animation-duration: 30s;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(20px, 40px) rotate(90deg);
            }
            50% {
                transform: translate(0, 80px) rotate(180deg);
            }
            75% {
                transform: translate(-20px, 40px) rotate(270deg);
            }
        }

        /* Навигация */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 5%;
            background: rgba(15, 15, 15, 0.9);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        nav.scrolled {
            padding: 1rem 5%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--accent2), var(--light));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: flex;
            align-items: center;
            transition: var(--transition);
            font-family: 'Russo One', sans-serif;
        }

        .logo:hover {
            transform: scale(1.05);
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
        }

        .logo i {
            margin-right: 10px;
            font-size: 2rem;
        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin: 0 1rem;
        }

        .nav-links a {
            color: var(--light);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: var(--transition);
            position: relative;
        }

        .nav-links a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background: linear-gradient(45deg, var(--accent2), var(--light));
            transition: width 0.3s ease;
        }

        .nav-links a:hover:after {
            width: 100%;
        }

        .nav-links a.active {
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-links a.active:after {
            width: 100%;
        }

        /* Контейнер для страниц с анимацией перелистывания */
        .pages-container {
            position: relative;
            flex: 1;
            overflow: hidden;
        }

        /* Основной контент */
        .page {
            position: absolute;
            width: 100%;
            top: 0;
            left: 0;
            padding: 2rem 5%;
            opacity: 0;
            transform: translateX(100%);
            transition: transform 0.6s ease, opacity 0.6s ease;
            pointer-events: none;
            min-height: calc(100vh - 80px);
        }

        .page.active {
            opacity: 1;
            transform: translateX(0);
            pointer-events: all;
            position: relative;
        }

        .page.prev {
            transform: translateX(-100%);
            opacity: 0;
        }

        .page.next {
            transform: translateX(100%);
            opacity: 0;
        }

        /* Стили для главной страницы */
        .hero {
            text-align: center;
            padding: 6rem 0;
            max-width: 900px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, var(--accent2), var(--light), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: gradientShift 5s infinite alternate, pulse 2s infinite ease-in-out;
            font-family: 'Russo One', sans-serif;
            letter-spacing: 1px;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #e0e0e0;
            line-height: 1.6;
        }

        .stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 3rem 0;
            flex-wrap: wrap;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.5rem;
            min-width: 180px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, var(--accent2), var(--light));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 4rem 0;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .feature-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--accent2), var(--light));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .feature-card:hover:before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .feature-card i {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: var(--accent2);
            background: linear-gradient(45deg, var(--accent2), var(--light));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: bounce 3s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-15px);}
            60% {transform: translateY(-7px);}
        }

        .feature-card h3 {
            margin-bottom: 1rem;
            font-size: 1.6rem;
        }

        /* Новые секции для главной страницы */
        .how-it-works {
            margin: 5rem 0;
            text-align: center;
        }

        .steps {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .step {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 2rem;
            width: 250px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            position: relative;
        }

        .step:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .step-number {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, var(--accent2), var(--light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .testimonials {
            margin: 5rem 0;
            text-align: center;
        }

        .testimonial-cards {
            display: flex;
            gap: 2rem;
            margin-top: 3rem;
            overflow-x: auto;
            padding: 1rem 0;
            scrollbar-width: thin;
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 2rem;
            min-width: 300px;
            text-align: left;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 1.5rem;
            position: relative;
            padding-left: 1.5rem;
        }

        .testimonial-text:before {
            content: """;
            position: absolute;
            left: 0;
            top: -10px;
            font-size: 3rem;
            color: var(--accent2);
            font-family: sans-serif;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 1rem;
            background: linear-gradient(45deg, var(--accent2), var(--light));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Стили для страницы загрузки */
        .download-container {
            max-width: 1200px;
            margin: 3rem auto;
            text-align: center;
        }

        .download-header {
            margin-bottom: 3rem;
        }

        .game-selector {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
            perspective: 1000px;
        }

        .game-option {
            width: 280px;
            height: 350px;
            margin: 0 1.5rem;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            transition: var(--transition);
            transform-style: preserve-3d;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .game-option:hover {
            transform: translateY(-10px) rotateY(5deg);
        }

        .game-option.active {
            transform: scale(1.05) rotateY(0);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .game-option[data-game="minecraft"].active {
            border: 3px solid var(--minecraft);
        }

        .game-option[data-game="rust"].active {
            border: 3px solid var(--rust);
        }

        .game-option[data-game="csgo"].active {
            border: 3px solid var(--csgo);
        }

        .game-content {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s;
        }

        .game-front, .game-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .game-back {
            transform: rotateY(180deg);
            background: rgba(26, 26, 26, 0.95);
        }

        .game-option.flipped .game-content {
            transform: rotateY(180deg);
        }

        .game-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        .game-option[data-game="minecraft"] .game-icon {
            color: var(--minecraft);
        }

        .game-option[data-game="rust"] .game-icon {
            color: var(--rust);
        }

        .game-option[data-game="csgo"] .game-icon {
            color: var(--csgo);
        }

        .game-title {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            font-family: 'Russo One', sans-serif;
        }

        .game-description {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .flip-button {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            cursor: pointer;
            transition: var(--transition);
        }

        .flip-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .download-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .download-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .download-content.active {
            display: block;
        }

        .download-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .download-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(128, 128, 128, 0.1), rgba(240, 240, 240, 0.1));
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .download-card:hover:before {
            opacity: 1;
        }

        .download-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .download-card i {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--accent2), var(--light));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .download-card h3 {
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .download-card.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .download-card.disabled:after {
            content: 'Только для Windows';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        /* Уменьшенная кнопка скачивания */
        .download-btn {
            display: inline-block;
            padding: 0.6rem 1.5rem;
            background: linear-gradient(45deg, var(--accent), var(--accent2));
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 500;
            margin-top: 1rem;
            overflow: hidden;
            position: relative;
            z-index: 1;
            transition: var(--transition);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .download-btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, var(--accent2), var(--accent));
            z-index: -1;
            transition: transform 0.5s ease;
            transform: scaleX(0);
            transform-origin: right;
        }

        .download-btn:hover:before {
            transform: scaleX(1);
            transform-origin: left;
        }

        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }

        .download-btn i {
            margin-right: 8px;
            animation: downloadPulse 2s infinite;
        }

        @keyframes downloadPulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Стили для страницы "О нас" */
        .about-container {
            max-width: 1200px;
            margin: 3rem auto;
        }

        .about-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .about-content {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 3rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            line-height: 1.6;
            position: relative;
            overflow: hidden;
            margin-bottom: 3rem;
        }

        .about-content:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, var(--accent2), var(--light));
        }

        .about-content p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .history {
            display: flex;
            gap: 2rem;
            margin: 3rem 0;
            flex-wrap: wrap;
        }

        .history-item {
            flex: 1;
            min-width: 250px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .history-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .history-year {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--accent2), var(--light));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .values {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .value-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .value-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, var(--accent2), var(--light));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .team {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .team-member {
            text-align: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .member-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 3px solid transparent;
            background: linear-gradient(45deg, var(--accent2), var(--light)) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
        }

        .member-social {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .member-social a {
            color: var(--light);
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .member-social a:hover {
            color: var(--accent2);
            transform: translateY(-3px);
        }

        /* Футер - исправлен для прижатия к низу */
        footer {
            background: rgba(15, 15, 15, 0.9);
            padding: 3rem 5%;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
            width: 100%;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .footer-links {
            display: flex;
            gap: 2rem;
            margin: 1.5rem 0;
            flex-wrap: wrap;
            justify-content: center;
        }

        .footer-links a {
            color: var(--light);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--accent2);
        }

        .social-links {
            display: flex;
            gap: 1.5rem;
            margin: 1.5rem 0;
        }

        .social-links a {
            color: var(--light);
            font-size: 1.8rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .social-links a:hover {
            color: var(--accent2);
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        /* Модальное окно для скачивания */
        .download-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .download-modal.active {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: linear-gradient(135deg, var(--darker), var(--dark));
            border-radius: 16px;
            padding: 2.5rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .download-modal.active .modal-content {
            transform: scale(1);
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5rem;
            color: var(--light);
            background: none;
            border: none;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: var(--accent3);
        }

        .modal-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, var(--accent2), var(--light));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .modal-title {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .modal-text {
            margin-bottom: 2rem;
            color: #e0e0e0;
        }

        .modal-download-btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: linear-gradient(45deg, var(--accent), var(--accent2));
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }

        /* Progress bar animation */
        .download-progress {
            height: 5px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            margin: 1.5rem 0;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--accent2), var(--light));
            border-radius: 5px;
            transition: width 0.5s ease;
        }

        /* Анимации появления элементов */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        .is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Адаптивность */
        @media (max-width: 1024px) {
            .game-selector {
                flex-direction: column;
                align-items: center;
                gap: 2rem;
            }

            .game-option {
                width: 100%;
                max-width: 400px;
                margin: 0;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2.8rem;
            }

            .features, .download-options, .team, .values, .steps {
                grid-template-columns: 1fr;
            }

            .stats {
                flex-direction: column;
                align-items: center;
            }

            .stat-item {
                width: 100%;
                max-width: 250px;
            }

            .download-btn {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }

            .about-content {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Параллакс фон -->
    <div class="parallax-container">
        <div class="parallax-layer layer-1"></div>
        <div class="parallax-layer layer-2"></div>
    </div>

    <!-- Треугольники, следующие за курсором - ТОЛЬКО ВНИЗУ -->
    <div class="cursor-triangles">
        <div class="triangle triangle-1"></div>
        <div class="triangle triangle-2"></div>
        <div class="triangle triangle-3"></div>
    </div>

    <!-- Интерактивный фон с частицами -->
    <div id="particles-js"></div>
    <div class="gradient-circles">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

    <!-- Модальное окно для скачивания -->
    <div class="download-modal" id="downloadModal">
        <div class="modal-content">
            <button class="modal-close" id="modalClose">&times;</button>
            <i class="fas fa-download modal-icon"></i>
            <h2 class="modal-title">Скачивание началось!</h2>
            <p class="modal-text" id="modalGameText">Ваша версия Vrime checkerX готовится к загрузке.</p>

            <div class="download-progress">
                <div class="progress-bar" id="progressBar"></div>
            </div>

            <p class="modal-text">Спасибо, что выбрали наш продукт!</p>
            <a href="#" class="modal-download-btn" id="realDownload">Начать установку</a>
        </div>
    </div>

    <!-- Навигация -->
    <nav>
        <div class="logo">
            <i class="fas fa-shield-alt"></i>
            <span>Vrime Client</span>
        </div>
        <ul class="nav-links">
            <li><a href="#home" class="active nav-link">Главная</a></li>
            <li><a href="#download" class="nav-link">Скачать</a></li>
            <li><a href="#about" class="nav-link">О нас</a></li>
        </ul>
    </nav>

    <!-- Страницы -->
    <div class="pages-container">
        <div id="home" class="page active">
            <div class="hero">
                <h1>Vrime Checker X</h1>
                <p>Мощный инструмент для обнаружения читов в Minecraft, Rust и CS2. Обеспечьте честную игру на вашем сервере с помощью передовых алгоритмов анализа.</p>
                <a href="#download" class="download-btn nav-link">
                    <i class="fas fa-download"></i>Скачать сейчас
                </a>
            </div>

            <div class="stats">
                <div class="stat-item animate-on-scroll">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Активных серверов</div>
                </div>
                <div class="stat-item animate-on-scroll">
                    <div class="stat-number">300+</div>
                    <div class="stat-label">Данных в базе</div>
                </div>
                <div class="stat-item animate-on-scroll">
                    <div class="stat-number">99.9%</div>
                    <div class="stat-label">Точность обнаружения</div>
                </div>
                <div class="stat-item animate-on-scroll">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Техническая поддержка</div>
                </div>
            </div>

            <div class="features">
                <div class="feature-card animate-on-scroll">
                    <i class="fas fa-cube"></i>
                    <h3>Minecraft Checker</h3>
                    <p>Обнаруживает более 100+ различных модов и читов для Minecraft, включая самые последние версии.</p>
                </div>
                <div class="feature-card animate-on-scroll">
                    <i class="fas fa-fire"></i>
                    <h3>Rust Checker</h3>
                    <p>Эффективная система обнаружения читов для Rust с минимальным влиянием на производительность сервера.</p>
                </div>
                <div class="feature-card animate-on-scroll">
                    <i class="fas fa-crosshairs"></i>
                    <h3>CS2 Checker</h3>
                    <p>Продвинутые алгоритмы для выявления читателей в CS2 с ежедневными обновлениями базы данных.</p>
                </div>
            </div>

            <div class="how-it-works">
                <h2>Как работает Vrime CheckerX</h2>
                <p>Наша система использует передовые технологии для обнаружения нечестной игры</p>

                <div class="steps">
                    <div class="step animate-on-scroll">
                        <div class="step-number">1</div>
                        <h3>Сканирование</h3>
                        <p>Проверка игровых файлов и процессов в реальном времени</p>
                    </div>
                    <div class="step animate-on-scroll">
                        <div class="step-number">2</div>
                        <h3>Анализ</h3>
                        <p>Сравнение с базой известных читов и сигнатур</p>
                    </div>
                    <div class="step animate-on-scroll">
                        <div class="step-number">3</div>
                        <h3>Обнаружение</h3>
                        <p>Выявление подозрительной активности и модификаций</p>
                    </div>
                    <div class="step animate-on-scroll">
                        <div class="step-number">4</div>
                        <h3>Отчет</h3>
                        <p>Подробный отчет с доказательствами нарушения</p>
                    </div>
                </div>
            </div>


        </div>

        <div id="download" class="page">
            <div class="download-container">
                <div class="download-header">
                    <h1>Скачать Vrime CheckerX</h1>
                    <p>Выберите игру и версию для вашей операционной системы</p>
                </div>

                <div class="game-selector">
                    <div class="game-option active" data-game="minecraft">
                        <div class="game-content">
                            <div class="game-front">
                                <i class="fas fa-cube game-icon"></i>
                                <h3 class="game-title">Minecraft</h3>
                                <p class="game-description">Чекер для Minecraft с большой базой данных</p>
                                <button class="flip-button">Подробнее</button>
                            </div>
                            <div class="game-back">
                                <h3>Особенности</h3>
                                <ul style="text-align: left; margin-bottom: 1.5rem;">
                                    <li>База с более чем 200 тысячами по/модов</li>
                                    <li>Поддержка Forge и Fabric</li>
                                    <li>Минимальные лаги</li>
                                    <li>Ежедневные обновления</li>
                                </ul>
                                <button class="flip-button">Назад</button>
                            </div>
                        </div>
                    </div>

                    <div class="game-option" data-game="rust">
                        <div class="game-content">
                            <div class="game-front">
                                <i class="fas fa-fire game-icon"></i>
                                <h3 class="game-title">Rust</h3>
                                <p class="game-description">Мощный чекер для Rust с продвинутым анализом</p>
                                <button class="flip-button">Подробнее</button>
                            </div>
                            <div class="game-back">
                                <h3>Особенности</h3>
                                <ul style="text-align: left; margin-bottom: 1.5rem;">
                                    <li>Анализ памяти в реальном времени</li>
                                    <li>Обнаружение запуска читов с flash, инфрмация по secureboot/TVM</li>
                                    <li>Защита от новых читов</li>
                                    <li>Подробная статистика</li>
                                </ul>
                                <button class="flip-button">Назад</button>
                            </div>
                        </div>
                    </div>

                    <div class="game-option" data-game="CS2">
                        <div class="game-content">
                            <div class="game-front">
                                <i class="fas fa-crosshairs game-icon"></i>
                                <h3 class="game-title">CS2</h3>
                                <p class="game-description">Профессиональный чекер для CS2</p>
                                <button class="flip-button">Подробнее</button>
                            </div>
                            <div class="game-back">
                                <h3>Особенности</h3>
                                <ul style="text-align: left; margin-bottom: 1.5rem;">
                                    <li>Только для Windows</li>
                                    <li>Обнаружение internal/external клиентлв</li>
                                    <li>Анализ игровых файлов</li>
                                    <li>Глубой анализ системы </li>
                                </ul>
                                <button class="flip-button">Назад</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Minecraft Downloads -->
                <div class="download-content active" data-game="minecraft">
                    <div class="download-options">
                        <div class="download-card animate-on-scroll">
                            <i class="fab fa-windows"></i>
                            <h3>Windows</h3>
                            <p>Версия для Windows 7, 8, 10 и 11</p>
                            <a href="VrimeCheckerX-Mainecraftversion.exe" class="download-btn" data-os="windows" data-game="minecraft">
                                Скачать для Windows
                            </a>
                        </div>
                        <div class="download-card animate-on-scroll">
                            <i class="fab fa-apple"></i>
                            <h3>macOS</h3>
                            <p>Версия для macOS 10.12 и новее</p>
                            <a href="Vrime-CheckerX.deb" class="download-btn" data-os="macos" data-game="minecraft">
                                Скачать для macOS
                            </a>
                        </div>
                        <div class="download-card animate-on-scroll">
                            <i class="fab fa-linux"></i>
                            <h3>Linux</h3>
                            <p>Версия для Ubuntu, Debian и Fedora</p>
                            <a href="Vrime-CheckerX.tar.gz" class="download-btn" data-os="linux" data-game="minecraft">
                                Скачать для Linux
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Rust Downloads -->
                <div class="download-content" data-game="rust">
                    <div class="download-options">
                        <div class="download-card animate-on-scroll">
                            <i class="fab fa-windows"></i>
                            <h3>Windows</h3>
                            <p>Версия для Windows 7, 8, 10 и 11</p>
                            <a href="#" class="download-btn" data-os="windows" data-game="rust">
                                Скачать для Windows
                            </a>
                        </div>
                        <div class="download-card animate-on-scroll">
                            <i class="fab fa-apple"></i>
                            <h3>macOS</h3>
                            <p>Версия для macOS 10.12 и новее</p>
                            <a href="Vrime-CheckerX.deb" class="download-btn" data-os="macos" data-game="rust">
                                Скачать для macOS
                            </a>
                        </div>
                        <div class="download-card animate-on-scroll">
                            <i class="fab fa-linux"></i>
                            <h3>Linux</h3>
                            <p>Версия для Ubuntu, Debian и Fedora</p>
                            <a href="Vrime-CheckerX.tar.gz" class="download-btn" data-os="linux" data-game="rust">
                                Скачать для Linux
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CS2 Downloads -->
                <div class="download-content" data-game="CS2">
                    <div class="download-options">
                        <div class="download-card animate-on-scroll">
                            <i class="fab fa-windows"></i>
                            <h3>Windows</h3>
                            <p>Версия для Windows 7, 8, 10 и 11</p>
                            <a href="#" class="download-btn" data-os="windows" data-game="CS2">
                                Скачать для Windows
                            </a>
                        </div>
                        <div class="download-card animate-on-scroll disabled">
                            <i class="fab fa-apple"></i>
                            <h3>macOS</h3>
                            <p>Недоступно для macOS</p>
                            <a href="#" class="download-btn" data-os="macos" data-game="CS2">
                                Скачать для macOS
                            </a>
                        </div>
                        <div class="download-card animate-on-scroll disabled">
                            <i class="fab fa-linux"></i>
                            <h3>Linux</h3>
                            <p>Недоступно для Linux</p>
                            <a href="#" class="download-btn" data-os="linux" data-game="CS2">
                                Скачать для Linux
                            </a>
                        </div>
                    </div>
                </div>

                <div class="system-requirements animate-on-scroll">
                    <h2>Системные требования</h2>
                    <p>Минимальные требования для работы программы:</p>
                    <ul>
                        <li>ОС: Windows 7+, macOS 10.12+, или Linux (Ubuntu 16.04+)</li>
                        <li>Оперативная память: 2 GB RAM</li>
                        <li>Место на диске: 500 MB свободного места</li>
                        <li>Подключение к интернету для проверки обновлений базы читов</li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="about" class="page">
            <div class="about-container">
                <div class="about-header">
                    <h1>О нашем проекте</h1>
                    <p>УзнайтеА больше о Vrime CheckerX и нашей команде</p>
                </div>

                <div class="about-content animate-on-scroll">
                    <p>Vrime CheckerX — это инновационная разработка, созданная для обеспечения честной игры в сообществах Minecraft, Rust и CS2. Наша программа использует передовые алгоритмы анализа для обнаружения неавторизованных модификаций клиента.</p>

                    <p>Мы начали этот проект в 2024 году как ответ на растущее количество читеров, портящих игровой опыт на multiplayer-серверах. С тех пор наша команда постоянно работает над улучшением и расширением возможностей программы.</p>

                    <p>Наше решение уже используют более 500 серверов по всему миру, включая крупные игровые сообщества с тысячами активных игроков.</p>
                </div>

                <div class="history">
                    <div class="history-item animate-on-scroll">
                        <div class="history-year">2024</div>
                        <h3>Начало пути</h3>
                        <p>Основание проекта и первые версии античита для Minecraft</p>
                    </div>
                    <div class="history-item animate-on-scroll">
                        <div class="history-year">2025</div>
                        <h3>Расширение</h3>
                        <p>Добавлена поддержка Rust и CS2, улучшены алгоритмы</p>
                    </div>
                    <div class="history-item animate-on-scroll">
                        <div class="history-year">2026</div>
                        <h3>Скоро...</h3>
                        <p>Не кто не знает что будет 😉</p>
                    </div>
                </div>

                <div class="values">
                    <div class="value-card animate-on-scroll">
                        <i class="fas fa-shield-alt value-icon"></i>
                        <h3>Безопасность</h3>
                        <p>Мы обеспечиваем максимальную защиту от читов, сохраняя конфиденциальность пользователей</p>
                    </div>
                    <div class="value-card animate-on-scroll">
                        <i class="fas fa-sync-alt value-icon"></i>
                        <h3>Развитие</h3>
                        <p>Постоянно обновляем и улучшаем наш продукт для борьбы с новыми угрозами</p>
                    </div>
                    <div class="value-card animate-on-scroll">
                        <i class="fas fa-users value-icon"></i>
                        <h3>Сообщество</h3>
                        <p>Работаем в тесном контакте с администраторами серверов для лучшего понимания потребностей</p>
                    </div>
                </div>

                <div class="about-header">
                    <h2>Наша команда</h2>
                    <p>Профессионалы, создающие лучший античит</p>
                </div>

                <div class="team">
                    <div class="team-member animate-on-scroll">

                        <h4>𝒱𝒾𝓀𝓉𝑜𝓇𝑔𝑜𝓈𝓉 ♡</h4>
                        <p>Основатель и главный разработчик</p>
                        <div class="member-social">

                            <a href="https://github.com/vi3itor"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                    <div class="team-member animate-on-scroll">

                        <h4>_arkenstoun_</h4>
                        <p>Главный аналитик по безопасности</p>
                        <div class="member-social">

                            <a href="https://github.com/m3talsmith"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                    <div class="team-member animate-on-scroll">

                        <h4>liyamurr</h4>
                        <p>Дизайнер и UX-специалист</p>
                        <div class="member-social">

                            <a href="https://github.com/LiamKarlMitchell"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
                <span>Vrime Client</span>
            </div>

            <div class="footer-links">
                <a href="#home" class="nav-link">Главная</a>
                <a href="#download" class="nav-link">Скачать</a>
                <a href="#about" class="nav-link">О нас</a>
                <a href="#">Политика конфиденциальности</a>
                <a href="https://t.me/VrimeCheckerSupport_bot">Поддержка</a>
            </div>

            <div class="social-links">
                <a href="https://t.me/VrimeCheckerSupport_bot"><i class="fab fa-telegram"></i></a>
                <a href="https://github.com/topics/plagiarism-checker"><i class="fab fa-github"></i></a>
            </div>
            <p>&copy; 2024 Vrime Client. Все права защищены.</p>
        </div>
    </footer>

    <!-- Подключаем библиотеки -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        // Инициализация частиц для фона
        document.addEventListener('DOMContentLoaded', function() {
            particlesJS('particles-js', {
                "particles": {
                    "number": {
                        "value": 120,
                        "density": {
                            "enable": true,
                            "value_area": 800
                        }
                    },
                    "color": {
                        "value": "#ffffff"
                    },
                    "shape": {
                        "type": "circle",
                        "stroke": {
                            "width": 0,
                            "color": "#000000"
                        }
                    },
                    "opacity": {
                        "value": 0.5,
                        "random": true,
                        "anim": {
                            "enable": true,
                            "speed": 1,
                            "opacity_min": 0.1,
                            "sync": false
                        }
                    },
                    "size": {
                        "value": 3,
                        "random": true,
                        "anim": {
                            "enable": true,
                            "speed": 2,
                            "size_min": 0.1,
                            "sync": false
                        }
                    },
                    "line_linked": {
                        "enable": true,
                        "distance": 150,
                        "color": "#ffffff",
                        "opacity": 0.4,
                        "width": 1
                    },
                    "move": {
                        "enable": true,
                        "speed": 3,
                        "direction": "none",
                        "random": true,
                        "straight": false,
                        "out_mode": "out",
                        "bounce": false,
                        "attract": {
                            "enable": false,
                            "rotateX": 600,
                            "rotateY": 1200
                        }
                    }
                },
                "interactivity": {
                    "detect_on": "canvas",
                    "events": {
                        "onhover": {
                            "enable": true,
                            "mode": "grab"
                        },
                        "onclick": {
                            "enable": true,
                            "mode": "push"
                        },
                        "resize": true
                    },
                    "modes": {
                        "grab": {
                            "distance": 180,
                            "line_linked": {
                                "opacity": 1
                            }
                        },
                        "push": {
                            "particles_nb": 6
                        }
                    }
                },
                "retina_detect": true
            });

            // Параллакс эффект для фона
            const parallaxLayers = document.querySelectorAll('.parallax-layer');
            document.addEventListener('mousemove', function(e) {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;

                parallaxLayers.forEach((layer, index) => {
                    const speed = (index + 1) * 0.01;
                    const xOffset = x * speed * 100;
                    const yOffset = y * speed * 100;

                    layer.style.transform = `translate(-${xOffset}%, -${yOffset}%)`;
                });
            });

            // Анимация треугольников, следующих за курсором - ТОЛЬКО ВНИЗУ
            const triangles = document.querySelectorAll('.triangle');
            let mouseX = 0;
            let mouseY = 0;
            let triangleX = [0, 0, 0];
            let triangleY = [0, 0, 0];

            document.addEventListener('mousemove', (e) => {
                mouseX = e.clientX;
                mouseY = e.clientY;
            });

            function animateTriangles() {
                // Позиционируем треугольники с задержкой
                triangles.forEach((triangle, index) => {
                    const speed = 0.05 + (index * 0.02);

                    triangleX[index] += (mouseX - triangleX[index]) * speed;
                    // Ограничиваем движение треугольников только нижней частью экрана
                    triangleY[index] += ((mouseY * 0.3 + window.innerHeight * 0.7) - triangleY[index]) * speed;

                    triangle.style.left = `${triangleX[index]}px`;
                    triangle.style.top = `${triangleY[index]}px`;

                    // Изменяем размер треугольников в зависимости от скорости движения мыши
                    const deltaX = Math.abs(mouseX - triangleX[index]);
                    const deltaY = Math.abs(mouseY - triangleY[index]);
                    const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

                    const scale = Math.min(2, 1 + distance / 200);
                    triangle.style.transform = `scale(${scale}) rotate(${index * 120 + Date.now() / 100}deg)`;
                });

                requestAnimationFrame(animateTriangles);
            }

            // Инициализируем начальные позиции треугольников в нижней части экрана
            triangles.forEach((triangle, index) => {
                triangleX[index] = window.innerWidth / 2;
                triangleY[index] = window.innerHeight * 0.8;

                triangle.style.left = `${triangleX[index]}px`;
                triangle.style.top = `${triangleY[index]}px`;
            });

            animateTriangles();

            // Навигация между страницами
            const navLinks = document.querySelectorAll('.nav-link');
            const pages = document.querySelectorAll('.page');

            function changePage(targetId) {
                // Обновляем активные классы на навигационных ссылках
                navLinks.forEach(navLink => {
                    navLink.classList.remove('active');
                    if (navLink.getAttribute('href').substring(1) === targetId) {
                        navLink.classList.add('active');
                    }
                });

                // Показываем целевую страницу, скрываем другие
                pages.forEach(page => {
                    page.classList.remove('active');
                    if (page.id === targetId) {
                        setTimeout(() => {
                            page.classList.add('active');
                            // Прокручиваем к началу страницы
                            window.scrollTo(0, 0);
                        }, 50);
                    }
                });
            }

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    changePage(targetId);
                });
            });

            // Анимация появления элементов при прокрутке
            const animatedElements = document.querySelectorAll('.animate-on-scroll');

            function checkIfInView() {
                animatedElements.forEach(element => {
                    const position = element.getBoundingClientRect();

                    if (position.top < window.innerHeight && position.bottom >= 0) {
                        element.classList.add('is-visible');
                    }
                });
            }

            window.addEventListener('scroll', checkIfInView);
            window.addEventListener('load', checkIfInView);
            checkIfInView(); // Проверить при загрузке

            // Обработка хэша URL при загрузке
            if (window.location.hash) {
                const targetId = window.location.hash.substring(1);
                if (['home', 'download', 'about'].includes(targetId)) {
                    changePage(targetId);
                }
            }

            // Переключение между играми на странице загрузки
            const gameOptions = document.querySelectorAll('.game-option');
            const downloadContents = document.querySelectorAll('.download-content');

            gameOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const game = this.getAttribute('data-game');

                    // Активируем выбранную опцию
                    gameOptions.forEach(o => o.classList.remove('active'));
                    this.classList.add('active');

                    // Показываем соответствующий контент
                    downloadContents.forEach(content => {
                        content.classList.remove('active');
                        if (content.getAttribute('data-game') === game) {
                            content.classList.add('active');
                        }
                    });
                });

                // Кнопки переворота
                const flipButtons = option.querySelectorAll('.flip-button');
                flipButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.stopPropagation();
                        option.classList.toggle('flipped');
                    });
                });
            });

            // Модальное окно для скачивания
            const downloadModal = document.getElementById('downloadModal');
            const modalClose = document.getElementById('modalClose');
            const progressBar = document.getElementById('progressBar');
            const realDownload = document.getElementById('realDownload');
            const modalGameText = document.getElementById('modalGameText');
            const downloadButtons = document.querySelectorAll('.download-btn[data-os]');

            downloadButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const os = this.getAttribute('data-os');
                    const game = this.getAttribute('data-game');

                    // Устанавливаем текст в модальном окне в зависимости от игры
                    let gameName = '';
                    switch(game) {
                        case 'minecraft':
                            gameName = 'Minecraft';
                            break;
                        case 'rust':
                            gameName = 'Rust';
                            break;
                        case 'CS2':
                            gameName = 'CS2';
                            break;
                    }
                    modalGameText.textContent = `Ваша версия Vrime CheckerX для ${gameName} готовится к загрузке.`;

                    // Показываем модальное окно
                    downloadModal.classList.add('active');

                    // Анимация прогресс-бара
                    let progress = 0;
                    const progressInterval = setInterval(() => {
                        progress += 5;
                        progressBar.style.width = `${progress}%`;

                        if (progress >= 100) {
                            clearInterval(progressInterval);
                            realDownload.style.display = 'inline-block';

                            // Устанавливаем правильную ссылку для скачивания
                            switch(os) {
                                case 'windows':
                                    realDownload.href = `Vrime${game}.exe`;
                                    break;
                                case 'macos':
                                    realDownload.href = `Vrime-CheckerX-${game}.dmg`;
                                    break;
                                case 'linux':
                                    realDownload.href = `Vrime-CheckerX-${game}.deb`;
                                    break;
                            }
                        }
                    }, 100);
                });
            });

            modalClose.addEventListener('click', function() {
                downloadModal.classList.remove('active');
                progressBar.style.width = '0%';
                realDownload.style.display = 'none';
            });

            window.addEventListener('click', function(e) {
                if (e.target === downloadModal) {
                    downloadModal.classList.remove('active');
                    progressBar.style.width = '0%';
                    realDownload.style.display = 'none';
                }
            });

            // Анимация навигации при прокрутке
            const nav = document.querySelector('nav');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    nav.classList.add('scrolled');
                } else {
                    nav.classList.remove('scrolled');
                }
            });
        });
    </script>
</body>
</html>