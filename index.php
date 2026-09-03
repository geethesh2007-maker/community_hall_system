<?php

// Public landing page
// No login or database connection is required here.

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Harmony Hall Booking System - Find and book community halls easily."
    >

    <title>
        Harmony Hall Booking System
    </title>


    <!-- Main stylesheet -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        /* =========================================
           LANDING PAGE
        ========================================== */

        html {

            scroll-behavior: smooth;

            scroll-padding-top: 80px;

        }


        body {

            margin: 0;

            background: #f4f6f9;

        }



        /* =========================================
           NAVIGATION
        ========================================== */

        .landing-navbar {

            position: fixed;

            top: 0;

            left: 0;

            width: 100%;

            z-index: 1000;

            background: rgba(20, 35, 75, 0.96);

            padding: 14px 5%;

            box-shadow:
                0 2px 12px rgba(0, 0, 0, 0.18);

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            box-sizing: border-box;

        }



        /* =========================================
           BRAND / LOGO
        ========================================== */

        .navbar-brand {

            color: white;

            font-size: 21px;

            font-weight: bold;

            text-decoration: none;

            white-space: nowrap;

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .navbar-brand:hover {

            color: white;

            text-decoration: none;

        }


        .brand-icon {

            font-size: 28px;

            color: white;

            line-height: 1;

        }



        /* =========================================
           NAVIGATION LINKS
        ========================================== */

        .landing-nav-links {

            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 6px;

            flex-wrap: wrap;

        }


        .landing-nav-links a {

            color: white;

            text-decoration: none;

            padding: 9px 13px;

            border-radius: 6px;

            font-size: 14px;

            font-weight: 600;

            transition: 0.2s ease;

        }


        .landing-nav-links a:hover {

            background: rgba(255, 255, 255, 0.15);

            color: white;

            text-decoration: none;

        }


        .nav-login {

            border:
                1px solid rgba(255, 255, 255, 0.5);

        }


        .nav-register {

            background: white;

            color: #1f3c88 !important;

        }


        .nav-register:hover {

            background: #eef3ff !important;

            color: #162d66 !important;

        }



        /* =========================================
           HERO SECTION
        ========================================== */

        .hero-section {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            text-align: center;

            padding: 120px 20px 80px;

            position: relative;

            color: white;

            background-image:

                linear-gradient(
                    rgba(10, 25, 55, 0.72),
                    rgba(10, 25, 55, 0.78)
                ),

                url("https://img.magnific.com/free-photo/restaurant-hall-with-round-square-tables-some-chairs-plants_140725-8030.jpg?semt=ais_hybrid&w=740&q=80");

            background-size: cover;

            background-position: center;

            background-repeat: no-repeat;

            background-attachment: fixed;

        }


        .hero-content {

            max-width: 850px;

            margin: auto;

        }


        .hero-content h1 {

            color: white;

            font-size: 52px;

            line-height: 1.15;

            margin-bottom: 20px;

        }


        .hero-content h1 span {

            color: #dbe4ff;

        }


        .hero-content p {

            max-width: 700px;

            margin: 0 auto 30px;

            color: #f1f4ff;

            font-size: 19px;

            line-height: 1.7;

        }


        .hero-buttons {

            display: flex;

            justify-content: center;

            gap: 15px;

            flex-wrap: wrap;

        }


        .hero-btn {

            display: inline-block;

            padding: 13px 25px;

            border-radius: 7px;

            text-decoration: none;

            font-weight: bold;

            transition: 0.2s ease;

        }


        .hero-btn-primary {

            background: #1f3c88;

            color: white;

        }


        .hero-btn-primary:hover {

            background: #162d66;

            color: white;

            text-decoration: none;

            transform: translateY(-2px);

        }


        .hero-btn-light {

            background: white;

            color: #1f3c88;

        }


        .hero-btn-light:hover {

            background: #eef3ff;

            color: #162d66;

            text-decoration: none;

            transform: translateY(-2px);

        }



        /* =========================================
           GENERAL SECTION
        ========================================== */

        .landing-section {

            padding: 85px 20px;

        }


        .section-container {

            width: 90%;

            max-width: 1150px;

            margin: auto;

        }


        .section-heading {

            text-align: center;

            margin-bottom: 45px;

        }


        .section-heading h2 {

            color: #1f3c88;

            font-size: 34px;

            margin-bottom: 10px;

        }


        .section-heading p {

            max-width: 700px;

            margin: auto;

            color: #6c757d;

            font-size: 16px;

        }



        /* =========================================
           HALL PHOTO GALLERY
        ========================================== */

        #hall-gallery {

            background: white;

            overflow: hidden;

        }


        .hall-slider-wrapper {

            width: 100%;

            overflow: hidden;

            position: relative;

        }


        .hall-slider-track {

            display: flex;

            gap: 22px;

            width: max-content;

            animation:
                moveHallPhotos
                30s
                linear
                infinite;

        }


        .hall-slider-wrapper:hover .hall-slider-track {

            animation-play-state: paused;

        }


        .hall-photo-card {

            width: 330px;

            flex-shrink: 0;

            background: #f8f9fc;

            border-radius: 12px;

            overflow: hidden;

            box-shadow:
                0 5px 18px rgba(
                    0,
                    0,
                    0,
                    0.10
                );

            transition: 0.3s ease;

        }


        .hall-photo-card:hover {

            transform:
                translateY(-6px);

        }


        .hall-photo-image {

            width: 100%;

            height: 230px;

            display: block;

            object-fit: cover;

        }


        .hall-photo-content {

            padding: 18px;

        }


        .hall-photo-content h3 {

            margin: 0 0 8px;

            color: #1f3c88;

            font-size: 19px;

        }


        .hall-photo-content p {

            margin: 0;

            color: #666;

            line-height: 1.6;

            font-size: 14px;

        }


        /* =========================================
           SLIDING ANIMATION
        ========================================== */

        @keyframes moveHallPhotos {

            from {

                transform:
                    translateX(0);

            }


            to {

                transform:
                    translateX(-50%);

            }

        }



        /* =========================================
           FEATURES
        ========================================== */

        #features {

            background: white;

        }


        .feature-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;

        }


        .feature-card {

            background: #f8f9fc;

            padding: 30px;

            border-radius: 12px;

            text-align: center;

            border-top:
                4px solid #1f3c88;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.06);

            transition: 0.2s ease;

        }


        .feature-card:hover {

            transform: translateY(-5px);

            box-shadow:
                0 8px 22px rgba(0, 0, 0, 0.10);

        }


        .feature-icon {

            font-size: 40px;

            margin-bottom: 15px;

            color: #1f3c88;

        }


        .feature-card h3 {

            color: #1f3c88;

            margin-bottom: 10px;

        }


        .feature-card p {

            color: #666;

            margin: 0;

        }



        /* =========================================
           ABOUT
        ========================================== */

        #about {

            background: #f4f6f9;

        }


        .about-grid {

            display: grid;

            grid-template-columns:
                1.2fr 1fr;

            gap: 40px;

            align-items: center;

        }


        .about-content h2 {

            color: #1f3c88;

            font-size: 34px;

            margin-bottom: 20px;

        }


        .about-content p {

            color: #555;

            line-height: 1.8;

            margin-bottom: 15px;

        }


        .about-box {

            background: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

            border-left:
                5px solid #1f3c88;

        }


        .about-box h3 {

            color: #1f3c88;

            margin-top: 0;

        }


        .about-box p {

            color: #666;

            line-height: 1.7;

        }



        /* =========================================
           SERVICES
        ========================================== */

        #services {

            background: white;

        }


        .service-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 25px;

        }


        .service-card {

            background: #f8f9fc;

            padding: 28px;

            border-radius: 10px;

            border:
                1px solid #e1e5eb;

            transition: 0.2s ease;

        }


        .service-card:hover {

            border-color: #1f3c88;

            transform: translateY(-3px);

        }


        .service-card h3 {

            color: #1f3c88;

            margin-top: 0;

        }


        .service-card p {

            color: #666;

            line-height: 1.7;

        }



        /* =========================================
           FACILITIES
        ========================================== */

        #facilities {

            background: #f4f6f9;

        }


        .facility-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

        }


        .facility-card {

            background: white;

            padding: 25px 18px;

            border-radius: 10px;

            text-align: center;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.06);

            transition: 0.2s ease;

        }


        .facility-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 8px 20px rgba(0, 0, 0, 0.10);

        }


        .facility-card .facility-icon {

            font-size: 35px;

            margin-bottom: 10px;

            color: #1f3c88;

        }


        .facility-card h3 {

            color: #1f3c88;

            font-size: 17px;

            margin: 0;

        }



        /* =========================================
           CALL TO ACTION
        ========================================== */

        .cta-section {

            padding: 80px 20px;

            background:

                linear-gradient(
                    135deg,
                    #1f3c88,
                    #315bb5
                );

            text-align: center;

            color: white;

        }


        .cta-section h2 {

            color: white;

            font-size: 34px;

            margin-bottom: 12px;

        }


        .cta-section p {

            color: #e5ecff;

            max-width: 650px;

            margin: 0 auto 25px;

        }



        /* =========================================
           FOOTER
        ========================================== */

        .landing-footer {

            background: #162d66;

            color: white;

            text-align: center;

            padding: 25px 20px;

        }


        .landing-footer p {

            margin: 0;

            color: #dbe4ff;

            font-size: 14px;

        }



        /* =========================================
           RESPONSIVE
        ========================================== */

        @media (max-width: 1000px) {

            .feature-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }


            .facility-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }


            .about-grid {

                grid-template-columns: 1fr;

            }

        }


        @media (max-width: 750px) {

            .landing-navbar {

                position: relative;

                flex-direction: column;

                padding: 15px;

            }


            .navbar-brand {

                font-size: 19px;

            }


            .landing-nav-links {

                justify-content: center;

            }


            .hero-section {

                padding-top: 70px;

                min-height: 85vh;

                background-attachment: scroll;

            }


            .hero-content h1 {

                font-size: 38px;

            }


            .hero-content p {

                font-size: 16px;

            }


            .feature-grid {

                grid-template-columns: 1fr;

            }


            .service-grid {

                grid-template-columns: 1fr;

            }


            .facility-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }


            .hall-photo-card {

                width: 270px;

            }


            .hall-photo-image {

                height: 190px;

            }

        }


        @media (max-width: 480px) {

            .landing-nav-links a {

                padding: 8px 9px;

                font-size: 13px;

            }


            .navbar-brand {

                font-size: 17px;

            }


            .brand-icon {

                font-size: 24px;

            }


            .hero-content h1 {

                font-size: 31px;

            }


            .hero-buttons {

                flex-direction: column;

            }


            .hero-btn {

                width: 100%;

                box-sizing: border-box;

            }


            .facility-grid {

                grid-template-columns: 1fr;

            }


            .landing-section {

                padding: 60px 15px;

            }


            .section-heading h2 {

                font-size: 28px;

            }


            .hall-photo-card {

                width: 240px;

            }


            .hall-photo-image {

                height: 170px;

            }

        }

    </style>

</head>


<body>


    <!-- =========================================
         NAVIGATION
    ========================================== -->

    <header class="landing-navbar">

        <a
            href="#home"
            class="navbar-brand"
        >

            <i
                class="fa-solid fa-building-columns brand-icon"
                aria-hidden="true"
            ></i>

            <span>
                Harmony Hall Booking System
            </span>

        </a>


        <nav class="landing-nav-links">

            <a href="#home">
                Home
            </a>

            <a href="#hall-gallery">
                Halls
            </a>

            <a href="#features">
                Features
            </a>

            <a href="#about">
                About
            </a>

            <a href="#services">
                Services
            </a>

            <a href="#facilities">
                Facilities
            </a>

            <a
                href="auth/login.php"
                class="nav-login"
            >
                Login
            </a>

            <a
                href="auth/register.php"
                class="nav-register"
            >
                Register
            </a>

        </nav>

    </header>


    <main>


        <!-- =========================================
             HERO / HOME
        ========================================== -->

        <section
            id="home"
            class="hero-section"
        >

            <div class="hero-content">

                <h1>

                    Find the Perfect

                    <span>
                        Community Hall
                    </span>

                    for Your Event

                </h1>


                <p>

                    A simple and convenient community hall
                    booking system that helps customers discover
                    halls, view facilities, check details and
                    submit booking requests online.

                </p>


                <div class="hero-buttons">

                    <a
                        href="auth/register.php"
                        class="hero-btn hero-btn-primary"
                    >

                        <i class="fa-solid fa-user-plus"></i>

                        Get Started

                    </a>


                    <a
                        href="#features"
                        class="hero-btn hero-btn-light"
                    >

                        <i class="fa-solid fa-compass"></i>

                        Explore Features

                    </a>

                </div>

            </div>

        </section>



        <!-- =========================================
             HALL PHOTO GALLERY
        ========================================== -->

        <section
            id="hall-gallery"
            class="landing-section"
        >

            <div class="section-container">


                <div class="section-heading">

                    <h2>
                        Explore Our Halls
                    </h2>

                    <p>

                        Take a look at some of the beautiful
                        community halls available for your
                        events and special occasions.

                    </p>

                </div>


                <div class="hall-slider-wrapper">

                    <div class="hall-slider-track">


                        <!-- HALL 1 -->

                        <div class="hall-photo-card">

                            <img
                                src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=80"
                                alt="Grand Community Hall"
                                class="hall-photo-image"
                            >

                            <div class="hall-photo-content">

                                <h3>
                                    Grand Community Hall
                                </h3>

                                <p>

                                    A spacious venue suitable for
                                    weddings, celebrations and
                                    large events.

                                </p>

                            </div>

                        </div>



                        <!-- HALL 2 -->

                        <div class="hall-photo-card">

                            <img
                                src="https://images.unsplash.com/photo-1507504031003-b417219a0fde?auto=format&fit=crop&w=900&q=80"
                                alt="Modern Event Hall"
                                class="hall-photo-image"
                            >

                            <div class="hall-photo-content">

                                <h3>
                                    Modern Event Hall
                                </h3>

                                <p>

                                    A stylish and comfortable
                                    space for meetings and
                                    special events.

                                </p>

                            </div>

                        </div>



                        <!-- HALL 3 -->

                        <div class="hall-photo-card">

                            <img
                                src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=900&q=80"
                                alt="Celebration Hall"
                                class="hall-photo-image"
                            >

                            <div class="hall-photo-content">

                                <h3>
                                    Celebration Hall
                                </h3>

                                <p>

                                    A beautiful location for
                                    weddings, receptions and
                                    family celebrations.

                                </p>

                            </div>

                        </div>



                        <!-- HALL 4 -->

                        <div class="hall-photo-card">

                            <img
                                src="https://images.unsplash.com/photo-1507504031003-b417219a0fde?auto=format&fit=crop&w=900&q=80"
                                alt="Conference Hall"
                                class="hall-photo-image"
                            >

                            <div class="hall-photo-content">

                                <h3>
                                    Conference Hall
                                </h3>

                                <p>

                                    A professional venue suitable
                                    for meetings, seminars and
                                    conferences.

                                </p>

                            </div>

                        </div>



                        <!-- HALL 5 -->

                        <div class="hall-photo-card">

                            <img
                                src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=80"
                                alt="Premium Function Hall"
                                class="hall-photo-image"
                            >

                            <div class="hall-photo-content">

                                <h3>
                                    Premium Function Hall
                                </h3>

                                <p>

                                    A premium and spacious venue
                                    designed for memorable
                                    occasions.

                                </p>

                            </div>

                        </div>



                        <!-- DUPLICATE HALLS FOR SMOOTH LOOP -->

                        <div class="hall-photo-card">

                            <img
                                src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=80"
                                alt="Grand Community Hall"
                                class="hall-photo-image"
                            >

                            <div class="hall-photo-content">

                                <h3>
                                    Grand Community Hall
                                </h3>

                                <p>

                                    A spacious venue suitable for
                                    weddings, celebrations and
                                    large events.

                                </p>

                            </div>

                        </div>


                        <div class="hall-photo-card">

                            <img
                                src="https://images.unsplash.com/photo-1507504031003-b417219a0fde?auto=format&fit=crop&w=900&q=80"
                                alt="Modern Event Hall"
                                class="hall-photo-image"
                            >

                            <div class="hall-photo-content">

                                <h3>
                                    Modern Event Hall
                                </h3>

                                <p>

                                    A stylish and comfortable
                                    space for meetings and
                                    special events.

                                </p>

                            </div>

                        </div>


                        <div class="hall-photo-card">

                            <img
                                src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=900&q=80"
                                alt="Celebration Hall"
                                class="hall-photo-image"
                            >

                            <div class="hall-photo-content">

                                <h3>
                                    Celebration Hall
                                </h3>

                                <p>

                                    A beautiful location for
                                    weddings, receptions and
                                    family celebrations.

                                </p>

                            </div>

                        </div>


                        <div class="hall-photo-card">

                            <img
                                src="https://images.unsplash.com/photo-1507504031003-b417219a0fde?auto=format&fit=crop&w=900&q=80"
                                alt="Conference Hall"
                                class="hall-photo-image"
                            >

                            <div class="hall-photo-content">

                                <h3>
                                    Conference Hall
                                </h3>

                                <p>

                                    A professional venue suitable
                                    for meetings, seminars and
                                    conferences.

                                </p>

                            </div>

                        </div>


                        <div class="hall-photo-card">

                            <img
                                src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=80"
                                alt="Premium Function Hall"
                                class="hall-photo-image"
                            >

                            <div class="hall-photo-content">

                                <h3>
                                    Premium Function Hall
                                </h3>

                                <p>

                                    A premium and spacious venue
                                    designed for memorable
                                    occasions.

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        <!-- =========================================
             FEATURES
        ========================================== -->

        <section
            id="features"
            class="landing-section"
        >

            <div class="section-container">

                <div class="section-heading">

                    <h2>
                        Features
                    </h2>

                    <p>
                        Everything you need to make community
                        hall booking simple and convenient.
                    </p>

                </div>


                <div class="feature-grid">


                    <div class="feature-card">

                        <div class="feature-icon">

                            <i class="fa-solid fa-building"></i>

                        </div>

                        <h3>
                            Browse Halls
                        </h3>

                        <p>
                            View available community halls
                            and compare their important details.
                        </p>

                    </div>


                    <div class="feature-card">

                        <div class="feature-icon">

                            <i class="fa-solid fa-calendar-check"></i>

                        </div>

                        <h3>
                            Easy Booking
                        </h3>

                        <p>
                            Submit your hall booking request
                            quickly through the online system.
                        </p>

                    </div>


                    <div class="feature-card">

                        <div class="feature-icon">

                            <i class="fa-solid fa-clipboard-list"></i>

                        </div>

                        <h3>
                            Booking Management
                        </h3>

                        <p>
                            Customers can keep track of their
                            booking requests and their status.
                        </p>

                    </div>


                    <div class="feature-card">

                        <div class="feature-icon">

                            <i class="fa-solid fa-shield-halved"></i>

                        </div>

                        <h3>
                            Secure Accounts
                        </h3>

                        <p>
                            Separate customer and administrator
                            access keeps the system organized.
                        </p>

                    </div>


                    <div class="feature-card">

                        <div class="feature-icon">

                            <i class="fa-solid fa-user-tie"></i>

                        </div>

                        <h3>
                            Admin Management
                        </h3>

                        <p>
                            Administrators can manage halls,
                            users and booking requests.
                        </p>

                    </div>


                    <div class="feature-card">

                        <div class="feature-icon">

                            <i class="fa-solid fa-images"></i>

                        </div>

                        <h3>
                            Hall Videos
                        </h3>

                        <p>
                            Hall videos help customers understand
                            the available venue before booking.
                        </p>

                    </div>

                </div>

            </div>

        </section>



        <!-- =========================================
             ABOUT
        ========================================== -->

        <section
            id="about"
            class="landing-section"
        >

            <div class="section-container">

                <div class="about-grid">


                    <div class="about-content">

                        <h2>
                            About Our System
                        </h2>

                        <p>
                            The Harmony Hall Booking System is
                            designed to make the process of finding
                            and requesting a community hall easier
                            for customers.
                        </p>

                        <p>
                            Instead of depending on manual enquiries,
                            customers can browse available halls,
                            view their facilities and submit booking
                            requests online.
                        </p>

                        <p>
                            Administrators can manage community halls,
                            review booking requests and update booking
                            statuses through the administrative section.
                        </p>

                    </div>


                    <div class="about-box">

                        <h3>

                            <i class="fa-solid fa-bullseye"></i>

                            Our Goal

                        </h3>

                        <p>
                            To provide a simple, organized and
                            user-friendly platform for managing
                            community hall bookings.
                        </p>

                        <p>
                            The system connects customers and
                            administrators in one centralized
                            booking platform.
                        </p>

                    </div>

                </div>

            </div>

        </section>



        <!-- =========================================
             SERVICES
        ========================================== -->

        <section
            id="services"
            class="landing-section"
        >

            <div class="section-container">

                <div class="section-heading">

                    <h2>
                        Services
                    </h2>

                    <p>
                        Services provided through the Community
                        Hall Booking System.
                    </p>

                </div>


                <div class="service-grid">


                    <div class="service-card">

                        <h3>

                            <i class="fa-solid fa-magnifying-glass"></i>

                            Hall Discovery

                        </h3>

                        <p>
                            Customers can browse the community halls
                            available through the system and view
                            important information such as location,
                            capacity and price.
                        </p>

                    </div>


                    <div class="service-card">

                        <h3>

                            <i class="fa-solid fa-calendar-plus"></i>

                            Online Booking Requests

                        </h3>

                        <p>
                            Customers can select a suitable hall,
                            provide booking details and submit a
                            request without visiting the office
                            manually.
                        </p>

                    </div>


                    <div class="service-card">

                        <h3>

                            <i class="fa-solid fa-clock"></i>

                            Booking Status

                        </h3>

                        <p>
                            Customers can monitor their booking
                            requests and see whether they are
                            pending, approved or rejected.
                        </p>

                    </div>


                    <div class="service-card">

                        <h3>

                            <i class="fa-solid fa-building-shield"></i>

                            Hall Administration

                        </h3>

                        <p>
                            Administrators can add, edit and manage
                            community halls and maintain their
                            available information.
                        </p>

                    </div>


                    <div class="service-card">

                        <h3>

                            <i class="fa-solid fa-list-check"></i>

                            Booking Administration

                        </h3>

                        <p>
                            Administrators can review booking
                            requests and approve or reject them
                            according to the system requirements.
                        </p>

                    </div>


                    <div class="service-card">

                        <h3>

                            <i class="fa-solid fa-users"></i>

                            User Management

                        </h3>

                        <p>
                            Administrators can manage registered
                            users and maintain an organized
                            community hall booking environment.
                        </p>

                    </div>

                </div>

            </div>

        </section>



        <!-- =========================================
             FACILITIES
        ========================================== -->

        <section
            id="facilities"
            class="landing-section"
        >

            <div class="section-container">

                <div class="section-heading">

                    <h2>
                        Hall Facilities
                    </h2>

                    <p>
                        Community halls can provide a range of
                        facilities suitable for different events.
                    </p>

                </div>


                <div class="facility-grid">


                    <div class="facility-card">

                        <div class="facility-icon">

                            <i class="fa-solid fa-snowflake"></i>

                        </div>

                        <h3>
                            Air Conditioning
                        </h3>

                    </div>


                    <div class="facility-card">

                        <div class="facility-icon">

                            <i class="fa-solid fa-car"></i>

                        </div>

                        <h3>
                            Parking
                        </h3>

                    </div>


                    <div class="facility-card">

                        <div class="facility-icon">

                            <i class="fa-solid fa-microphone"></i>

                        </div>

                        <h3>
                            Stage
                        </h3>

                    </div>


                    <div class="facility-card">

                        <div class="facility-icon">

                            <i class="fa-solid fa-chair"></i>

                        </div>

                        <h3>
                            Chairs
                        </h3>

                    </div>


                    <div class="facility-card">

                        <div class="facility-icon">

                            <i class="fa-solid fa-table"></i>

                        </div>

                        <h3>
                            Tables
                        </h3>

                    </div>


                    <div class="facility-card">

                        <div class="facility-icon">

                            <i class="fa-solid fa-volume-high"></i>

                        </div>

                        <h3>
                            Sound System
                        </h3>

                    </div>


                    <div class="facility-card">

                        <div class="facility-icon">

                            <i class="fa-solid fa-lightbulb"></i>

                        </div>

                        <h3>
                            Lighting
                        </h3>

                    </div>


                    <div class="facility-card">

                        <div class="facility-icon">

                            <i class="fa-solid fa-people-roof"></i>

                        </div>

                        <h3>
                            Spacious Venue
                        </h3>

                    </div>

                </div>

            </div>

        </section>



        <!-- =========================================
             CALL TO ACTION
        ========================================== -->

        <section class="cta-section">

            <h2>
                Ready to Book a Community Hall?
            </h2>

            <p>
                Create your account and start exploring
                available community halls today.
            </p>

            <a
                href="auth/register.php"
                class="hero-btn hero-btn-light"
            >

                <i class="fa-solid fa-user-plus"></i>

                Create Your Account

            </a>

        </section>

    </main>



    <!-- =========================================
         FOOTER
    ========================================== -->

    <footer class="landing-footer">

        <p>

            &copy;

            <?php echo date("Y"); ?>

            Harmony Hall Booking System.

            All Rights Reserved.

        </p>

    </footer>


</body>

</html>