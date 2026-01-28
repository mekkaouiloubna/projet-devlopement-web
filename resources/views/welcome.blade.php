@extends('layouts.app')

@section('title', 'Accueil - Data Center Resource Manager')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <i class="fas fa-server"></i> Gestionnaire de Ressources Data Center
                </h1>
                <p class="hero-description">
                    Une plateforme simple et efficace pour gérer les ressources informatiques
                    de votre centre de données. Réservez, allouez et suivez vos ressources en temps réel.
                </p>
                <div class="hero-actions">
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> S'inscrire
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </a>
                    @else
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-tachometer-alt"></i> Tableau Admin
                            </a>
                        @elseif(auth()->user()->isResponsable())
                            <a href="{{ route('responsable.dashboard') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-tachometer-alt"></i> Tableau Responsable
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        @endif
                        <a href="{{ route('resources.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-server"></i> Voir les ressources
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Fonctionnalités principales</h2>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <h3>Gestion des ressources</h3>
                    <p>Serveurs, machines virtuelles, stockage et équipements réseau</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Réservation</h3>
                    <p>Système de réservation avec vérification des disponibilités</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3>Multi-rôles</h3>
                    <p>4 profils utilisateurs avec permissions différenciées</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Tableaux de bord</h3>
                    <p>Statistiques et rapports d'utilisation</p>
                </div>
            </div>
        </div>
    </section>

    <!-- User Roles Section -->
    <section class="roles-section">
        <div class="container">
            <h2 class="section-title">Profils utilisateurs</h2>

            <div class="roles-grid">
                <!-- Invité -->
                <div class="role-card">
                    <div class="role-header">
                        <i class="fas fa-eye"></i>
                        <h3>Invé</h3>
                    </div>
                    <ul class="role-features">
                        <li><i class="fas fa-check text-success"></i> Voir les ressources</li>
                        <li><i class="fas fa-check text-success"></i> Demander un compte</li>
                        <li><i class="fas fa-times text-danger"></i> Réserver</li>
                    </ul>
                </div>

                <!-- Utilisateur -->
                <div class="role-card">
                    <div class="role-header">
                        <i class="fas fa-user"></i>
                        <h3>Utilisateur</h3>
                    </div>
                    <ul class="role-features">
                        <li><i class="fas fa-check text-success"></i> Réserver des ressources</li>
                        <li><i class="fas fa-check text-success"></i> Suivre ses réservations</li>
                        <li><i class="fas fa-check text-success"></i> Recevoir des notifications</li>
                    </ul>
                </div>

                <!-- Responsable -->
                <div class="role-card">
                    <div class="role-header">
                        <i class="fas fa-user-shield"></i>
                        <h3>Responsable</h3>
                    </div>
                    <ul class="role-features">
                        <li><i class="fas fa-check text-success"></i> Gérer ses ressources</li>
                        <li><i class="fas fa-check text-success"></i> Approuver les réservations</li>
                        <li><i class="fas fa-check text-success"></i> Modérer les messages</li>
                    </ul>
                </div>

                <!-- Admin -->
                <div class="role-card">
                    <div class="role-header">
                        <i class="fas fa-user-cog"></i>
                        <h3>Administrateur</h3>
                    </div>
                    <ul class="role-features">
                        <li><i class="fas fa-check text-success"></i> Gérer tout le système</li>
                        <li><i class="fas fa-check text-success"></i> Administrer les utilisateurs</li>
                        <li><i class="fas fa-check text-success"></i> Voir les statistiques</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    @guest
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Prêt à commencer ?</h2>
                    <p>Rejoignez notre plateforme pour une meilleure gestion de vos ressources</p>
                    <div class="cta-actions">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Créer un compte
                        </a>
                        <a href="{{ route('resources.index') }}" class="btn btn-outline btn-lg">
                            <i class="fas fa-search"></i> Explorer les ressources
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endguest
    
    <style>
        .hero-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            text-align: center;
        }

        .hero-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .hero-description {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 40px;
            opacity: 0.9;
        }

        .hero-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .features-section {
            padding: 80px 0;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
            color: var(--primary-color);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .feature-card {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .roles-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .role-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .role-header {
            padding: 25px;
            text-align: center;
            background: var(--light-color);
        }

        .role-header i {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        .role-header h3 {
            color: var(--primary-color);
            margin: 0;
        }

        .role-features {
            list-style: none;
            padding: 25px;
            margin: 0;
        }

        .role-features li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cta-section {
            padding: 80px 0;
            background: white;
            text-align: center;
        }

        .cta-content h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .cta-content p {
            color: var(--gray-color);
            margin-bottom: 40px;
            font-size: 1.1rem;
        }

        .cta-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-actions,
            .cta-actions {
                flex-direction: column;
                align-items: center;
            }

            .hero-actions .btn,
            .cta-actions .btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
@endsection