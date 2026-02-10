class LanguageManager {
    constructor() {
        this.currentLang = localStorage.getItem('site_lang') || this.detectLanguage();
        this.init();
    }

    detectLanguage() {
        const userLang = navigator.language || navigator.userLanguage;
        return userLang.startsWith('ar') ? 'ar' : 'en';
    }

    setLanguage(lang) {
        this.currentLang = lang;
        localStorage.setItem('site_lang', lang);
        this.applyLanguage();
    }

    applyLanguage() {
        const doc = document.documentElement;
        const isAr = this.currentLang === 'ar';

        doc.setAttribute('lang', this.currentLang);
        doc.setAttribute('dir', isAr ? 'rtl' : 'ltr');

        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (translations[this.currentLang][key]) {
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                    el.placeholder = translations[this.currentLang][key];
                } else {
                    el.innerText = translations[this.currentLang][key];
                }
            }
        });

        if (isAr) {
            document.body.style.fontFamily = "'Tahoma', 'Segoe UI', sans-serif"; // Fallback for better Arabic rendering
        } else {
            document.body.style.fontFamily = "'Inter', sans-serif";
        }
    }

    init() {
        this.applyLanguage();
    }
}

const langManager = new LanguageManager();

function switchLanguage(lang) {
    langManager.setLanguage(lang);
    location.reload();
}
