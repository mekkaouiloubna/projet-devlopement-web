/* ===========================================
   JavaScript personnalisé pour le système
   de gestion des ressources du Data Center
   Sans jQuery – utilisation de JavaScript natif
   =========================================== */

// Variables globales
const App = {
    // Initialisation
    init: function () {
        console.log("Data Center Resource Manager - Initialized");

        // Initialisation de tous les composants
        this.initMenuToggle();
        this.initModals();
        this.initTooltips();
        this.initTabs();
        this.initAccordions();
        this.initForms();
        this.initNotifications();
        this.initThemeSwitch();
        this.initPrintButtons();
    },

    // Basculer le menu latéral (mobile)
    initMenuToggle: function () {
        const menuToggle = document.querySelector(".menu-toggle");
        const sidebar = document.querySelector(".sidebar");

        if (menuToggle && sidebar) {
            menuToggle.addEventListener("click", function () {
                sidebar.classList.toggle("active");
            });
        }

        // Fermer le menu en cliquant à l’extérieur (mobile)
        document.addEventListener("click", function (event) {
            if (window.innerWidth <= 992) {
                if (sidebar && sidebar.classList.contains("active")) {
                    if (
                        !sidebar.contains(event.target) &&
                        !menuToggle.contains(event.target)
                    ) {
                        sidebar.classList.remove("active");
                    }
                }
            }
        });
    },

    // Initialisation des fenêtres modales
    initModals: function () {
        // Ouvrir la modale
        document.querySelectorAll("[data-modal]").forEach((button) => {
            button.addEventListener("click", function () {
                const modalId = this.getAttribute("data-modal");
                const modal = document.getElementById(modalId);

                if (modal) {
                    modal.classList.add("active");
                    document.body.style.overflow = "hidden";
                }
            });
        });

        // Fermer la modale
        document.querySelectorAll(".modal-close, .modal").forEach((element) => {
            element.addEventListener("click", function (event) {
                if (
                    event.target === this ||
                    event.target.classList.contains("modal-close") ||
                    event.target.classList.contains("btn-cancel")
                ) {
                    const modal = this.closest(".modal") || this;
                    modal.classList.remove("active");
                    document.body.style.overflow = "";
                }
            });
        });

        // Empêcher la fermeture lors du clic sur le contenu
        document.querySelectorAll(".modal-content").forEach((content) => {
            content.addEventListener("click", function (event) {
                event.stopPropagation();
            });
        });
    },

    // Initialisation des info-bulles
    initTooltips: function () {
        // Gérées automatiquement via CSS
        console.log("Tooltips initialized");
    },

    // Initialisation des onglets
    initTabs: function () {
        document.querySelectorAll(".tab").forEach((tab) => {
            tab.addEventListener("click", function () {
                const tabId = this.getAttribute("data-tab");

                // Supprimer l’état actif de tous les onglets
                this.closest(".tabs")
                    .querySelectorAll(".tab")
                    .forEach((t) => {
                        t.classList.remove("active");
                    });

                // Activer l’onglet courant
                this.classList.add("active");

                // Masquer tous les contenus
                const tabContents =
                    this.closest(".tab-container")?.querySelectorAll(
                        ".tab-content",
                    ) || document.querySelectorAll(".tab-content");

                tabContents.forEach((content) => {
                    content.classList.remove("active");
                });

                // Afficher le contenu ciblé
                if (tabId) {
                    const targetContent = document.getElementById(tabId);
                    if (targetContent) {
                        targetContent.classList.add("active");
                    }
                }
            });
        });
    },

    // Initialisation des accordéons
    initAccordions: function () {
        document.querySelectorAll(".nav-accordion-header").forEach((header) => {
            header.addEventListener("click", function () {
                const accordionItem = this.closest(".nav-accordion-item");
                accordionItem.classList.toggle("active");
            });
        });
    },

    // Initialisation des formulaires
    initForms: function () {
        // Validation des formulaires
        document.querySelectorAll("form[data-validate]").forEach((form) => {
            form.addEventListener("submit", function (event) {
                if (!this.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    // Afficher les messages d’erreur
                    const invalidFields = this.querySelectorAll(":invalid");
                    invalidFields.forEach((field) => {
                        field.classList.add("is-invalid");

                        const errorDiv = document.createElement("div");
                        errorDiv.className = "invalid-feedback";
                        errorDiv.textContent = field.validationMessage;

                        field.parentNode.appendChild(errorDiv);
                    });
                }

                this.classList.add("was-validated");
            });
        });

        // Supprimer les messages d’erreur lors de la saisie
        document
            .querySelectorAll("input, select, textarea")
            .forEach((field) => {
                field.addEventListener("input", function () {
                    this.classList.remove("is-invalid");

                    const errorDiv =
                        this.parentNode.querySelector(".invalid-feedback");
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                });
            });
    },

    // Initialisation des notifications
    initNotifications: function () {
        this.updateUnreadCount();

        // Mise à jour toutes les 30 secondes
        setInterval(() => {
            this.updateUnreadCount();
        }, 30000);
    },

    // Mettre à jour le nombre de notifications non lues
    updateUnreadCount: function () {
        fetch("/notifications/unread-count")
            .then((response) => response.json())
            .then((data) => {
                const badge = document.querySelector(".notification-badge");
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = "inline-block";
                    } else {
                        badge.style.display = "none";
                    }
                }
            })
            .catch((error) =>
                console.error(
                    "Erreur lors du chargement des notifications :",
                    error,
                ),
            );
    },

    // Basculer le mode clair / sombre
    initThemeSwitch: function () {
        const themeSwitch = document.querySelector("#theme-switch");
        if (!themeSwitch) return;

        const savedTheme = localStorage.getItem("theme") || "light";
        if (savedTheme === "dark") {
            document.body.classList.add("dark-mode");
            themeSwitch.checked = true;
        }

        themeSwitch.addEventListener("change", function () {
            if (this.checked) {
                document.body.classList.add("dark-mode");
                localStorage.setItem("theme", "dark");
            } else {
                document.body.classList.remove("dark-mode");
                localStorage.setItem("theme", "light");
            }
        });
    },

    // Initialisation des boutons d’impression
    initPrintButtons: function () {
        document.querySelectorAll(".btn-print").forEach((button) => {
            button.addEventListener("click", function () {
                window.print();
            });
        });
    },

    // Afficher une notification (toast)
    showToast: function (message, type = "info") {
        const toast = document.createElement("div");
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-header">
                <strong>${this.getToastTitle(type)}</strong>
                <button class="toast-close">&times;</button>
            </div>
            <div class="toast-body">${message}</div>
        `;

        document.body.appendChild(toast);

        toast
            .querySelector(".toast-close")
            .addEventListener("click", function () {
                toast.remove();
            });

        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    },

    // Titre du toast selon le type
    getToastTitle: function (type) {
        const titles = {
            success: "Succès",
            error: "Erreur",
            warning: "Avertissement",
            info: "Information",
        };
        return titles[type] || "Notification";
    },

    // Charger du contenu via AJAX
    loadContent: function (url, containerId, callback) {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = '<div class="loader"></div>';

        fetch(url)
            .then((response) => response.text())
            .then((html) => {
                container.innerHTML = html;
                if (callback) callback();
            })
            .catch((error) => {
                container.innerHTML = `<div class="alert alert-danger">Erreur de chargement : ${error.message}</div>`;
            });
    },

    // Soumettre un formulaire via AJAX
    submitForm: function (form, callback) {
        const formData = new FormData(form);

        fetch(form.action, {
            method: form.method,
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (callback) callback(data);
            })
            .catch((error) => {
                this.showToast(
                    "Erreur lors de l’envoi : " + error.message,
                    "error",
                );
            });
    },
};

// Initialisation de l’application au chargement de la page
document.addEventListener("DOMContentLoaded", function () {
    App.init();
});

// Styles du mode sombre
const darkModeStyles = `
    <style>
        body.dark-mode {
            background-color: #1a1a1a;
            color: #f0f0f0;
        }
        
        body.dark-mode .card,
        body.dark-mode .modal-content,
        body.dark-mode .table {
            background-color: #2d2d2d;
            color: #f0f0f0;
            border-color: #404040;
        }
        
        body.dark-mode .table th {
            background-color: #404040;
            color: #f0f0f0;
        }
        
        body.dark-mode .form-control {
            background-color: #333;
            border-color: #555;
            color: #f0f0f0;
        }
        
        body.dark-mode .sidebar {
            background-color: #1e1e1e;
        }
    </style>
`;

document.head.insertAdjacentHTML("beforeend", darkModeStyles);

// user-management.js
document.addEventListener("DOMContentLoaded", function () {
    // Gestion des filtres
    const filterChips = document.querySelectorAll(".filter-chip .remove");
    filterChips.forEach((chip) => {
        chip.addEventListener("click", function () {
            this.closest(".filter-chip").remove();
            // Mettre à jour l'URL ou soumettre le formulaire
        });
    });

    // Sélection/désélection multiple
    const selectAllCheckbox = document.getElementById("selectAll");
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener("change", function () {
            const checkboxes = document.querySelectorAll(".user-checkbox");
            checkboxes.forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Actions groupées
    const bulkActionForm = document.getElementById("bulkActionForm");
    if (bulkActionForm) {
        const bulkActionSelect = document.getElementById("bulkAction");
        bulkActionSelect.addEventListener("change", function () {
            if (this.value) {
                if (
                    confirm(
                        `Voulez-vous vraiment ${this.options[this.selectedIndex].text.toLowerCase()} les utilisateurs sélectionnés ?`,
                    )
                ) {
                    bulkActionForm.submit();
                }
                this.value = "";
            }
        });
    }

    // Recherche en temps réel (délai)
    let searchTimeout;
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener("input", function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });
    }

    // Toggle des ressources dans le modal
    document.querySelectorAll(".resource-select-card").forEach((card) => {
        card.addEventListener("click", function (e) {
            if (!e.target.closest("a") && !e.target.closest("input")) {
                const checkbox = this.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    this.classList.toggle("selected", checkbox.checked);
                }
            }
        });
    });

    // Confirmation pour les actions critiques
    document.querySelectorAll(".btn-danger, .btn-warning").forEach((btn) => {
        btn.addEventListener("click", function (e) {
            if (this.classList.contains("require-confirmation")) {
                const action = this.textContent.trim();
                if (
                    !confirm(
                        `Êtes-vous sûr de vouloir ${action.toLowerCase()} ?`,
                    )
                ) {
                    e.preventDefault();
                }
            }
        });
    });
});

// Fonction pour afficher/cacher les ressources
function toggleResourcesField(select, userId = null) {
    const roleName = select.options[select.selectedIndex].text;
    const resourcesField = userId
        ? document.getElementById("resourcesField" + userId)
        : document.getElementById("resourcesField");

    if (!resourcesField) return;

    if (roleName === "Responsable") {
        resourcesField.style.display = "block";
        // Animation
        resourcesField.style.animation = "fadeIn 0.3s ease";
    } else {
        resourcesField.style.display = "none";
        // Désélectionner toutes les cases à cocher
        const checkboxes = resourcesField.querySelectorAll(
            'input[type="checkbox"]',
        );
        checkboxes.forEach((checkbox) => (checkbox.checked = false));
    }
}
