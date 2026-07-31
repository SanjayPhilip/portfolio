<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E Blood Connect - Home</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        :root {
            --primary-color: #e63946;
            --secondary-color: #1d3557;
            --accent-color: #457b9d;
            --light-color: #f1faee;
            --dark-color: #1d3557;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            background-color: #f9f9f9;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23e63946' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            background-attachment: fixed;
        }
        
        /* Hero Section */
        .hero {
            position: relative;
            height: 100vh;
            min-height: 600px;
            background: linear-gradient(rgba(29, 53, 87, 0.8), rgba(230, 57, 70, 0.7)), url('blood_pic_1.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            text-align: center;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='180' height='180' viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M81.28 88H68.413l19.298 19.298L81.28 88zm2.107 0h13.226L90 107.838 83.387 88zm15.334 0h12.866l-19.298 19.298L98.72 88zm-32.927-2.207L73.586 78h32.827l.5.5 7.294 7.293L115.414 87l-24.707 24.707-.707.707L64.586 87l1.207-1.207zm2.62.207L74 80.414 79.586 86H68.414L74 80.414zm16 0L90 80.414 95.586 86H84.414L90 80.414zm16 0L106 80.414 111.586 86h-11.172L106 80.414zM87.414 91h11.172L92 96.586 87.414 91zm-16 0h11.172L76 96.586 71.414 91zm32 0h11.172L98 96.586 93.414 91zm16 0h11.172L114 96.586 109.414 91zm-144.007 6.414l7.293-7.293.5-.5h32.827l.707.707L65.414 94 41.414 118l-1.414-1.414 3.293-3.293L24 94h21.414zM38.586 98H27.414L32 93.414 38.586 98z' fill='white' fill-opacity='0.08' fill-rule='evenodd'/%3E%3C/svg%3E");
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 10;
            padding: 0 20px;
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        
        .hero h1 {
            font-size: 5rem;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: 2px;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: fadeInDown 1.2s;
        }
        
        .hero p {
            font-size: 1.6rem;
            margin-bottom: 40px;
            max-width: 700px;
            line-height: 1.6;
            animation: fadeIn 1.5s;
        }
        
        .hero-buttons {
            display: flex;
            gap: 20px;
            animation: fadeInUp 1.8s;
        }
        
        .hero-buttons .btn {
            padding: 15px 35px;
            font-size: 1.1rem;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.5s ease;
        }
        
        .hero-buttons .primary-btn {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 10px 20px rgba(230, 57, 70, 0.3);
        }
        
        .hero-buttons .primary-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(230, 57, 70, 0.4);
        }
        
        .hero-buttons .secondary-btn {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .hero-buttons .secondary-btn:hover {
            background-color: white;
            color: var(--primary-color);
            transform: translateY(-5px);
        }
        
        .blood-drop {
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: rgba(230, 57, 70, 0.7);
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            top: -50px;
            animation: falling linear infinite;
        }
        
        @keyframes falling {
            0% {
                top: -50px;
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                top: calc(100% + 50px);
                opacity: 0;
            }
        }
        
        /* Floating hearts */
        .floating-hearts {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1;
        }
        
        .heart {
            position: absolute;
            color: rgba(230, 57, 70, 0.3);
            font-size: 20px;
            animation: float linear infinite;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-1000%) rotate(720deg);
                opacity: 0;
            }
        }
        
        /* Blood count animation */
        .blood-count-animation {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            z-index: 10;
            color: white;
            font-size: 14px;
            pointer-events: none;
        }
        
        .blood-count-animation .count {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .blood-count-bar {
            width: 200px;
            height: 8px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            overflow: hidden;
            margin: 5px auto;
        }
        
        .blood-count-progress {
            height: 100%;
            background-color: var(--primary-color);
            width: 0%;
            animation: fillProgress 3s ease-in-out infinite;
        }
        
        @keyframes fillProgress {
            0% {
                width: 0%;
            }
            50% {
                width: 75%;
            }
            100% {
                width: 0%;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Additional styles continue... */
        .blood-facts {
            padding: 80px 0;
            background-color: white;
        }
        
        .facts-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .fact-card {
            flex: 0 0 calc(25% - 20px);
            text-align: center;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            background-color: #f8f9fa;
            box-shadow: var(--box-shadow);
            transition: all 0.3s ease;
        }
        
        .fact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .fact-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .testimonials {
            padding: 80px 0;
            background-color: #f8f9fa;
        }
        
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .testimonial-card {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            position: relative;
        }
        
        .testimonial-card::before {
            content: '\201C';
            font-size: 4rem;
            color: rgba(230, 57, 70, 0.1);
            position: absolute;
            top: 10px;
            left: 20px;
        }
        
        .testimonial-content {
            margin-bottom: 20px;
            font-style: italic;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .testimonial-author img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        
        .author-info h4 {
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .cta-section {
            padding: 120px 0;
            background: linear-gradient(rgba(29, 53, 87, 0.9), rgba(230, 57, 70, 0.8)), url('blood-transfusion.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'%3E%3Cpath d='M0 38.59l2.83-2.83 1.41 1.41L1.41 40H0v-1.41zM0 20.83l2.83-2.83 1.41 1.41L1.41 22.24H0v-1.41zM22.24 0l2.83 2.83-1.41 1.41L20.83 1.41V0h1.41zm17.59 0l2.83 2.83-1.41 1.41L38.59 1.41V0h1.41zM20.83 38.59l2.83-2.83 1.41 1.41L22.24 40H20.83v-1.41zM38.59 20.83l2.83-2.83 1.41 1.41-2.83 2.83h-1.41v-1.41zM0 1.41l2.83 2.83-1.41 1.41L0 2.83V1.41zm17.59 0l2.83 2.83-1.41 1.41-2.83-2.83V1.41h1.41zm20 36.82l2.83-2.83 1.41 1.41L38.59 40h-1.41v-1.41z'/%3E%3C/g%3E%3C/svg%3E");
            z-index: 0;
        }
        
        .cta-pattern {
            position: absolute;
            width: 300px;
            height: 300px;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.3;
            border-radius: 50%;
            z-index: 0;
        }
        
        .cta-pattern-1 {
            top: -150px;
            left: -150px;
            animation: rotate 60s linear infinite;
        }
        
        .cta-pattern-2 {
            bottom: -150px;
            right: -150px;
            animation: rotate 80s linear infinite reverse;
        }
        
        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }
        
        .cta-content h2 {
            font-size: 3rem;
            margin-bottom: 30px;
            font-weight: 800;
            letter-spacing: 1px;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .cta-content p {
            margin-bottom: 40px;
            font-size: 1.2rem;
            line-height: 1.8;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .cta-buttons .btn {
            padding: 15px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            letter-spacing: 1px;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.5s ease;
        }
        
        .cta-buttons .primary-btn {
            background-color: white;
            color: var(--primary-color);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .cta-buttons .primary-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
        }
        
        .cta-buttons .secondary-btn {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .cta-buttons .secondary-btn:hover {
            background-color: white;
            color: var(--primary-color);
            transform: translateY(-5px);
        }
        
        .pulse-circle {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background-color: rgba(230, 57, 70, 0.1);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            animation: pulse 4s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: translate(-50%, -50%) scale(0.5);
                opacity: 0.8;
            }
            50% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0;
            }
            100% {
                transform: translate(-50%, -50%) scale(0.5);
                opacity: 0.8;
            }
        }
        
        /* Media Queries */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 3.5rem;
            }
            
            .hero p {
                font-size: 1.2rem;
            }
            
            .fact-card {
                flex: 0 0 calc(50% - 20px);
            }
            
            .about-section {
                flex-direction: column;
            }
            
            .about-image, .about-content {
                flex: auto;
                width: 100%;
            }
            
            .cta-content h2 {
                font-size: 2.2rem;
            }
            
            .hero-buttons, .cta-buttons {
                flex-direction: column;
                gap: 15px;
            }
            
            .hero-buttons .btn, .cta-buttons .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .fact-card {
                flex: 0 0 100%;
            }
            
            .blood-count-animation {
                display: none;
            }
        }

        /* Features Section */
        .features {
            padding: 100px 0 70px;
            position: relative;
            z-index: 10;
            background-color: #fff;
        }
        
        .section-title {
            font-size: 2.5rem;
            color: var(--secondary-color);
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            font-weight: 700;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            margin: 15px auto 0;
            border-radius: 2px;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            background-color: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.5s ease;
            position: relative;
            overflow: hidden;
            border-bottom: 4px solid transparent;
            z-index: 1;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--primary-color);
            z-index: -1;
            transform: scaleY(0);
            transform-origin: bottom;
            transition: transform 0.5s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-15px);
            border-bottom-color: var(--primary-color);
        }
        
        .feature-card:hover::before {
            transform: scaleY(0.15);
        }
        
        .feature-card i {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            transition: all 0.5s ease;
        }
        
        .feature-card:hover i {
            transform: scale(1.2);
            color: var(--primary-color);
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--secondary-color);
            transition: all 0.5s ease;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .feature-card .btn {
            margin-top: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }
        
        .feature-card:hover .btn {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* About Section */
        .about {
            padding: 100px 0;
            background-color: #f9f9f9;
            position: relative;
            z-index: 5;
            overflow: hidden;
        }
        
        .about::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23e63946' fill-opacity='0.03' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='3'/%3E%3Ccircle cx='13' cy='13' r='3'/%3E%3C/g%3E%3C/svg%3E");
            z-index: -1;
        }
        
        .about-section {
            display: flex;
            align-items: center;
            gap: 50px;
        }
        
        .about-image {
            flex: 1;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .about-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.8s ease;
        }
        
        .about-image:hover img {
            transform: scale(1.05);
        }
        
        .about-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 70%, rgba(29, 53, 87, 0.8));
            pointer-events: none;
        }
        
        .about-content {
            flex: 1;
        }
        
        .about-content h3 {
            margin-bottom: 20px;
            color: var(--primary-color);
            font-size: 2rem;
            position: relative;
            padding-bottom: 15px;
        }
        
        .about-content h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .about-content p {
            margin-bottom: 20px;
            line-height: 1.8;
            color: #444;
            font-size: 1.1rem;
        }
        
        .stats-row {
            display: flex;
            gap: 30px;
            margin: 40px 0;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #555;
            font-size: 0.9rem;
        }
        
        /* Blood Facts */
        .blood-facts {
            padding: 100px 0;
            background-color: white;
            position: relative;
            overflow: hidden;
        }
        
        .blood-facts::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23e63946' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
            z-index: 0;
        }
        
        .facts-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 40px;
            position: relative;
            z-index: 2;
        }
        
        .fact-card {
            flex: 0 0 calc(25% - 20px);
            text-align: center;
            padding: 40px 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.5s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .fact-card::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, var(--primary-color) 0%, transparent 70%);
            opacity: 0.1;
            top: -100px;
            right: -100px;
            z-index: -1;
            transition: all 0.5s ease;
        }
        
        .fact-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .fact-card:hover::before {
            transform: scale(1.5);
            opacity: 0.15;
        }
        
        .fact-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .fact-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
            line-height: 1.2;
            background: linear-gradient(45deg, var(--primary-color), #f44336);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            display: inline-block;
        }
        
        .fact-card p {
            color: #555;
            font-size: 1.1rem;
            line-height: 1.5;
        }
        
        /* Testimonials */
        .testimonials {
            padding: 100px 0;
            background-color: #f9f9f9;
            position: relative;
            overflow: hidden;
        }
        
        .testimonials::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='84' height='48' viewBox='0 0 84 48' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h12v6H0V0zm28 8h12v6H28V8zm14-8h12v6H42V0zm14 0h12v6H56V0zm0 8h12v6H56V8zM42 8h12v6H42V8zm0 16h12v6H42v-6zm14-8h12v6H56v-6zm14 0h12v6H70v-6zm0-16h12v6H70V0zM28 32h12v6H28v-6zM14 16h12v6H14v-6zM0 24h12v6H0v-6zm0 8h12v6H0v-6zm14 0h12v6H14v-6zm14 8h12v6H28v-6zm-14 0h12v6H14v-6zm28 0h12v6H42v-6zm14-8h12v6H56v-6zm0-8h12v6H56v-6zm14 8h12v6H70v-6zm0 8h12v6H70v-6zM14 24h12v6H14v-6zm14-8h12v6H28v-6zM14 8h12v6H14V8zM0 8h12v6H0V8z' fill='%23e63946' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
            z-index: 0;
        }
        
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            position: relative;
            z-index: 1;
        }
        
        .testimonial-card {
            background-color: white;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
            position: relative;
            transition: all 0.5s ease;
            overflow: hidden;
        }
        
        .testimonial-card::before {
            content: '\201C';
            font-size: 8rem;
            color: rgba(230, 57, 70, 0.1);
            position: absolute;
            top: -20px;
            left: 20px;
            font-family: serif;
            pointer-events: none;
        }
        
        .testimonial-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), transparent);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }
        
        .testimonial-card:hover::after {
            transform: scaleX(1);
        }
        
        .testimonial-content {
            margin-bottom: 30px;
            font-style: italic;
            color: #444;
            line-height: 1.8;
            position: relative;
            z-index: 1;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .testimonial-author img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin-right: 20px;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.5s ease;
        }
        
        .testimonial-card:hover .testimonial-author img {
            transform: scale(1.1);
            border-color: var(--primary-color);
        }
        
        .author-info h4 {
            margin-bottom: 5px;
            color: var(--secondary-color);
            font-weight: 600;
        }
        
        .author-info p {
            color: var(--primary-color);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="hero">
        <!-- Animated Blood Drops -->
        <?php for ($i = 0; $i < 15; $i++): 
            $size = rand(10, 30);
            $delay = rand(0, 15);
            $duration = rand(10, 20);
            $left = rand(5, 95);
        ?>
        <div class="blood-drop" style="width: <?php echo $size; ?>px; height: <?php echo $size; ?>px; left: <?php echo $left; ?>%; animation-delay: <?php echo $delay; ?>s; animation-duration: <?php echo $duration; ?>s;"></div>
        <?php endfor; ?>
        
        <!-- Floating Hearts -->
        <div class="floating-hearts">
            <?php for ($i = 0; $i < 20; $i++): 
                $size = rand(10, 30);
                $delay = rand(0, 10);
                $duration = rand(15, 30);
                $left = rand(5, 95);
                $bottom = rand(-10, 20);
            ?>
            <i class="fas fa-heart heart" style="font-size: <?php echo $size; ?>px; left: <?php echo $left; ?>%; bottom: <?php echo $bottom; ?>%; animation-delay: <?php echo $delay; ?>s; animation-duration: <?php echo $duration; ?>s;"></i>
            <?php endfor; ?>
        </div>
        
        <div class="hero-content">
            <h1>E Blood Connect</h1>
            <p>Connecting blood donors with those in need, saving lives one donation at a time</p>
            <div class="hero-buttons">
                <a href="request_blood.php" class="btn primary-btn">Request Blood</a>
                <a href="donate.php" class="btn secondary-btn">Donate Blood</a>
            </div>
        </div>
        
        <!-- Blood Donation Counter Animation -->
        <div class="blood-count-animation">
            <div class="count">1,257</div>
            <div>Total blood donations this month</div>
            <div class="blood-count-bar">
                <div class="blood-count-progress"></div>
            </div>
            <div>Help us reach our target of 2,000</div>
        </div>
    </div>

    <section class="features">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Our Services</h2>
            <div class="feature-grid">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-hand-holding-medical"></i>
                    <h3>Request Blood</h3>
                    <p>Create blood requests that can be seen by potential donors in your area. Get matched with compatible donors quickly.</p>
                    <a href="request_blood.php" class="btn primary-btn">Request Now</a>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-heartbeat"></i>
                    <h3>Donate Blood</h3>
                    <p>Respond to blood requests or donate to our blood bank. Your donation can save up to three lives.</p>
                    <a href="donate.php" class="btn primary-btn">Donate Now</a>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-hospital"></i>
                    <h3>Blood Bank</h3>
                    <p>Request blood from our centralized blood bank. We maintain a diverse inventory of blood types.</p>
                    <a href="blood_bank.php" class="btn primary-btn">Visit Bank</a>
                </div>
            </div>
        </div>
    </section>

    <section class="about">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">About E Blood Connect</h2>
            <div class="about-section">
                <div class="about-image" data-aos="fade-right">
                    <img src="360_F_238084232_5XhGUddDZezzJxybvVXzfPp8cOKAuqRp.jpg" alt="Blood Donation">
                </div>
                <div class="about-content" data-aos="fade-left">
                    <h3>Our Mission</h3>
                    <p>E Blood Connect is a platform dedicated to connecting blood donors with individuals in need. Our mission is to ensure that no one faces a shortage of blood during medical emergencies. Through our network of generous donors and blood banks, we strive to make blood readily available to everyone.</p>
                    <p>We believe in creating a community of compassionate individuals who can come together to save lives through blood donation. Join us in our mission to make a difference!</p>
                    
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="stat-value">10K+</div>
                            <div class="stat-label">Donors</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">5K+</div>
                            <div class="stat-label">Lives Saved</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">100+</div>
                            <div class="stat-label">Hospitals</div>
                        </div>
                    </div>
                    
                    <a href="about.php" class="btn primary-btn">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <section class="blood-facts">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Blood Donation Facts</h2>
            <div class="facts-container">
                <div class="fact-card" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-heartbeat fact-icon"></i>
                    <div class="fact-number">3</div>
                    <p>One donation can save up to 3 lives</p>
                </div>
                <div class="fact-card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-hospital-user fact-icon"></i>
                    <div class="fact-number">4.5M</div>
                    <p>Americans need blood transfusions each year</p>
                </div>
                <div class="fact-card" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-tint fact-icon"></i>
                    <div class="fact-number">32K</div>
                    <p>Pints of blood used each day in the U.S.</p>
                </div>
                <div class="fact-card" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-users fact-icon"></i>
                    <div class="fact-number">38%</div>
                    <p>Of the population is eligible to donate blood</p>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Testimonials</h2>
            <div class="testimonial-grid">
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-content">
                        <p>I was in a critical condition after an accident and needed blood urgently. Thanks to E Blood Connect, I found donors quickly. Forever grateful for this amazing platform!</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="360_F_964324453_d0Z9QZzvkuoA1X5eclz1XuMdlBek24nW.jpg" alt="Sarah">
                        <div class="author-info">
                            <h4>Sarah Williams</h4>
                            <p>Blood Recipient</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-content">
                        <p>As a regular blood donor, E Blood Connect makes it easy for me to find people who need my blood type. The process is streamlined and the team is always helpful.</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="360_F_238084232_5XhGUddDZezzJxybvVXzfPp8cOKAuqRp.jpg" alt="John">
                        <div class="author-info">
                            <h4>John Doe</h4>
                            <p>Regular Donor</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-content">
                        <p>Our hospital has partnered with E Blood Connect for emergency blood requirements. Their quick response and reliable service have helped save countless lives.</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="qms-software-for-healthcare-industry.jpg" alt="Dr. Michael">
                        <div class="author-info">
                            <h4>Dr. Michael Johnson</h4>
                            <p>Hospital Director</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="pulse-circle"></div>
        <div class="cta-pattern cta-pattern-1"></div>
        <div class="cta-pattern cta-pattern-2"></div>
        
        <div class="container">
            <div class="cta-content" data-aos="fade-up">
                <h2>Ready to Make a Difference?</h2>
                <p>Your blood donation can save lives. Join our community of donors today and be a hero for someone in need.</p>
                <div class="cta-buttons">
                    <a href="register.php" class="btn primary-btn">Register Now</a>
                    <a href="about.php" class="btn secondary-btn">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="js/script.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-out',
            once: true,
            offset: 100
        });
        
        // Add animated blood drops function
        function createBloodDrop() {
            const drop = document.createElement('div');
            drop.classList.add('blood-drop');
            
            // Random size
            const size = Math.random() * 20 + 10;
            drop.style.width = `${size}px`;
            drop.style.height = `${size}px`;
            
            // Random position
            drop.style.left = `${Math.random() * 100}%`;
            
            // Random animation duration
            drop.style.animationDuration = `${Math.random() * 10 + 10}s`;
            
            document.querySelector('.hero').appendChild(drop);
            
            // Remove after animation completes
            setTimeout(() => {
                drop.remove();
            }, parseFloat(drop.style.animationDuration) * 1000);
        }
        
        // Create new drops every few seconds
        setInterval(createBloodDrop, 3000);
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const target = document.querySelector(this.getAttribute('href'));
                
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html> 