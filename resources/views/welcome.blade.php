@extends('layouts.app')

@section('title', 'Accueil - Data Center Resource Manager')

@section('content')
    <!-- Hero Section -->
    <section class="header-sec mt-6">
        <div class="header-sec-title">
            <h1>
                <i class="fa-duotone fa-solid fa-house"></i> Gestionnaire de Ressources Data Center
            </h1>
            <p>
                Une plateforme simple et efficace pour gérer les ressources informatiques
                de votre centre de données. Réservez, allouez et suivez vos ressources en temps réel.
            </p>
        </div>

        <div class="header-sec-actions">
            @guest
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg trans-up">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg trans-up">
                    <i class="fas fa-user-plus"></i> S'inscrire
                </a>
            @else
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('dashboard.admin') }}" class="btn btn-primary btn-lg trans-up">
                        <i class="fas fa-tachometer-alt"></i> Tableau Admin
                    </a>
                @elseif(auth()->user()->isResponsable())
                    <a href="{{ route('responsable.dashboard') }}" class="btn btn-primary btn-lg trans-up">
                        <i class="fas fa-tachometer-alt"></i> Tableau Responsable
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg trans-up">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                @endif
                <a href="{{ route('resources.index') }}" class="btn btn-primary btn-lg trans-up">
                    <i class="fas fa-server"></i> Voir les ressources
                </a>
            @endguest
        </div>
    </section>

    <section class="section-content">
        <h2 class="section-title">Accès Rapide</h2>
        <ul class="grid list-none">
            @guest
                <li class="section-card">
                    <div class="section-icon"><i class="fas fa-list"></i></div>
                    <h3>Ressources</h3>
                    <p>Consultez toutes les ressources disponibles dans le Data Center.</p>
                    <a href="{{ route('resources.index') }}" class="btn btn-outline">Accéder</a>
                </li>
                <li class="section-card">
                    <div class="section-icon"><i class="fas fa-sign-in-alt"></i></div>
                    <h3>Connexion</h3>
                    <p>Connectez-vous pour accéder à votre tableau de bord et gérer vos réservations.</p>
                    <a href="{{ route('login') }}" class="btn btn-outline">Se connecter</a>
                </li>
                <li class="section-card">
                    <div class="section-icon"><i class="fas fa-user-plus"></i></div>
                    <h3>S'inscrire</h3>
                    <p>Créez un compte pour gérer vos ressources et réservations facilement.</p>
                    <a href="{{ route('register') }}" class="btn btn-outline">S'inscrire</a>
                </li>
            @else
                @if(auth()->user()->isAdmin())
                    <li class="section-card">
                        <div class="section-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <h3>Dashboard Admin</h3>
                        <p>Accédez à la gestion complète des utilisateurs et ressources.</p>
                        <a href="{{ route('dashboard.admin') }}" class="btn btn-outline">Accéder</a>
                    </li>
                    <li class="section-card">
                        <div class="section-icon"><i class="fas fa-users"></i></div>
                        <h3>Users</h3>
                        <p>Gérez tous les utilisateurs inscrits et leurs permissions.</p>
                        <a href="{{ route('users.index') }}" class="btn btn-outline">Accéder</a>
                    </li>
                    <li class="section-card">
                        <div class="section-icon"><i class="fas fa-tools"></i></div>
                        <h3>Maintenance</h3>
                        <p>Surveillez et effectuez la maintenance des resources.</p>
                        <a href="{{ route('maintenance.index') }}" class="btn btn-outline">Accéder</a>
                    </li>
                @elseif(auth()->user()->isResponsable())
                    <li class="section-card">
                        <div class="section-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <h3>Dashboard</h3>
                        <p>Visualisez vos ressources et approuvez les réservations.</p>
                        <a href="{{ route('responsable.dashboard') }}" class="btn btn-outline">Accéder</a>
                    </li>
                    <li class="section-card">
                        <div class="section-icon"><i class="fas fa-check-circle"></i></div>
                        <h3>Approbations</h3>
                        <p>Validez ou rejetez les demandes de réservation des utilisateurs.</p>
                        <a href="{{route('reservations.index')}}?statut=en attente }}" class="btn btn-outline">Accéder</a>
                    </li>
                @else
                    <li class="section-card">
                        <div class="section-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <h3>Tableau de bord</h3>
                        <p>Accédez à vos réservations et vos ressources personnelles.</p>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline">Accéder</a>
                    </li>
                @endif

                <li class="section-card">
                    <div class="section-icon"><i class="fas fa-server"></i></div>
                    <h3>Ressources</h3>
                    <p>Consultez toutes les ressources disponibles et leur statut.</p>
                    <a href="{{ route('resources.index') }}" class="btn btn-outline">Accéder</a>
                </li>
                <li class="section-card">
                    <div class="section-icon"><i class="fas fa-calendar-alt"></i></div>
                    <h3>Réservations</h3>
                    <p>Visualisez et gérez vos réservations en un seul endroit.</p>
                    <a href="{{ route('reservations.index') }}" class="btn btn-outline">Accéder</a>
                </li>
            @endguest
        </ul>
    </section>



    <!-- Features Section -->
    <section class="section-content">
        <h2 class="section-title">Fonctionnalités principales</h2>
        <div class="grid">
            <div class="section-card">
                <div class="section-icon">
                    <i class="fas fa-server"></i>
                </div>
                <h3>Gestion des ressources</h3>
                <p>Serveurs, machines virtuelles, stockage et équipements réseau</p>
            </div>

            <div class="section-card">
                <div class="section-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Réservation</h3>
                <p>Système de réservation avec vérification des disponibilités</p>
            </div>

            <div class="section-card">
                <div class="section-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3>Multi-rôles</h3>
                <p>4 profils utilisateurs avec permissions différenciées</p>
            </div>

            <div class="section-card">
                <div class="section-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3>Tableaux de bord</h3>
                <p>Statistiques et rapports d'utilisation</p>
            </div>
        </div>
    </section>

    <!-- User Roles Section -->
    <section class="section-content">
        <h2 class="section-title">Profils utilisateurs</h2>
        <div class="grid">
            <!-- Invité -->
            <div class="section-card">
                <div class="section-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>Invité</h3>
                <ul class="section-list">
                    <li>✔ Voir les ressources</li>
                    <li>✔ Demander un compte</li>
                    <li>✔ Réserver</li>
                </ul>
            </div>

            <!-- Utilisateur -->
            <div class="section-card">
                <div class="section-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h3>Utilisateur</h3>
                <ul class="section-list">
                    <li>✔ Réserver des ressources</li>
                    <li>✔ Suivre ses réservations</li>
                    <li>✔ Recevoir des notifications</li>
                </ul>
            </div>

            <!-- Responsable -->
            <div class="section-card">
                <div class="section-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3>Responsable</h3>
                <ul class="section-list">
                    <li>✔ Gérer ses ressources</li>
                    <li>✔ Approuver les réservations</li>
                    <li>✔ Modérer les messages</li>
                </ul>
            </div>

            <!-- Admin -->
            <div class="section-card">
                <div class="section-icon">
                    <i class="fas fa-user-cog"></i>
                </div>
                <h3>Administrateur</h3>
                <ul class="section-list">
                    <li><i class="fas fa-check text-success"></i> Gérer tout le système</li>
                    <li><i class="fas fa-check text-success"></i> Administrer les utilisateurs</li>
                    <li><i class="fas fa-check text-success"></i> Voir les statistiques</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    @guest
        <section class="cta-content" role="region" aria-labelledby="cta-title">
            <h2 id="cta-title">Prêt à commencer ?</h2>
            <p>Rejoignez notre plateforme pour une meilleure gestion de vos ressources</p>
            <div class="cta-actions">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg" aria-label="Créer un compte gratuit">
                    <i class="fas fa-user-plus"></i> Créer un compte
                </a>
                <a href="{{ route('resources.index') }}" class="btn btn-outline btn-lg"
                    aria-label="Explorer les ressources disponibles">
                    <i class="fas fa-search"></i> Explorer les ressources
                </a>
            </div>
        </section>
    @endguest

    <style>
        .cta-content {
            padding: 80px 0;
            background: white;
            text-align: center;
        }

        .cta-content h2 {
            color: var(--prim-color);
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
    </style>
@endsection