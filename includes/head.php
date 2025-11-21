<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DROPZONE - Recargas para Juegos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --black: #000000;
            --dark-gray: #1A1A1A;
            --white: #FFFFFF;
            --gold: #C8A032;
            --medium-gray: #333333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--black);
            color: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
            font-family: 'Exo 2', sans-serif;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header con efecto difuminado */
        header {
            background-color: rgba(26, 26, 26, 0);
            padding: 1.2rem 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.4s ease;
            border-bottom: 2px solid transparent;
        }
        
        header.scrolled {
            background-color: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--gold);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
            padding: 0.8rem 0;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Orbitron', sans-serif;
        }
        
        .logo span {
            color: var(--gold);
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav li {
            margin-left: 1.5rem;
        }
        
        nav a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
            font-family: 'Exo 2', sans-serif;
        }
        
        nav a:hover {
            color: var(--gold);
        }
        
        nav a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gold);
            transition: width 0.3s;
        }
        
        nav a:hover::after {
            width: 100%;
        }
        
        /* Resto de estilos... */
    </style>
</head>
<body>
    <header id="mainHeader">
        <div class="container">
            <div class="header-content">
                <div class="logo">DROP<span>ZONE</span></div>
                <nav>
                    <ul>
                        <li><a href="#">Inicio</a></li>
                        <li><a href="#">Juegos</a></li>
                        <li><a href="#">Recargas</a></li>
                        <li><a href="#">Soporte</a></li>
                        <li><a href="#">Contacto</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>