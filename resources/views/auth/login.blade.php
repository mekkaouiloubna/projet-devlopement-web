@extends('layouts.app')

@section('title', 'Connexion - Data Center Manager')

@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <h2>Connexion</h2>
            <p>Accédez à votre espace Data Center</p>
        </div>

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Adresse Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       placeholder="votre@email.com">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       required 
                       placeholder="Votre mot de passe">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group form-options">
                <label class="checkbox-container">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span class="checkmark"></span>
                    Se souvenir de moi
                </label>
                
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-password">
                        Mot de passe oublié ?
                    </a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary auth-btn">
                Se connecter
            </button>
        </form>

        <div class="auth-footer">
            <p>Vous n'avez pas de compte ? 
                <a href="{{ route('register') }}">Créer un compte</a>
            </p>
            
            <div class="auth-divider">
                <span>Ou continuer en tant qu'invité</span>
            </div>
            
            <a href="{{ route('resources.index') }}" class="btn">
                Consulter les ressources disponibles
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.auth-form');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        
        // Validation en temps réel
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        emailInput.addEventListener('blur', function() {
            if (this.value && !validateEmail(this.value)) {
                this.classList.add('is-invalid');
                let errorDiv = this.parentNode.querySelector('.invalid-feedback');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    this.parentNode.appendChild(errorDiv);
                }
                errorDiv.textContent = 'Veuillez entrer une adresse email valide.';
            } else {
                this.classList.remove('is-invalid');
                const errorDiv = this.parentNode.querySelector('.invalid-feedback');
                if (errorDiv && !this.classList.contains('is-invalid')) {
                    errorDiv.remove();
                }
            }
        });
        
        passwordInput.addEventListener('blur', function() {
            if (this.value.length < 6) {
                this.classList.add('is-invalid');
                let errorDiv = this.parentNode.querySelector('.invalid-feedback');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    this.parentNode.appendChild(errorDiv);
                }
                errorDiv.textContent = 'Le mot de passe doit contenir au moins 6 caractères.';
            } else {
                this.classList.remove('is-invalid');
                const errorDiv = this.parentNode.querySelector('.invalid-feedback');
                if (errorDiv && !this.classList.contains('is-invalid')) {
                    errorDiv.remove();
                }
            }
        });
        
        // Animation du bouton de soumission
        const submitBtn = form.querySelector('.auth-btn');
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validation finale
            if (!emailInput.value || !validateEmail(emailInput.value)) {
                emailInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!passwordInput.value || passwordInput.value.length < 6) {
                passwordInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                submitBtn.style.animation = 'shake 0.5s ease';
                setTimeout(() => {
                    submitBtn.style.animation = '';
                }, 500);
            } else {
                // Animation de chargement
                submitBtn.innerHTML = '<span class="btn-loader"></span> Connexion en cours...';
                submitBtn.disabled = true;
            }
        });
    });
</script>
@endsection