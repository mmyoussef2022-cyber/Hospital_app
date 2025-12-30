/**
 * Hospital Management System - Theme Manager
 * Handles theme switching, language switching, and RTL support
 */

class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'facebook';
        this.currentLanguage = localStorage.getItem('language') || 'ar';
        this.sidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        
        this.init();
    }

    init() {
        this.applyTheme(this.currentTheme);
        this.applyLanguage(this.currentLanguage);
        this.applySidebarState();
        this.bindEvents();
        this.createThemeToggle();
        this.createLanguageToggle();
    }

    // Theme Management
    applyTheme(theme) {
        document.body.className = document.body.className.replace(/theme-\w+/g, '');
        document.body.classList.add(`theme-${theme}`);
        this.currentTheme = theme;
        localStorage.setItem('theme', theme);
        
        // Update meta theme color for mobile browsers
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        const themeColors = {
            facebook: '#1877F2',
            medical: '#00BCD4',
            professional: '#2E7D32',
            dark: '#6366F1'
        };
        
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', themeColors[theme]);
        }
    }

    // Language Management
    applyLanguage(language) {
        const html = document.documentElement;
        html.setAttribute('lang', language);
        html.setAttribute('dir', language === 'ar' ? 'rtl' : 'ltr');
        
        this.currentLanguage = language;
        localStorage.setItem('language', language);
        
        // Update page content if translations are available
        this.updatePageContent(language);
    }

    updatePageContent(language) {
        const translations = {
            ar: {
                'Dashboard': 'لوحة التحكم',
                'Patients': 'المرضى',
                'Doctors': 'الأطباء',
                'Appointments': 'المواعيد',
                'Medical Records': 'السجلات الطبية',
                'Laboratory': 'المختبر',
                'Radiology': 'الأشعة',
                'Pharmacy': 'الصيدلية',
                'Billing': 'الفواتير',
                'Reports': 'التقارير',
                'Settings': 'الإعدادات',
                'Dental Department': 'قسم الأسنان',
                'Logout': 'تسجيل الخروج',
                'Profile': 'الملف الشخصي',
                'Search': 'بحث',
                'Add New': 'إضافة جديد',
                'Edit': 'تعديل',
                'Delete': 'حذف',
                'Save': 'حفظ',
                'Cancel': 'إلغاء',
                'Close': 'إغلاق'
            },
            en: {
                'لوحة التحكم': 'Dashboard',
                'المرضى': 'Patients',
                'الأطباء': 'Doctors',
                'المواعيد': 'Appointments',
                'السجلات الطبية': 'Medical Records',
                'المختبر': 'Laboratory',
                'الأشعة': 'Radiology',
                'الصيدلية': 'Pharmacy',
                'الفواتير': 'Billing',
                'التقارير': 'Reports',
                'الإعدادات': 'Settings',
                'قسم الأسنان': 'Dental Department',
                'تسجيل الخروج': 'Logout',
                'الملف الشخصي': 'Profile',
                'بحث': 'Search',
                'إضافة جديد': 'Add New',
                'تعديل': 'Edit',
                'حذف': 'Delete',
                'حفظ': 'Save',
                'إلغاء': 'Cancel',
                'إغلاق': 'Close'
            }
        };

        const currentTranslations = translations[language];
        if (currentTranslations) {
            document.querySelectorAll('[data-translate]').forEach(element => {
                const key = element.getAttribute('data-translate');
                if (currentTranslations[key]) {
                    element.textContent = currentTranslations[key];
                }
            });
        }
    }

    // Sidebar Management
    applySidebarState() {
        const sidebar = document.querySelector('.modern-sidebar');
        if (sidebar) {
            if (this.sidebarCollapsed) {
                sidebar.classList.add('mini');
            } else {
                sidebar.classList.remove('mini');
            }
        }
    }

    toggleSidebar() {
        const sidebar = document.querySelector('.modern-sidebar');
        if (sidebar) {
            sidebar.classList.toggle('mini');
            this.sidebarCollapsed = sidebar.classList.contains('mini');
            localStorage.setItem('sidebar-collapsed', this.sidebarCollapsed);
        }
    }

    // Event Binding
    bindEvents() {
        // Sidebar toggle
        document.addEventListener('click', (e) => {
            if (e.target.matches('.sidebar-toggle') || e.target.closest('.sidebar-toggle')) {
                e.preventDefault();
                this.toggleSidebar();
            }
        });

        // Mobile sidebar toggle
        document.addEventListener('click', (e) => {
            if (e.target.matches('.mobile-menu-toggle') || e.target.closest('.mobile-menu-toggle')) {
                e.preventDefault();
                const sidebar = document.querySelector('.modern-sidebar');
                if (sidebar) {
                    sidebar.classList.toggle('show');
                }
            }
        });

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', (e) => {
            const sidebar = document.querySelector('.modern-sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('show')) {
                if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            const sidebar = document.querySelector('.modern-sidebar');
            if (window.innerWidth > 768 && sidebar) {
                sidebar.classList.remove('show');
            }
        });
    }

    // Create Theme Toggle Button
    createThemeToggle() {
        const existingToggle = document.querySelector('.theme-toggle');
        if (existingToggle) {
            existingToggle.remove();
        }

        const toggle = document.createElement('div');
        toggle.className = 'theme-toggle';
        toggle.innerHTML = '<i class="bi bi-palette"></i>';
        toggle.title = this.currentLanguage === 'ar' ? 'تغيير المظهر' : 'Change Theme';
        
        toggle.addEventListener('click', () => {
            this.showThemeSelector();
        });

        document.body.appendChild(toggle);
    }

    showThemeSelector() {
        const themes = [
            { id: 'facebook', name: 'Facebook', color: '#1877F2' },
            { id: 'medical', name: 'Medical', color: '#00BCD4' },
            { id: 'professional', name: 'Professional', color: '#2E7D32' },
            { id: 'dark', name: 'Dark', color: '#6366F1' }
        ];

        const modal = document.createElement('div');
        modal.className = 'theme-selector-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        `;

        const content = document.createElement('div');
        content.className = 'theme-selector-content';
        content.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        `;

        const title = document.createElement('h3');
        title.textContent = this.currentLanguage === 'ar' ? 'اختر المظهر' : 'Choose Theme';
        title.style.cssText = `
            margin: 0 0 1.5rem 0;
            text-align: center;
            color: #1F2937;
        `;

        const themeGrid = document.createElement('div');
        themeGrid.style.cssText = `
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        `;

        themes.forEach(theme => {
            const themeOption = document.createElement('div');
            themeOption.className = 'theme-option';
            themeOption.style.cssText = `
                padding: 1rem;
                border: 2px solid ${theme.id === this.currentTheme ? theme.color : '#E5E7EB'};
                border-radius: 8px;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                background: ${theme.id === this.currentTheme ? theme.color + '10' : 'white'};
            `;

            themeOption.innerHTML = `
                <div style="width: 30px; height: 30px; background: ${theme.color}; border-radius: 50%; margin: 0 auto 0.5rem;"></div>
                <div style="font-weight: 500; color: #374151;">${theme.name}</div>
            `;

            themeOption.addEventListener('click', () => {
                this.applyTheme(theme.id);
                modal.remove();
            });

            themeGrid.appendChild(themeOption);
        });

        const closeBtn = document.createElement('button');
        closeBtn.textContent = this.currentLanguage === 'ar' ? 'إغلاق' : 'Close';
        closeBtn.style.cssText = `
            width: 100%;
            padding: 0.75rem;
            background: #6B7280;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        `;

        closeBtn.addEventListener('click', () => {
            modal.remove();
        });

        content.appendChild(title);
        content.appendChild(themeGrid);
        content.appendChild(closeBtn);
        modal.appendChild(content);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });

        document.body.appendChild(modal);
    }

    // Create Language Toggle
    createLanguageToggle() {
        const header = document.querySelector('.modern-header');
        if (!header) return;

        const existingToggle = header.querySelector('.language-toggle');
        if (existingToggle) {
            existingToggle.remove();
        }

        const toggle = document.createElement('div');
        toggle.className = 'language-toggle';
        
        const arOption = document.createElement('div');
        arOption.className = `language-option ${this.currentLanguage === 'ar' ? 'active' : ''}`;
        arOption.textContent = 'العربية';
        arOption.addEventListener('click', () => {
            this.switchLanguage('ar');
        });

        const enOption = document.createElement('div');
        enOption.className = `language-option ${this.currentLanguage === 'en' ? 'active' : ''}`;
        enOption.textContent = 'English';
        enOption.addEventListener('click', () => {
            this.switchLanguage('en');
        });

        toggle.appendChild(arOption);
        toggle.appendChild(enOption);

        // Insert before the last child (usually user menu)
        const headerContent = header.querySelector('.header-content') || header;
        headerContent.insertBefore(toggle, headerContent.lastElementChild);
    }

    switchLanguage(language) {
        if (language !== this.currentLanguage) {
            // Store the language preference
            localStorage.setItem('language', language);
            
            // Show loading indicator
            const loading = document.createElement('div');
            loading.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.9);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10001;
                font-size: 1.2rem;
                color: #374151;
            `;
            loading.innerHTML = `
                <div style="text-align: center;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div style="margin-top: 1rem;">${language === 'ar' ? 'جاري تغيير اللغة...' : 'Changing language...'}</div>
                </div>
            `;
            
            document.body.appendChild(loading);
            
            // Redirect to Laravel language route
            setTimeout(() => {
                window.location.href = `/lang/${language}`;
            }, 1000);
        }
    }

    // Utility Methods
    getCurrentTheme() {
        return this.currentTheme;
    }

    getCurrentLanguage() {
        return this.currentLanguage;
    }

    isRTL() {
        return this.currentLanguage === 'ar';
    }
}

// Initialize Theme Manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeManager;
}