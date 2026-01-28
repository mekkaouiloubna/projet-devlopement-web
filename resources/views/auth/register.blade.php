@extends('layouts.app')

@section('title', 'Créer un compte - Data Center Manager')

@section('content')
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Créer un compte</h2>
                <p>Rejoignez notre plateforme de gestion des ressources</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="auth-form" id="registerForm">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom" class="form-label">Prénom *</label>
                        <input type="text" id="prenom" name="prenom"
                            class="form-control @error('prenom') is-invalid @enderror" value="{{ old('prenom') }}" required
                            autofocus placeholder="Votre prénom">
                        @error('prenom')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nom" class="form-label">Nom *</label>
                        <input type="text" id="nom" name="nom" class="form-control @error('nom') is-invalid @enderror"
                            value="{{ old('nom') }}" required placeholder="Votre nom">
                        @error('nom')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Adresse Email *</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" required placeholder="votre@email.com">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="type" class="form-label">Type d'utilisateur *</label>
                    <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="" disabled selected>Sélectionnez votre type</option>
                        <option value="Ingénieur" {{ old('type') == 'Ingénieur' ? 'selected' : '' }}>Ingénieur</option>
                        <option value="Enseignant" {{ old('type') == 'Enseignant' ? 'selected' : '' }}>Enseignant</option>
                        <option value="Doctorant" {{ old('type') == 'Doctorant' ? 'selected' : '' }}>Doctorant</option>
                    </select>
                    @error('type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe *</label>
                    <div class="password-input-group">
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" required
                            placeholder="Minimum 6 caractères">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <div class="password-strength" id="passwordStrength"></div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirmer le mot de passe *</label>
                    <div class="password-input-group">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                            required placeholder="Répétez votre mot de passe">
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-match" id="passwordMatch"></div>
                </div>

                <div class="form-group terms-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="terms" id="terms" required>
                        <span class="checkmark"></span>
                        J'accepte les <a href="#terms-modal" data-modal="termsModal"
                            style="text-decoration : none">conditions d'utilisation</a> et la <a href="#privacy-modal"
                            style="text-decoration:none;" data-modal="privacyModal">politique de confidentialité</a>
                    </label>
                    @error('terms')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary auth-btn" id="submitBtn">
                    Créer mon compte
                </button>
            </form>

            <div class="auth-footer">
                <p>Déjà un compte ?
                    <a href="{{ route('login') }}">Se connecter</a>
                </p>

                <div>
                    <span>Ou continuer en tant qu'invité :</span>
                    <a href="{{ route('resources.index') }}" class="link">Consulter les ressources disponibles</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour les conditions d'utilisation -->
    <div class="modal" id="termsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Conditions d'utilisation</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Conditions d'utilisation du Data Center Resource Manager</strong></p>
                <p>1. Le système est destiné à la gestion des ressources informatiques du data center.</p>
                <p>2. Les utilisateurs s'engagent à utiliser les ressources de manière responsable.</p>
                <p>3. Toute utilisation abusive peut entraîner la suspension du compte.</p>
                <p>4. Les réservations doivent être justifiées par des besoins professionnels ou académiques.</p>
                <p>5. L'administration se réserve le droit de modifier ces conditions à tout moment.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="closeModal('termsModal')">Compris</button>
            </div>
        </div>
    </div>

    <!-- Modal pour la politique de confidentialité -->
    <div class="modal" id="privacyModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Politique de confidentialité</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Protection de vos données personnelles</strong></p>
                <p>1. Nous collectons uniquement les données nécessaires au fonctionnement du service.</p>
                <p>2. Vos données ne seront jamais vendues ou partagées avec des tiers sans votre consentement.</p>
                <p>3. Vous pouvez à tout moment demander la suppression de vos données personnelles.</p>
                <p>4. Le système utilise des mesures de sécurité avancées pour protéger vos informations.</p>
                <p>5. Les historiques de réservation sont conservés pendant 2 ans maximum.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="closeModal('privacyModal')">Compris</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('registerForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordMatch = document.getElementById('passwordMatch');
            const submitBtn = document.getElementById('submitBtn');
            const termsCheckbox = document.getElementById('terms');

            // Fonction pour basculer la visibilité du mot de passe
            window.togglePassword = function (fieldId) {
                const field = document.getElementById(fieldId);
                const toggleBtn = field.parentNode.querySelector('.password-toggle');

                if (field.type === 'password') {
                    field.type = 'text';
                    toggleBtn.innerHTML = `
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M2.99902 3L20.999 21M9.8433 9.91364C9.32066 10.4536 8.99902 11.1892 8.99902 12C8.99902 13.6569 10.3422 15 11.999 15C12.8215 15 13.5667 14.669 14.1086 14.133M6.49902 6.64715C4.59972 7.90034 3.15305 9.78394 2.45703 12C3.73128 16.0571 7.52159 19 11.999 19C13.9881 19 15.8414 18.4194 17.3988 17.4184M10.999 5.04939C11.328 5.01673 11.6617 5 11.999 5C16.4765 5 20.2668 7.94291 21.541 12C21.2607 12.894 20.8577 13.7338 20.3522 14.5" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    `;
                } else {
                    field.type = 'password';
                    toggleBtn.innerHTML = `
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    `;
                }
            };

            // Validation de la force du mot de passe
            function checkPasswordStrength(password) {
                let strength = 0;

                if (password.length >= 6) strength++;
                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;

                return strength;
            }

            function updatePasswordStrength() {
                const password = passwordInput.value;
                const strength = checkPasswordStrength(password);

                passwordStrength.className = 'password-strength';

                if (password.length === 0) {
                    passwordStrength.style.display = 'none';
                    return;
                }

                passwordStrength.style.display = 'block';

                if (strength <= 2) {
                    passwordStrength.classList.add('weak');
                } else if (strength === 3) {
                    passwordStrength.classList.add('medium');
                } else if (strength === 4) {
                    passwordStrength.classList.add('strong');
                } else {
                    passwordStrength.classList.add('very-strong');
                }
            }

            function checkPasswordMatch() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                if (confirmPassword.length === 0) {
                    passwordMatch.innerHTML = '';
                    return;
                }

                if (password === confirmPassword) {
                    passwordMatch.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#27ae60">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                        Les mots de passe correspondent
                    `;
                    passwordMatch.className = 'password-match match';
                } else {
                    passwordMatch.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#e74c3c">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                        </svg>
                        Les mots de passe ne correspondent pas
                    `;
                    passwordMatch.className = 'password-match no-match';
                }
            }

            // Écouteurs d'événements
            passwordInput.addEventListener('input', function () {
                updatePasswordStrength();
                checkPasswordMatch();
                validateForm();
            });

            confirmPasswordInput.addEventListener('input', function () {
                checkPasswordMatch();
                validateForm();
            });

            // Validation des champs
            const requiredFields = form.querySelectorAll('input[required], select[required]');

            function validateForm() {
                let isValid = true;

                // Vérifier les champs requis
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                    }
                });

                // Vérifier le mot de passe
                if (passwordInput.value.length < 6) {
                    isValid = false;
                }

                // Vérifier la correspondance des mots de passe
                if (passwordInput.value !== confirmPasswordInput.value) {
                    isValid = false;
                }

                // Vérifier les conditions d'utilisation
                if (!termsCheckbox.checked) {
                    isValid = false;
                }

                // Activer/désactiver le bouton
                submitBtn.disabled = !isValid;

                return isValid;
            }

            // Valider à chaque changement
            form.addEventListener('input', validateForm);
            termsCheckbox.addEventListener('change', validateForm);

            // Initial validation
            validateForm();

            // Gestion de la soumission
            form.addEventListener('submit', function (e) {
                if (!validateForm()) {
                    e.preventDefault();
                    submitBtn.style.animation = 'shake 0.5s ease';
                    setTimeout(() => {
                        submitBtn.style.animation = '';
                    }, 500);
                    return;
                }

                // Animation de chargement
                submitBtn.innerHTML = '<span class="btn-loader"></span> Création du compte...';
                submitBtn.disabled = true;
            });

            // Gestion des modals
            document.querySelectorAll('[data-modal]').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const modalId = this.getAttribute('data-modal');
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                });
            });

            document.querySelectorAll('.modal-close, .modal').forEach(element => {
                element.addEventListener('click', function (event) {
                    if (event.target === this ||
                        event.target.classList.contains('modal-close') ||
                        event.target.classList.contains('btn')) {

                        const modal = this.closest('.modal') || this;
                        modal.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });

            // Empêcher la fermeture en cliquant dans le contenu
            document.querySelectorAll('.modal-content').forEach(content => {
                content.addEventListener('click', function (event) {
                    event.stopPropagation();
                });
            });
        });

        // Fonction globale pour fermer les modals
        window.closeModal = function (modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        };
    </script>
@endsection